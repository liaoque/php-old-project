<?php


class Elasticsearch_LogNginx extends Elasticsearch_Base
{
    use Elasticsearch_TraitQuery;

    public function getTableName()
    {
        // TODO: Implement getTableName() method.
        if (empty($this->tableName)) {
            if (check_is_test()) {
                $this->tableName = 'logstash-test-nginx-log';
            } else {
                $this->tableName = 'logstash-master-nginx-log';
            }
        }
        return parent::getTableName();
    }


    public static function getHostList()
    {
        return $hostList = [
            '/alidata/log/nginx/api_access.log' => 'api.wm18.com_access',
            '/alidata/log/nginx/api_error.log' => 'api.wm18.com_error',
            '/alidata/log/nginx/apiwm18.wm18.com_access.log' => 'apiwm18.wm18.com_access',
            '/alidata/log/nginx/apiwm18.wm18.com_error.log' => 'apiwm18.wm18.com_error',
        ];
    }

    public function getQueryTemplateFiledList()
    {
        return [
            'source',
            'log.file.path',
            'agent',
            'host.ip',
            'url',
            '@timestamp',
            'http.request',
            'http.response.status_code',
            'user_agent.original'
        ];
    }

    public function getTableList($page = 0, $limit = 20, $where = [], $order = '')
    {
        $page = $page < 1 ? 1 : $page;
        $query = self::getQueryTemplate($page, $limit);
        $hostList = self::getHostList();
        $must = [
            'http.response.status_code' => var_def($_GET['http_code'], 0),
            'source.ip' => var_def($_GET['ip'], 0),
            'http.request.method' => var_def($_GET['methoded'], 0),
            'url.original.text' => var_def($_GET['keyword'], 0),
            'http.response.status_code' => var_def($_GET['http_code'], 0),
        ];
        if ($where['host']) {
            $listHost = array_flip($hostList);
            $must['log.file.path'] = $listHost[$_GET['host']];
        }


        $timestamp = [
            "gte" => var_def($_GET['time_start'], ""),
            "lte" => var_def($_GET['time_end'], ""),
        ];

        $must = array_filter($must);
        if (empty($must)) {
            $must = [];
        }
        array_walk($must, function (&$item, $key) {
            $item = [
                "match" => [
                    $key => $item
                ]
            ];
        });
        $must = array_values($must);
        $timestamp = array_filter($timestamp);
        $rangeArray = [];
        if ($timestamp) {
            $timestamp = array_map(function ($item) {
                return date("Y-m-d\TH:i:s", strtotime($item) - 3600 * 8);
            }, $timestamp);
            $rangeArray = [
                [
                    'range' => [
                        "@timestamp" => $timestamp,
                    ]
                ]
            ];
        }

        $must = array_merge($must, $rangeArray);
        if ($must) {
            $query['query']["bool"] = [
                "must" => $must
            ];
        }

        try {
            $res = Http_Base::requestGetJson(self::getSearchUrl(), ($query));
        } catch (Exception $exception) {
            $res = '[]';
            $error = $exception->getMessage();
            $GLOBALS['smarty']->assign('error', $error);
        }
        $res = json_decode($res, true);
        $list = array_map(function ($item) use ($hostList) {
            $item = $item['_source'];
            $host = $hostList[$item['log']['file']['path']];
            $info = [
                'date_time' => date('Y-m-d H:i:s', strtotime($item['@timestamp'])),
                'referrer' => empty($item['http']['request']) ? 'GET' : $item['http']['request']['referrer'],
                'method' => empty($item['http']['request']) ? 'GET' : $item['http']['request']['method'],
                'url' => empty($item['url']) ? '/' : $item['url']['original'],
                'status_code' => $item['http']['response']['status_code'],
                'server' => $item['agent']['hostname'],
                'file' => $host,
                'ip' => empty($item['source']['ip']) ? $item['host']['ip'][0] : $item['source']['ip'],
                'user_agent' => empty($item['user_agent']) ? '' : $item['user_agent']['original'],
            ];
            if (mb_strlen($info['url']) > 40) {
                $afFix = '...';
            }
            $info['short_url'] = mb_substr($info['url'], 0, 40) . $afFix;
            return $info;
        }, $res['hits']['hits']);

        if (empty($res)) {
            $count = 0;
        } else {
            if ($res['hits']['total']['relation'] == 'eq') {
                $count = $res['hits']['total']['value'];
            } else {
                $count = 1000000;
            }
        }

        return [
            'total' => $count,
            'list' => $list,
        ];
    }

}
