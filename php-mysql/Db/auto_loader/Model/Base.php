<?php


class Model_Base implements Model_BaseInterface
{
    public $dbQuery = null;
    public $tableName = '';
    public $pkId = 'id';
    private static $instanceHandl;
//    private static $instanceHandl = array();

    public function __construct($tableName = '', $pkId = '')
    {
        if ($tableName) {
            $this->setTableName($tableName);
        }
        if ($pkId) {
            $this->setPkId($pkId);
        }
        $this->setDbQuery(new Db_Query(
            $this->getTableName(),
            $this->getPkId()
        ));
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName, $pkId)
    {
        $this->tableName = $tableName;
        $this->pkId = $pkId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->getDbQuery()->getSql();
    }

    /**
     * @return mixed
     */
    public function getInsertId()
    {
        return $this->getDbQuery()->getInsertId();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        $className = get_called_class();
        if (empty(self::$instanceHandl[$className])) {
            self::$instanceHandl[$className] = new $className();
        }
        return self::$instanceHandl[$className];
    }

    public function getOne($filter, $condition = [])
    {
        return $this->getDbQuery()->getOne($filter, $condition);
    }

    public function getRow($condition = null, $filter = '*')
    {
        return $this->getDbQuery()->getRow($condition, $filter);
    }

    public function getAll($condition = null, $filter = '*')
    {
        return $this->getDbQuery()->getAll($condition, $filter);
    }

    public function insert($data = array())
    {
        return $this->getDbQuery()->insert($data);
    }

    public function update($data, $condition = null)
    {
        return $this->getDbQuery()->update($data, $condition);
    }


    public function count($condition = null)
    {
        return $this->getDbQuery()->count($condition);
    }

    public function sum($filter, $condition = null)
    {
        return $this->getDbQuery()->sum($filter, $condition);
    }

    public function deleteByPkId($id)
    {
        return $this->getDbQuery()->deleteByPkId($id);
    }

    /**
     * @return Db_Query|null
     */
    public function getDbQuery()
    {
        return $this->dbQuery;
    }

    /**
     * @param Db_Query|null $dbQuery
     */
    public function setDbQuery($dbQuery)
    {
        $this->dbQuery = $dbQuery;
    }

    /**
     * @return string
     */
    public function getPkId()
    {
        return $this->pkId;
    }

    /**
     * @param $pkId
     */
    public function setPkId($pkId)
    {
        $this->pkId = $pkId;
    }

    /**
     * @param $id
     * @param string $filter
     * @return array|bool
     */
    public function infoByPkId($id, $filter = '*'){
        return $this->getRow([
            $this->getPkId() => $id
        ], $filter);
    }
}