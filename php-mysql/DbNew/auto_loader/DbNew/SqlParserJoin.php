<?php


class DbNew_SqlParserJoin
{
    /**
     * @var DbNew_SqlQuery
     */
    private $sqlQuery;
    /**
     * @var array
     */
    private $join;

    public function __construct(DbNew_SqlQuery $sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
    }

    public function on($column1, $operator, $column2 = '')
    {
        $this->join[] = [
            ' and ',
            "{$column1} {$operator} {$column2}"
        ];
        return $this;
    }

    public function orOn($column1, $operator, $column2 = '')
    {
        $this->join[] = [
            ' or ',
            "{$column1} {$operator} {$column2}"
        ];
        return $this;
    }

    protected function __toString()
    {
        $string = '';
        foreach ($this->join as $key => $value){
            if($key == 0){
                $string = $value[1];
            }else{
                $string .= $value[0] .' '. $value[1];
            }
        }
        return $string;
    }
}
