<?php


class DbNew_SqlParserInsert implements DbNew_SqlParserInterface
{

    private $data;
    /**
     * @var DbNew_SqlParser
     */
    private $sqlParser;

    public function __construct(DbNew_SqlParser $sqlQuery, $data)
    {
        $this->sqlParser = $sqlQuery;
        $this->data = $data;
    }

    public function __toString()
    {
        $data = $this->data;
        if (is_callable($data)) {
            // 回调
            $dbNewSqlParser = new DbNew_SqlParser();
            call_user_func($data, $dbNewSqlParser);
            // 获取sql语句的查询字段
            $columns = $dbNewSqlParser->getSelect()->getFields();
            $cols = array_map(function ($v) {
                return "`{$v}`";
            }, $columns);
            $columns = implode(', ', $cols);
            $sql = 'INSERT INTO ' . $this->getTable() . "({$columns})VALUES{$dbNewSqlParser}";
        } elseif (is_string($data)) {
            $sql = $data;
        } else {
            if (!isset($data[0]) || !is_array($data[0])) {
                $data = [$data];
            }
            $cols = array_keys($data[0]);
            $cols = array_map(function ($v) {
                return "`{$v}`";
            }, $cols);
            $columns = implode(', ', $cols);
            $data = array_map(function ($item) {
                $item = array_map(function ($v) {
                    return "'{$v}'";
                }, $item);
                return '(' . implode(', ', $item) . ')';
            }, $data);
            $data = implode(' ', $data);
            $sql = 'INSERT INTO ' . $this->getTable() . "({$columns})VALUES{$data}";
        }
        return $sql;
    }

}
