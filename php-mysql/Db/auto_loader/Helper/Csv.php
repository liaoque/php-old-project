<?php


class Helper_Csv
{
    /**
     * 创建execl
     * @param $title       Csv 得标题
     * @param array $head Csv 列的标题
     * @param array $body Csv 行得数据
     * @param string $file 要保存的文件名
     * @return string   返回文件名
     */
    public static function createXls($title, $head = [], $body = [], $file = 'php://output')
    {
        if (empty($file)) {
            $file = '/pocket_admin/upload/excel/' . $title . '.csv';
        }

        if ($file == 'php://output') {
//            $handle = fopen("test.csv", "w+");
//            $objWriter->save($file);
            $handle = fopen($file, "w+");
        } else {
            $handle = fopen(ROOT_PATH . $file, "w+");
        }
//
        fputcsv($handle, array_map(function ($item) {
            $item = iconv('utf-8', 'gbk', $item);
            return $item;
        }, $head));

        array_map(function ($info) use ($handle) {
            $info = array_map(function ($item) {
                $item = iconv('UTF-8', 'gbk', $item);
                return $item;
            }, $info);
            fputcsv($handle, $info);
        }, $body);
        @fclose($handle);
        return $file;
    }


    public static function attendXls($file, $start, $body)
    {
        $handle = fopen($file, "a+");
        array_map(function ($info) use ($handle) {
            $info = array_map(function ($item) {
                $item = iconv('utf-8', 'gb2312', $item);
                return $item;
            }, $info);
            fputcsv($handle, $info);
        }, $body);
        @fclose($handle);
    }


    public static function downloadHeader($title)
    {
        header('Content-Type: text/csv');

        header("Content-Type:application/download");//7
        header("Content-Disposition:attachment;filename={$title}.csv");//8
        header("Content-Transfer-Encoding:binary");//9
    }

    public static function load($file, $char = 'gbk')
    {
        $fd = fopen($file, "r");
        return self::input($fd, $char);
    }

    public static function input($handle, $char = 'gbk')
    {
        $out = [];
        $n = 0;
        while ($data = fgetcsv($handle, 100000)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                if ($char == 'utf-8') {
                    $str = trim(iconv('gbk', 'utf-8', $data[$i]));
                } else {
                    $str = trim($data[$i]);
                }
                $out[$n][$i] = trim($str, " 　");
            }
            $n++;
        }
        return $out;
    }

    public static function output($data, $file_name = 'todayOrder')
    {
        header('Content-Type: text/csv');
        $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');
        header('Content-Disposition: attachment;filename="' . $str . '.csv"');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $csv_data = '';
        foreach ($data as $line) {
            foreach ($line as $key => &$item) {
                $item = str_replace(',', '，', str_replace(PHP_EOL, '', $item));   //过滤生成csv文件中的(,)逗号和换行
                $item = mb_convert_encoding($item, 'gbk', 'utf-8');
            }
            $csv_data .= implode(',', $line) . PHP_EOL;
        }
        return $csv_data;
    }
}
