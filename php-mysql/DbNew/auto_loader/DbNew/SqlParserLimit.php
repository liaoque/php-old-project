<?php


class DbNew_SqlParserLimit
{

    private $start;
    private $count;

    const  LIMIT = 10;

    /**
     * 解析limit
     * 写法
     * parserLimit(0)
     * parserLimit(0， 10)
     * parserLimit([0， 10])
     * @param array $start
     * @param int $count
     */
    public function __construct($start = null, $count = 10)
    {
        $this->start = $start;
        $this->count = $count;
    }


    protected function __toString()
    {
        if ($this->start === null) {
            return '';
        }
        if (is_array($this->start)) {
            $count = $this->start[1];
            $start = $this->start[0];
        } else {
            $start = $this->start;
            $count = $this->count ? $this->count : self::LIMIT;
        }
        return ' LIMIT ' . $start . ', ' . $count;
    }
}
