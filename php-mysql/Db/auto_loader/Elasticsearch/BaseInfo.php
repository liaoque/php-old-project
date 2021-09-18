<?php


class Elasticsearch_BaseInfo extends Elasticsearch_Base
{
    use Elasticsearch_TraitQuery;

    public function getTableName()
    {
        // TODO: Implement getTableName() method.
        if (empty($this->tableName)) {
            if (check_is_test()) {
                $this->tableName = 'logstash-test-base-info';
            } else {
                $this->tableName = 'logstash-master-base-info';
            }
        }
        return parent::getTableName();
    }


    public function getQueryTemplateFiledList()
    {
        return [
            'context',
            'log',
            'channel',
            'message',
            'host.hostname',
            'host.ip',
            "@timestamp",
            "tags",
            "level",
            "extra",
            "level_name",
        ];
    }

    public function getTableList($page = 0, $limit = 20, $where = [], $order = '')
    {
        $page = $page < 1 ? 1 : $page;
        $query = self::getQueryTemplate($page, $limit);
        $must = [
            'level_name' => var_def($_GET['level_name'], 0),
            'host.ip' => var_def($_GET['ip'], 0),
            'channel' => var_def($_GET['channel'], 0),
            'log.file.path' => var_def($_GET['log_module'], 0),
        ];
        switch ($_GET['search_type']) {
            case 1:
                $must['extra.traceId'] = var_def($_GET['keyword'], 0);
                break;
            case 2:
//                $must['context'] = var_def($_GET['keyword'], 0);
                break;
            case 3:
                $must['message'] = var_def($_GET['keyword'], 0);
                break;
//            case 4:
//                $must['log.file.path'] = var_def($_GET['keyword'], 0);
//                break;
            default:
                break;
        }

        $timestamp = [
            "gte" => var_def($_GET['time_start'], ""),
            "lte" => var_def($_GET['time_end'], ""),
        ];
//var_dump($must);
        $must = array_filter($must);
        if (empty($must)) {
            $must = [];
        }
        array_walk($must, function (&$item, $key) {
            if ($key == "log.file.path") {
                $item = [
                    "match_phrase" => [
                        $key =>  $item
                    ]
                ];
            } else {
                $item = [
                    "match" => [
                        $key => $item
                    ]
                ];
            }


        });
        $must = array_values($must);
        $timestamp = array_filter($timestamp);
        $rangeArray = [];
        if ($timestamp) {
            $timestamp = array_map(function ($item) {
                return date("Y-m-d\TH:i:s", strtotime($item));
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
                "must" => $must,
            ];
        }
//        var_dump($query);exit();
        $res = self::getAll($query);
        $list = array_map(function ($item) {
            $index = $item['_index'];
            $id = $item['_id'];
            $item = $item['_source'];
            $path = $item['log']['file']['path'];
            $explode = explode('/', $path);
            $log = $explode[count($explode) - 1];

            if ($log) {
                $logTag = preg_replace('/-\d+-\d+-\d+-\d+\.\d+\.\d+\.\d+\.log$/', '', $log);
                $log = Model_LogModule::getInstance()->getNameWhereSave($logTag);
            }
            $message = $item['message'];
            if($message == "提交信息"){
                if($logTag == "base-info"){
                    $context = $item['context'];
                    $context = json_decode($context, true);
                    unset($context['router']);
                    $contextKeys = array_keys($context);
                    $message .= ":".current($contextKeys);
                }
            }

            $info = [
                'id' => $id,
                'index' => $index,
                'date_time' => date('Y-m-d H:i:s', local_strtotime($item['@timestamp'])),
                'level_name' => $item['level_name'],
                'log' => $log,
                'extra' => $item['extra'],
                'tags' => $item['tags'],
                'ip' => implode(',', $item['host']['ip']),
                'channel' => $item['channel'],
                'message' => $message,
            ];
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


    public static function createIndex(){
        $jsonFile = ROOT_PATH.'/crontab/elasticsearch/template/base-info.json';
        $mix = file_get_contents($jsonFile);

        $data = [
            "logstash-master-base-info-2021-03-01" => json_decode($mix, true)
        ];
        $baseElasticsearchUrl = self::getBaseElasticsearchUrl();
        $requestGetJson = Http_Base::requestGetJson($baseElasticsearchUrl, $data);
        return $requestGetJson;
    }


}
