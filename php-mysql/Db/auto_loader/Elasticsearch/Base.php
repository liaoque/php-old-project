<?php


abstract class Elasticsearch_Base implements Model_BaseInterface
{
    public $tableName = '';
    public $suffix = '';
    private static $instanceHandl;


    public static function getBaseElasticsearchUrl()
    {
        return 'http://192.168.1.102:19200';
    }


    public function getTableName()
    {
        // TODO: Implement getTableName() method.
        $suffix = '';
        if ($this->suffix) {
            $suffix = '-' . $this->suffix;
        }
        return $this->tableName . $suffix;
    }

    /**
     * @param $tableName
     * @param $suffix
     * @return $this
     */
    public function setTableName($tableName, $suffix = '')
    {
        // TODO: Implement setTableName() method.
        $this->tableName = $tableName;
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * @param $tableName
     * @return Model_BaseInterface
     */
    public function setFullTableName($tableName)
    {
        // TODO: Implement setTableName() method.
        $index = mb_substr($tableName, strlen($this->getTableName()) + 1);
        $this->tableName = $this->getTableName();
        $this->suffix = $index;
        return $this;
    }

    public function getSql()
    {
        // TODO: Implement getSql() method.
    }

    public function getInsertId()
    {
        // TODO: Implement getInsertId() method.
    }

    /**
     * @return Elasticsearch_Base
     */
    public static function getInstance()
    {
        // TODO: Implement getInstance() method.
        $className = get_called_class();
        if (empty(self::$instanceHandl[$className])) {
            self::$instanceHandl[$className] = new $className();
        }
        return self::$instanceHandl[$className];
    }

    public function getOne($filter, $condition = [])
    {
        // TODO: Implement getOne() method.
    }

    public function getRow($condition = null, $filter = '*')
    {
        // TODO: Implement getRow() method.
        $res = Http_Base::getJson(self::getSearchOneUrl() . '/' . $condition['id']);
        $res = json_decode($res, true);
        $info = var_def($res['_source'], []);
        unset($info['host']);
        unset($info['agent']);
        unset($info['datetime']);
        unset($info['ecs']);
        return $info;
    }

    public function getAll($condition = null, $filter = '*')
    {
        // TODO: Implement getAll() method.
        try {
            $res = Http_Base::requestGetJson(self::getSearchUrl(), $condition);
            $res = json_decode($res, true);
        } catch (Exception $exception) {
            $res = [];
//            $error = $exception->getMessage();
        }
        return $res;
    }

    public function insert($data = array())
    {
        // TODO: Implement insert() method.
        return false;
    }

    public function update($data, $condition = null)
    {
        // TODO: Implement update() method.
    }

    public function count($condition = null)
    {
        // TODO: Implement count() method.
    }

    public function sum($filter, $condition = null)
    {
        // TODO: Implement sum() method.
    }

    public function getDbQuery()
    {
        // TODO: Implement getDbQuery() method.
    }

    public function setDbQuery($dbQuery)
    {
        // TODO: Implement setDbQuery() method.
    }

    public function getPkId()
    {
        // TODO: Implement getPkId() method.
    }

    public function setPkId($pkId)
    {
        // TODO: Implement setPkId() method.
    }

    /**
     * 删除索引及数据
     * @return bool|string
     */
    public function clear()
    {
        try {
            $tableName = $this->getTableName();
            Http_Base::requestDelete("http://192.168.1.102:19200/{$tableName}");
            return true;
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }
        return $error;
    }

    public function delete($suffix)
    {
        return false;
//        try {
//            $tableName = $this->getTableName() . '-' . $suffix;
//            Http_Base::requestDelete("http://192.168.1.102:19200/{$tableName}");
//            return true;
//        } catch (Exception $exception) {
//            $error = $exception->getMessage();
//        }
//        return $error;
    }

    public function getSearchUrl()
    {
        $tableName = $this->getTableName();
        if (empty($this->suffix)) {
            $tableName .= '*';
        }
        return self::getBaseElasticsearchUrl() . '/' . $tableName . '/_search';
    }

    public function getSearchOneUrl()
    {
        $tableName = $this->getTableName();
        if (empty($this->suffix)) {
            $tableName .= '*';
        }
        return self::getBaseElasticsearchUrl() . '/' . $tableName . '/_doc';
    }

    /**
     * 返回要查询的字段列表
     * @return array
     */
    abstract public function getQueryTemplateFiledList();

    public function getQueryTemplate($page = null, $limit = null)
    {
        $query = [
            "_source" => static::getQueryTemplateFiledList(),
            "sort" => [
                [
                    "@timestamp" => [
                        "order" => "desc"
                    ]
                ]
            ]
        ];
        if ($page !== null && $limit !== null) {
            $query['from'] = ($page - 1) * $limit;
        }
        if ($limit !== null) {
            $query['size'] = $limit;
        }
        return $query;
    }


}
