<?php


class DbNew_SqlParserWhere
{
    /**
     * @var DbNew_SqlQuery
     */
    private $sqlQuery;
    private $column;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var string
     */
    private $value;

    public function __toString()
    {
        if (empty($this->column)) {
            $sql = '';
        } elseif ($this->operator == 'or') {
            // 直接是 回调函数
            if (is_array($this->column)) {
                $this->column = implode(' and ', $this->column);
            }
            $sql = '(' . " {$this->column} {$this->operator} " . $this->value . ')';
        } elseif (is_string($this->column) && empty($this->operator)) {
            // 纯sql,
            $sql = $this->column;
        } elseif (is_callable($this->column)) {
            // 直接是 回调函数
            $dbNewSqlQuery = new DbNew_SqlQuery();
            call_user_func($this->column, $dbNewSqlQuery);
            $dbNewSqlQuery = trim($dbNewSqlQuery, '()');
            $sql = '(' . $dbNewSqlQuery . ')';
        } elseif (is_callable($this->value)) {
            // 带操作符的回调函数
            $dbNewSqlQuery = new DbNew_SqlQuery();
            call_user_func($this->value, $dbNewSqlQuery);
            $dbNewSqlQuery = trim($dbNewSqlQuery, '()');
            $sql = " {$this->column} {$this->operator} " . '(' . $dbNewSqlQuery . ')';
        } elseif (is_array($this->value)) {
            // 数组
            $sql = " {$this->column} {$this->operator} " . '( "' . implode('", "', $this->value) . '" )';
        } else {
            $sql = " {$this->column} {$this->operator} {$this->value} ";
        }
        return $sql;
    }

    public function __construct(DbNew_SqlQuery $sqlQuery, $column, $operator = '', $value = '')
    {
        $this->sqlQuery = $sqlQuery;
        if (is_array($column) && empty($operator)) {
            $count = count($column);
            if ($count == 2) {
                $value = $column[1];
                $operator = is_array($value) ? 'in' : '=';
            } elseif ($count == 3) {
                $value = $column[2];
                $operator = $column[1];
            }
            $column = $column[0];
        }
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value === null ? 'NULL' : $value;
    }


}
