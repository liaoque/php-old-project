<?php

/**
 * 这个类都要自己改造的
 * Class DbNew_DbDrive
 */
class DbNew_DbDrive
{
    /**
     * @var mixed
     */
    private $db;

    private static $instanceHandl = array();


    /**
     * @return DbNew_DbDrive
     * @throws Exception
     */
    public static function getDb()
    {
        // $GLOBALS['db'] 一般是项目自带的连接变量, 目标是公用项目自带链接
        // 不要去多创建多个链接

        if (empty($GLOBALS['db'])) {
            throw new Exception("请自定义数据库连接");
        }
        if (empty(self::$instanceHandl['default'])) {
            $dbNewDbDrive = new self();
            $dbNewDbDrive->db = $GLOBALS['db'];
            self::$instanceHandl['default'] = $dbNewDbDrive;
        }
        return self::$instanceHandl['default'];
    }

    /**
     * @param $config
     * @return DbNew_DbDrive
     * @throws Exception
     */
    public static function db($config){
        // 这里是从库或者其他库， 指定的数据库
        // 如果没有连接， 请自定义连接
        throw new Exception("自定义连接");
        if (empty(self::$instanceHandl[$config])) {
            $dbNewDbDrive = new self();
            $dbNewDbDrive->db = $config;
            self::$instanceHandl[$config] = $dbNewDbDrive;
        }
        return self::$instanceHandl[$config];
    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function findAll(DbNew_SqlParserInterface $sqlParser){
        $sql = $sqlParser . '';
        // 执行sql

        // 保存执行的sql
        $sqlParser->getSqlParser()->clear();
        return [];
    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function first(DbNew_SqlParserInterface $sqlParser){
        return [];
    }


    /**
     * @return int
     */
    public function lastInsertId(){
        return 1;
    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function find(DbNew_SqlParserInterface $sqlParser)
    {

    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function sum(DbNew_SqlParserInterface $sqlParser)
    {

    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function max(DbNew_SqlParserInterface $sqlParser)
    {

    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function min(DbNew_SqlParserInterface $sqlParser)
    {

    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function avg(DbNew_SqlParserInterface $sqlParser)
    {

    }

    /**
     * @param DbNew_SqlParserInterface
     * @return mixed
     */
    public function exits(DbNew_SqlParserInterface $sqlParser)
    {

    }

}
