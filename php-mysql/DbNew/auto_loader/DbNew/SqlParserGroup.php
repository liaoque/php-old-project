<?php


class DbNew_SqlParserGroup
{
    private $fields;
    /**
     * @var DbNew_SqlQuery
     */
    private $sqlQuery;

    /**
     * 解析 select
     * 表名前缀自己维护, 虽然麻烦， 但有利于阅读
     * 写法
     * parserSelect('字段1， 字段2')
     * parserSelect(['字段1'， '字段2'...])
     * parserSelect(['表名1.字段1'， '表名2.字段2'， '表名3.字段3'...])
     * @param DbNew_SqlQuery $sqlQuery
     * @param array $fields
     */
    public function __construct(DbNew_SqlQuery $sqlQuery, $fields)
    {
        $this->sqlQuery = $sqlQuery;
        $this->fields = $fields;
    }

    protected function __toString()
    {
        if (empty($this->fields)) {
            return '';
        }
        if (is_array($this->fields)) {
            return ' GROUP BY ' . implode(' , ', $this->fields);
        }
        return ' GROUP BY ' . $this->fields;
    }
}
