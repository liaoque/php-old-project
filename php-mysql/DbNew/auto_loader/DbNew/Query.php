<?php


class DbNew_Query
{
    /**
     * @var DbNew_SqlParser
     */
    private $sqlParser;

//    private
    /**
     * @var DbNew_DbDrive
     */
    private $db;
    private $lastInsertId;

    /**
     * @param $config
     * @return DbNew_Query
     * @throws Exception
     */
    public static function db($config)
    {
        $dbNewQuery = new self();
        $dbNewQuery->db = DbNew_DbDrive::db($config);
        return $dbNewQuery;
    }


    /**
     * @param $tableName
     * @param array $pkIds
     * @return DbNew_Query
     * @throws Exception
     */
    public static function table($tableName, $pkIds = [])
    {
        $dbNewSqlParser = DbNew_SqlParser::table($tableName, $pkIds);
        $dbNewQuery = new self();
        $dbNewQuery->sqlParser = $dbNewSqlParser;
        $dbNewQuery->db = DbNew_DbDrive::getDb();
        return $dbNewQuery;
    }

    public function findAll($field = [])
    {
        $dbNewSqlParser = $this->sqlParser->findAll($field);
        return $this->db->findAll($dbNewSqlParser);
    }

    public function find($id)
    {
        $dbNewSqlParser = $this->sqlParser->find($id);
        return $this->db->first($dbNewSqlParser);
    }

    public function sum($field)
    {
        $dbNewSqlParser = $this->sqlParser->sum($field);
        return $this->db->sum($dbNewSqlParser);
    }

    public function max($field)
    {
        $dbNewSqlParser = $this->sqlParser->max($field);
        return $this->db->max($dbNewSqlParser);
    }

    public function min($field)
    {
        $dbNewSqlParser = $this->sqlParser->min($field);
        return $this->db->findAll($dbNewSqlParser);
    }

    public function avg($field)
    {
        $dbNewSqlParser = $this->sqlParser->avg($field);
        return $this->db->avg($dbNewSqlParser);
    }

    public function exits()
    {
        $dbNewSqlParser = $this->sqlParser->exits();
        return $this->db->exits($dbNewSqlParser);
    }

    public function update($data)
    {
        $dbNewSqlParser = $this->sqlParser->update($data);
        return $this->db->update($dbNewSqlParser);
    }

    public function insert($data)
    {
        $dbNewSqlParser = $this->sqlParser->insert($data);
        $insert = $this->db->insert($dbNewSqlParser);
        if (!isset($data[0]) || !is_array($data[0])) {
            $this->lastInsertId = $this->db->lastInsertId();
        }
        return $insert;
    }

    public function insertId()
    {
        return $this->lastInsertId;
    }

    public function clear(){
        $this->sqlParser->clear();
    }


    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if (method_exists($this->sqlParser, $name)) {
            call_user_func_array([$this->sqlParser, $name], $arguments);
        }
        return $this;
    }

}
