<?php


class DbNew_SqlParserUpdate implements DbNew_SqlParserInterface
{
    /**
     * @var DbNew_SqlParser
     */
    private $sqlParser;

    /**
     * @return DbNew_SqlParser
     */
    public function getSqlParser()
    {
        return $this->sqlParser;
    }
    private $data;

    public function __construct(DbNew_SqlParser $sqlQuery, $data)
    {
        $this->sqlParser = $sqlQuery;
        $this->data = $data;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        $join = $this->sqlParser->parseJoin();
        $sql = "UPDATE {$this->sqlParser->getTable()} {$join} SET ";
        foreach ($this->data as $key => $value) {
            $sql = $key . '=' . $value . ' ';
        }
        return $sql . $this->sqlParser->parseCondition();
        return $this->sqlParser->parseSelect();
    }

}
