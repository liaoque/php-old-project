<?php


class Db_SqlParser
{
    private static $instanceHandl = array();
    protected $tableName;

    /**
     * 获取表名
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }


    /**
     * 获取总的数量
     * @param null $condition
     * @return int|string
     */
    public function count($condition = null)
    {
        $sql = 'SELECT count(1) as count FROM ' . $this->getTableName();
        if (!empty($condition)) {
            $sql .= $this->parserSqlCondition($condition);
        }
        return $sql;
    }

    public function sum($filer, $condition = null)
    {
        $sql = " SELECT sum({$filer}) as sum FROM " . $this->getTableName();
        if (!empty($condition)) {
            $sql .= $this->parserSqlCondition($condition);
        }
        return $sql;
    }


    /**
     * 查找所有数据
     * @param null $condition
     * @param string $filter
     * @return array
     */
    public function findAll($condition = null, $filter = '*')
    {
        if (is_string($condition)) {
            $sql = $condition;
        } else {
            $sqlFilter = is_string($filter) ? $filter : (is_array($filter) ? implode(', ', $filter) : '*');
            $sql = 'SELECT ' . $sqlFilter . ' FROM ' . $this->getTableName();
            if (!empty($condition)) {
                $sql .= $this->parserSqlCondition($condition);
            }
        }
        return $sql;
    }


    /**
     * 查找一条数据
     * @param null $condition array( '表列名' => '查询条件' )
     * @param string $filter
     * @return string
     */
    public function find($condition = null, $filter = '*')
    {
        if (empty($sql)) {
            // TODO: Implement find() method.
            if (is_string($condition)) {
                $sql = $condition;
            } else {
                $sqlFilter = is_string($filter) ? $filter : (is_array($filter) ? implode(', ', $filter) : '*');
                $sql = 'SELECT ' . $sqlFilter . ' FROM ' . $this->getTableName();
                if (!empty($condition)) {
                    $sql .= $this->parserSqlCondition($condition);
                }
            }
        }
        return $sql;
    }


    /**
     * 允许传递字符串或者数组
     * $this->update('update xxx set a = 1 where a = 2');
     * $this->update(array('a' => 1), array('where'=> 'a = 1', 'order' => 'id', 'limit' => '10'))
     *  $this->update(array('a' => 1), array('where'=> ['a = 1', 'b'=>3], 'order' => 'id', 'limit' => '10'))
     * @param $data
     * @param null $condition
     * @return bool
     */
    public function update($data, $condition = null)
    {
        if (is_string($data)) {
            $sql = $data;
        } else {
            $str = $this->parserUpdateSqlCondition($condition);
            $values = array();
            foreach ($data as $k => $v) {
                if (is_numeric($k) && strpos($v, '=') !== false) {
                    $v = trim($v);
                    $values[] = preg_replace('/,$/', '', $v);
                } else {
                    $values[] = "`{$k}` = '{$v}'";
                }
            }
            $values = implode(', ', $values);
            $sql = 'UPDATE ' . $this->getTableName() . ' SET ' . $values . ' ' . $str;
        }
        return $sql;
    }

    /**
     * 简单解析Updatesql条件
     * @param string $condition
     * @return string
     */
    protected function parserUpdateSqlCondition($condition = '')
    {
        if (empty($condition)) {
            return $condition;
        }
        if (is_string($condition)) {
            return $condition;
        }
        $order = $this->parserOrder($condition['order']);
        unset($condition['order']);

        $limit = empty($condition['limit']) ? '' : ' LIMIT ' . $condition['limit'];
        unset($condition['limit']);

        if (empty($condition['where'])) {
            $condition['where'] = $condition;
        }
        $where = $this->pareserWhere($condition['where']);

        return $where . $order . $limit;
    }

    /**
     * 设置表名
     * @param $tableName
     * @return mixed
     */
    public function setTableName($tableName)
    {
        return $this->tableName = $tableName;
    }


    public function __construct($tableName, $pkId = 'id')
    {
        $this->setPkId($pkId);
        $this->setTableName($tableName);
    }

    /**
     * 设置主键
     * @param $pkId
     * @return mixed
     */
    public function setPkId($pkId)
    {
        return $this->pkId = $pkId;
    }

    public function getPkId()
    {
        return $this->pkId;
    }

    /**
     * @param $tableName
     * @param string $pkId
     * @return Db_SqlParser
     */
    public static function getInstance($tableName, $pkId = 'id')
    {
        $className = get_called_class();
        if (empty(self::$instanceHandl[$tableName])) {
            self::$instanceHandl[$tableName] = new $className($tableName, $pkId);
        }
        return self::$instanceHandl[$tableName];
    }

    /**
     * @return cls_mysql
     */
    public static function getDb()
    {
        if (empty($GLOBALS['db'])) {
            return null;
        }
        return $GLOBALS['db'];
    }

    /**
     * @param $tableName
     * @param string $pkId
     * @return Db_SqlParser
     */
    public static function getInstanceNew($tableName, $pkId = 'id')
    {
        return self::getInstance($GLOBALS['ecs']->table($tableName), $pkId);
    }

    public function insert($data = array())
    {
        if (is_string($data)) {
            $sql = $data;
        } else {
            $values = array();
            foreach ($data as $k => $v) {
                $values[$k] = "'{$v}'";
            }
            $cols = array_keys($values);
            foreach ($cols as $k => $v) {
                $cols[$k] = "`{$v}`";
            }
            $columns = implode(', ', $cols);
            $values = implode(', ', $values);
            $sql = 'INSERT INTO ' . $this->getTableName() . "({$columns})VALUES({$values})";
        }
        return $sql;
    }


    public function parserSqlCondition($condition = '')
    {
        if (empty($condition)) {
            return $condition;
        }
        if (is_string($condition)) {
            return $condition;
        }
        unset($condition['master']);
        $group = $this->parserGroup($condition['group']);
        unset($condition['group']);
        $having = $this->parserHavering($condition['having']);
        unset($condition['having']);
        $order = $this->parserOrder($condition['order']);
        unset($condition['order']);
        $limit = $this->parserLimit($condition['limit']);
        unset($condition['limit']);
        if (empty($condition['where'])) {
            unset($condition['where']);
            $condition['where'] = $condition;
        }
        $where = $this->pareserWhere($condition['where']);
        return $where . $group . $having . $order . $limit;
    }


    /**
     * 可支持写法
     * 数组： parserOrder(['id1', 'id2'])
     * 数组： parserOrder(['id1 ASC', 'id2 DESC'])
     * 字符串 parserOrder('id1, id2 DESC')
     * @param null $order
     * @param string $sort
     * @return string
     */
    protected function parserOrder($order = null, $sort = 'DESC')
    {
        $order = $order ? $order : $this->pkId;
        $order = is_array($order) ? implode(', ', $order) : $order;
        if (preg_match('/DESC|ASC$/i', $order) == false) {
            $order .= ' ' . $sort;
        }
        return ' ORDER BY ' . $order;
    }

    /**
     * 规则比较简单, 只用适用连续的AND语句， 且不带括号
     * pareserWhere(['a'=>111, 'b'=>'222']) => WHERE a = '111' AND b = '222'
     * pareserWhere(['a !='=>111, 'b >'=>'222']) => WHERE a != '111' AND b > '222'
     * pareserWhere(['a'=>111, 'b'=>[1, 2]]) => WHERE a = '111' AND b IN ('1', '2')
     * pareserWhere(['a'=>111, 'b NOT IN'=>[1, 2]]) => WHERE a = '111' AND b  NOT IN ('1', '2')
     * @param null $where
     * @return null|string
     */
    protected function pareserWhere($where = null)
    {
        $result = '';
        if (empty($where)) {
            return $result;
        }
        if (is_string($where)) {
            $result = preg_match('/WHERE/i', $where) != false ? $where : ' WHERE ' . $where;
        } elseif (is_array($where)) {
            $where = array_filter($where, function ($value) {
                return $value !== false;
            });
            if (!empty($where)) {
                $result = ' WHERE ' . implode(' AND ', $this->parserAndOfArray($where));
            }
        }
        return $result;
    }


    /**
     * 解析limit
     * 写法
     * parserLimit(0)
     * parserLimit(0， 10)
     * parserLimit([0， 10])
     * @param int $start
     * @param null $count
     * @return string
     */
    protected function parserLimit($start = null, $count = null)
    {
        if ($start === null) {
            return '';
        }
        if (is_array($start)) {
            $count = $start[1];
            $start = $start[0];
        } else {
            $count = $count ? $count : self::LIMIT;
        }
        return ' LIMIT ' . $start . ', ' . $count;
    }


    /**
     * 写法
     * parserHavering(['a'=>111, 'b'=>'222']) => HAVEING a = '111' AND b = '222'
     * parserHavering(['a !='=>111, 'b >'=>'222']) => HAVEING a != '111' AND b > '222'
     * parserHavering(['a'=>111, 'b'=>[1, 2]]) => HAVEING a = '111' AND b IN ('1', '2')
     * parserHavering(['a'=>111, 'b NOT IN'=>[1, 2]]) => HAVEING a = '111' AND b  NOT IN ('1', '2')
     * @param null $haveing
     * @return null|string
     */
    protected function parserHavering($haveing = null)
    {
        if (empty($haveing)) {
            return '';
        }
        if (is_string($haveing)) {
            $result = preg_match('/HAVING/i', $haveing) != false ? $haveing : ' HAVEING ' . $haveing;
        } elseif (is_array($haveing)) {
            $result = ' HAVING ' . implode(' AND ', $this->parserAndOfArray($haveing));
        }
        return $result;
    }


    /**
     * 支持写法
     * parserGroup('id')
     * @param null $group
     * @return string
     */
    protected function parserGroup($group = null)
    {
        if (empty($group)) {
            return '';
        }
        return ' GROUP BY ' . $group;
    }


    /**
     * 只解析成连续的 AND 语句
     * @param array $data
     * @return array
     */
    private function parserAndOfArray($data = array())
    {
        $whereDate = array();
        foreach ($data as $key => $value) {
            $key = trim($key);
            if (is_numeric($key) && is_string($value)) {
                $whereDate[] = trim($value);
            } elseif (preg_match('/^([\w\.]+)\s*(.*)$/', $key, $result)) {
                $column = $result[1];
                $symbol = empty($result[2]) ? '=' : trim($result[2]);
                if (is_array($value)) {
                    $value = '( "' . implode('", "', $value) . '" )';
                    if (strripos($symbol, 'IN') === false) {
                        $symbol = strripos($symbol, 'NOT') === false ? 'IN' : 'NOT IN';
                        $whereDate[] = $column . ' ' . $symbol . ' ' . $value;
                        continue;
                    }

                } elseif ($value instanceof Closure) {
                    $value = call_user_func($value, new Db_SqlParser($this->getTableName(), $this->getPkId()));
                    $symbol = strripos($symbol, 'NOT') === false ? 'IN' : 'NOT IN';
                    $whereDate[] = $column . ' ' . $symbol . ' ' . "({$value})";
                    continue;
                }
                $whereDate[] = $column . ' ' . $symbol . ' ' . ("'{$value}'");
            }
        }
        return $whereDate;
    }

    public function setModel($model)
    {
        $this->setTableName($GLOBALS['ecs']->table($model->getTableName()));
        $this->setPkId($model->getPkId());
        return $this;
    }

}
