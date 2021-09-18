<?php


class DbNew_SqlParserOrder
{
    private $fields;
    private $sort;
    /**
     * @var DbNew_SqlQuery
     */
    private $sqlQuery;


    /**
     * 可支持写法
     * 数组： parserOrder(['id1', 'id2'])
     * 数组： parserOrder(['id1 ASC', 'id2 DESC'])
     * 字符串 parserOrder('id1, id2 DESC')
     * @param DbNew_SqlQuery $sqlQuery
     * @param null $fields
     * @param string $sort
     */
    public function __construct(DbNew_SqlQuery $sqlQuery, $fields = null, $sort = 'DESC')
    {
        $this->sqlQuery = $sqlQuery;
        $this->fields = $fields;
        $this->sort = $sort;
    }


    protected function __toString()
    {
        $order = $this->fields;
        $order = $order ? $order :  'id';
        $order = is_array($order) ? implode(', ', $order) : $order;
        if (preg_match('/DESC|ASC$/i', $order) == false) {
            $order .= ' ' . $this->sort;
        }
        return $order;
    }
}
