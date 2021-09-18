<?php


class DbNew_SqlParserTable
{
    private $tableName;
    private $pkId;

    private static $alias = 'a';

    /**
     * @var string
     */
    private $aliasName;

    /**
     * @return string
     */
    public function getAliasName()
    {
        return $this->aliasName;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getPkId()
    {
        return $this->pkId;
    }

    /**
     * @param DbNew_SqlParserPkId $pkId
     */
    public function setPkId(DbNew_SqlParserPkId $pkId)
    {
        $this->pkId = $pkId;
    }


    public function __construct($tableName, $pkId = [])
    {
        $this->tableName = $tableName;
        if ($pkId instanceof DbNew_SqlParserPkId) {
            $pkId = new DbNew_SqlParserPkId($pkId);
        }
        $this->aliasName = self::$alias;
        $this->pkId = $pkId;
        self::incAlias();
    }

    public static function incAlias($step = 1)
    {
        $ord = ord(self::$alias);
        self::$alias = chr($ord + $step);
    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        return " {$this->tableName} ";
    }

}
