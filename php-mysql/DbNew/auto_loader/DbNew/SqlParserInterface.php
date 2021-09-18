<?php


interface DbNew_SqlParserInterface
{
    public function __toString();

    /**
     * @return DbNew_SqlParser
     */
    public function getSqlParser();
}
