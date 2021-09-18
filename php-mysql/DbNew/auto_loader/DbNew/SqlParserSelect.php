<?php


class DbNew_SqlParserSelect implements DbNew_SqlParserInterface
{
    /**
     * @var DbNew_SqlParser
     */
    private $sqlQuery;
    /**
     * @var DbNew_SqlParser
     */
    private $sqlParser;

    public function __construct(DbNew_SqlParser $sqlQuery)
    {
        $this->sqlParser = $sqlQuery;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return $this->sqlParser->parseSelect();
    }

}
