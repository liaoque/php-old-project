<?php


class DbNew_SqlParserPkId
{

    private $pkId;

    /**
     * @return mixed
     */
    public function getPkId()
    {
        return $this->pkId;
    }

    /**
     * @param mixed $pkId
     */
    public function setPkId($pkId)
    {
        $this->pkId = $pkId;
    }


    public function __construct($pkId)
    {
        $this->pkId = $pkId;
    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        $tableName = $this->pkId;
        return $tableName;
    }

}
