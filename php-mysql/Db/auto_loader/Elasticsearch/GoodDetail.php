<?php

class Elasticsearch_GoodDetail extends Elasticsearch_Base
{
    use Elasticsearch_TraitQuery;

    public function getTableName()
    {
        // TODO: Implement getTableName() method.
        if (empty($this->tableName)) {
            if (check_is_test()) {
                $this->tableName = 'logstash-test-goods-detail-point';
            } else {
                $this->tableName = 'logstash-master-goods-detail-point';
            }
        }
        return parent::getTableName();
    }

    public function getQueryTemplateFiledList()
    {
        // TODO: Implement getQueryTemplateFiledList() method.
    }


    /**
     * 获取商品详情页的pv和uv
     * @return array
     * @throws Exception
     */
    public function countPvUv($where = [])
    {
        $var = [
            "aggs" => [
                "countPv" => [
                    "value_count" => [
                        "field" => "context.ip"
                    ]
                ],
                "countUv" => [
                    "cardinality" => [
                        "field" => "context.ip"
                    ]
                ],
            ]
        ];
        $must = [];
        if ($where['@timestamp']) {
            $must[] = [
                'range' => ['@timestamp' => $where['@timestamp']]
            ];
        }

        if ($where['goods_id']) {
            $must[] = [
                'match' => ['context.goods_id' => $where['goods_id']]
            ];
        }
        if ($must) {
            $var['query'] = [
                'bool' => [
                    'must' => $must
                ]
            ];
        }


        $url = self::getSearchUrl() . "?" . http_build_query([
                "filter_path" => "aggregations"
            ]);
        $requestGetJson = Http_Base::requestGetJson($url, $var);
        $request = json_decode($requestGetJson, true);
        return [
            'pv' => $request['aggregations']['countPv']['value'],
            'uv' => $request['aggregations']['countUv']['value'],
        ];
    }

    /**
     * @param int $order 按pv或者uv排序
     * @param int $topN 前N个上的商品 null就是全部返回
     * @return array[]
     * @throws Exception
     */
    public function countTopPvUv($where = [], $order = 'pv', $topN = 10)
    {
        if ($order != 'pv') {
            $order = 'uv.value';
        } else {
            $order = '_count';
        }
        $var = [
            "aggs" => [
                "top" => [
                    "terms" => [
                        "field" => "context.goods_id",
                        "order" => [
                            $order => 'desc'
                        ],
                        "size" => $topN
                    ],
                    "aggs" => [
                        "uv" => [
                            "cardinality" => [
                                "field" => "context.ip"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if($topN === null){
            unset($var['aggs']['top']['terms']['size']);
        }

        $must = [];
        if ($where['@timestamp']) {
            $must[] = [
                'range' => ['@timestamp' => $where['@timestamp']]
            ];
        }

        if ($where['goods_id']) {
            $must[] = [
                'match' => ['context.goods_id' => $where['goods_id']]
            ];
        }
        if ($must) {
            $var['query'] = [
                'bool' => [
                    'must' => $must
                ]
            ];
        }

        $url = self::getSearchUrl() . "?" . http_build_query([
                "filter_path" => "aggregations"
            ]);
        $requestGetJson = Http_Base::requestGetJson($url, $var);
        $request = json_decode($requestGetJson, true);

        $request = array_map(function ($info) {
            return [
                'goods_id' => $info['key'],
                'pv' => $info['doc_count'],
                'uv' => $info['uv']['value'],
            ];
        }, $request['aggregations']['top']['buckets']);
        return $request;
    }

}
