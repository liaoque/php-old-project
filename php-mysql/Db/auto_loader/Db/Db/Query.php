<?php


class Db_Query
{

    private static $instanceHandl = array();

    protected $dbParser;
    protected $insert_id;
    protected $sql;

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }


    public static function myCatMaster($toMaster)
    {
        if (is_string($toMaster)) {
            return;
        } elseif (is_array($toMaster)) {
            $toMaster = !empty($toMaster['master']);
        }
        $mater = '';
        if (!empty($toMaster)) {
            $mater = '/*#mycat:db_type=master*/ ';
        }
        return $mater;
    }

    /**
     * @return mixed
     */
    public function getInsertId()
    {
        return $this->insert_id;
    }

    /**
     * @param $tableName
     * @param string $pkId
     * @return Db_Query
     */
    public static function getInstance($tableName, $pkId = 'id')
    {
        $className = get_called_class();
        if (empty(self::$instanceHandl[$tableName])) {
            self::$instanceHandl[$tableName] = new $className($tableName, $pkId);
        }
        return self::$instanceHandl[$tableName];
    }

    public function __construct($tableName, $pkId = 'id')
    {
        $this->dbParser = Db_SqlParser::getInstanceNew($tableName, $pkId);
    }
    
    public function getOne($filter, $condition = [])
    {
        $mater = self::myCatMaster($condition);
        if($mater){
            unset($condition['master']);
        }
        $this->sql = $sql = $mater . $this->dbParser->find($condition, $filter);
        return Db_SqlParser::getDb()->getOne($sql);
    }

    public function getRow($condition = null, $filter = '*')
    {
        $mater = self::myCatMaster($condition);
        if($mater){
            unset($condition['master']);
        }
        $this->sql = $sql = $mater . $this->dbParser->find($condition, $filter);
        return Db_SqlParser::getDb()->getRow($sql);
    }

    public function getAll($condition = null, $filter = '*')
    {
        $mater = self::myCatMaster($condition);
        if($mater){
            unset($condition['master']);
        }
        $this->sql = $sql = $mater . $this->dbParser->findAll($condition, $filter);
        return Db_SqlParser::getDb()->getAll($sql);
    }

    public function insert($data = array())
    {
        $this->sql = $sql = $this->dbParser->insert($data);
        $cls_mysql = Db_SqlParser::getDb();
        $query = $cls_mysql->query($sql);
        if ($query) {
            $this->insert_id = $cls_mysql->insert_id();
        }
        return $query;
    }


    public function update($data, $condition = null)
    {
        $this->sql = $sql = $this->dbParser->update($data, $condition);
        $cls_mysql = Db_SqlParser::getDb();
        $query = $cls_mysql->query($sql);
        return $query;
    }

    public function count($condition = null)
    {
        $mater = self::myCatMaster($condition);
        if($mater){
            unset($condition['master']);
        }
        $this->sql = $sql = $mater . $this->dbParser->count($condition);
        $cls_mysql = Db_SqlParser::getDb();
        $query = $cls_mysql->getOne($sql);
        return $query ? $query : 0;
    }

    public function sum($filter, $condition = null)
    {
        $mater = self::myCatMaster($condition);
        if($mater){
            unset($condition['master']);
        }
        $this->sql = $sql = $mater . $this->dbParser->sum($filter, $condition);
        $cls_mysql = Db_SqlParser::getDb();
        $query = $cls_mysql->getOne($sql);
        return $query ? $query : 0;
    }

    public function deleteByPkId($id)
    {
        $tableName = $this->dbParser->getTableName();
        $pkId = $this->dbParser->getPkId();
        $this->sql = $sql = "delete from {$tableName} where {$pkId} = '{$id}'";
        $cls_mysql = Db_SqlParser::getDb();
        return $cls_mysql->query($sql);
    }

}
