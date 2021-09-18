<?php


Interface Model_BaseInterface
{

    public function getTableName();

    public function setTableName($tableName, $pkId);

    public function getSql();

    public function getInsertId();

    public static function getInstance();

    public function getOne($filter, $condition = []);

    public function getRow($condition = null, $filter = '*');

    public function getAll($condition = null, $filter = '*');

    public function insert($data = array());

    public function update($data, $condition = null);

    public function count($condition = null);

    public function sum($filter, $condition = null);

    /**
     * @return Db_Query|null
     */
    public function getDbQuery();

    /**
     * @param Db_Query|null $dbQuery
     */
    public function setDbQuery($dbQuery);

    /**
     * @return string
     */
    public function getPkId();

    /**
     * @param string $pkId
     */
    public function setPkId($pkId);

}