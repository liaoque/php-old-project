<?php


class DbNew_SqlParser
{
    /**
     * @var DbNew_SqlParserTable
     */
    private $table;
    /**
     * @var DbNew_SqlParserSelect
     */
    private $select;
    private $backSql;

    /**
     * @return DbNew_SqlParserSelect
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @var DbNew_SqlParserLimit
     */
    private $limit;
    /**
     * @var DbNew_SqlParserGroup
     */
    private $group;
    /**
     * @var DbNew_SqlParserOrder
     */
    private $order;
    /**
     * @var array
     */
    private $where;
    private $join;

    /**
     * @return DbNew_SqlParserTable
     */
    public function getTable()
    {
        return $this->table;
    }

    public static function table($tableName, $pkIds = [])
    {
        $dbNew_SqlParser = new self();
        $dbNew_SqlParser->table = new DbNew_SqlParserTable($tableName, $pkIds);
        return $dbNew_SqlParser;
    }

    public function from($tableName, $pkIds = [])
    {
        $this->table = new DbNew_SqlParserTable($tableName, $pkIds);
        return $this;
    }

    public function findAll($field = [])
    {
        return $this->get($field);
    }

    public function get($field = [])
    {
        $this->select = new DbNew_SqlParserSelect($field);
        return $this;
    }

    public function find($id)
    {
        $pkId = $this->getTable()->getPkId();
        $this->where[] = new DbNew_SqlParserWhere($pkId[0], $id);
        return $this;
    }

    public function parseJoin()
    {
        $join = '';
        foreach ($this->join as $key => $value) {
            $join .= $key . $value;
        }
        return $join;
    }

    public function parseCondition()
    {
        // TODO: Implement __toString() method.
        if (!$this->getTable()) {
            $where = $this->where ? implode(' and ', $this->where) : '';
            return $where;
        }

        $where = $this->where ? ' where ' . implode(' and ', $this->where) : '';
        $having = $this->having ? ' having ' . implode(' and ', $this->having) : '';
        $order = $this->order ? ' ORDER BY ' . implode(',', $this->order) : '';
        return $where . $this->group . $having . $order . $this->limit;
    }

    public function parseSelect()
    {
        $parseCondition = $this->parseCondition();
        $select = $this->select ? $this->select : '*';
        return "select " . $select . " from " . $this->table . $this->parseJoin() . $parseCondition;
    }

    public function first($field = [])
    {
        $this->select = new DbNew_SqlParserSelect($field);
        $this->limit = new DbNew_SqlParserLimit([0, 1]);
        return new DbNew_SqlParserSelect($this);
    }

    public function count()
    {
        $this->select = new DbNew_SqlParserSelect(['count(1) as count1']);
        return new DbNew_SqlParserSelect($this);
    }


    public function sum($field)
    {
        $this->select = new DbNew_SqlParserSelect(["sum({$field}) as sum1"]);
        return new DbNew_SqlParserSelect($this);
    }

    public function max($field)
    {
        $this->select = new DbNew_SqlParserSelect(["max({$field}) as max1"]);
        return new DbNew_SqlParserSelect($this);
    }

    public function min($field)
    {
        $this->select = new DbNew_SqlParserSelect(["min({$field}) as min1"]);
        return new DbNew_SqlParserSelect($this);
    }

    public function avg($field)
    {
        $this->select = new DbNew_SqlParserSelect(["avg({$field}) as avg1"]);
        return new DbNew_SqlParserSelect($this);
    }

    public function exits()
    {
        $dbNew_SqlParser = new DbNew_SqlParser();
        $dbNew_SqlParser->first([
            "exists({$this}) as exists1"
        ]);
        return new DbNew_SqlParserSelect($this);
    }

    public function parseWhereCondition($num, $args)
    {
        switch ($num) {
            case 3:
                list($column, $operator, $value) = $args;
                break;
            case 2:
                $arg = $args[1];
                if (is_numeric($arg) || is_string($arg)) {
                    $operator = '=';
                } else {
                    $operator = 'in';
                }
                list($column, $value) = $args;
                break;
            default:
                $column = $args[0];
                $operator = '';
                $value = '';
                break;
        }
        return [$column, $operator, $value];
    }

    public function where($where)
    {
        list($column, $operator, $value) = self::parseWhereCondition(func_num_args(), func_get_args());
        $this->where[] = new DbNew_SqlParserWhere($this, $column, $operator, $value);
        return $this;
    }

    public function orWhere($where)
    {
        $dbNewSqlParserWhere2 = new DbNew_SqlParserWhere(new DbNew_SqlParser(), func_get_args());
        $this->where = [
            new DbNew_SqlParserWhere($this,
                $this->where,
                'or',
                $dbNewSqlParserWhere2
            )
        ];
        return $this;
    }


    public function having($where)
    {
        list($column, $operator, $value) = self::parseWhereCondition(func_num_args(), func_get_args());
        $this->having[] = new DbNew_SqlParserHaving($this, $column, $operator, $value);
        return $this;
    }

    public function groupBy($where)
    {
        $this->group = new DbNew_SqlParserGroup($this, func_get_args());
        return $this;
    }

    public function orderBy($column, $sort = 'DESC')
    {
        $this->order[] = new DbNew_SqlParserOrder($this, $column, $sort);
        return $this;
    }

    public function leftJoin($table, $column1, $operator = '', $column2 = '')
    {
        $str = ' LEFT JOIN ' . new DbNew_SqlParserTable($table) . ' ON ';
        if (empty($this->join[$str])) {
            $this->join[$str] = new DbNew_SqlParserJoin($this);
        }
        if (is_callable($column1)) {
            call_user_func($column1, $this->join[$str]);
        } else {
            $this->join[$str]->on($column1, $operator, $column2);
        }
        return $this;
    }

    public function rightJoin($table, $column1, $operator = '', $column2 = '')
    {
        $str = ' RIGHT JOIN ' . new DbNew_SqlParserTable($table) . ' ON ';
        if (empty($this->join[$str])) {
            $this->join[$str] = new DbNew_SqlParserJoin($this);
        }
        if (is_callable($column1)) {
            call_user_func($column1, $this->join[$str]);
        } else {
            $this->join[$str]->on($column1, $operator, $column2);
        }
        return $this;
    }

    public function join($table, $column1, $operator = '', $column2 = '')
    {
        $str = ' INNER JOIN ' . new DbNew_SqlParserTable($table) . ' ON ';
        if (empty($this->join[$str])) {
            $this->join[$str] = new DbNew_SqlParserJoin($this);
        }
        if (is_callable($column1)) {
            call_user_func($column1, $this->join[$str]);
        } else {
            $this->join[$str]->on($column1, $operator, $column2);
        }
        return $this;
    }

    public function update($data)
    {
        return new DbNew_SqlParserUpdate($this, $data);
    }

    public function insert($data)
    {
        return new DbNew_SqlParserInsert($this, $data);
    }

    public function clear($sql)
    {
        $this->select = null;
        $this->limit = null;
        $this->group = null;
        $this->order = null;
        $this->where = [];
        $this->join = [];

        // 备份一下sql，以debug的时候查看解析的sql结果
        $this->backSql = $sql;
    }
}
