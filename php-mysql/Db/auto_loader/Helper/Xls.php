<?php


class Helper_Xls
{
    /**
     * 创建execl
     * @param $title       xls 得标题
     * @param array $head xls 列的标题
     * @param array $body xls 行得数据
     * @param string $file 要保存的文件名
     * @return string   返回文件名
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public static function createXls($title, $head = [], $body = [], $file = 'php://output')
    {
        include_once(ROOT_PATH . "/plugins/Classes/PHPExcel.php");
        $PHPExcel = new PHPExcel();
        $PHPExcel->getProperties()->setCreator("作者mzq")
            ->setLastModifiedBy("作者mzq")
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription($title)
            ->setKeywords($title);
        $PHPExcel_Worksheet = $PHPExcel->setActiveSheetIndex(0);
        $pre = 65;
        array_walk($head, function ($value, $key) use ($pre, $PHPExcel_Worksheet) {
            $prefix = '';
            if ($prefixKey = intval($key / 26)) {
                $prefix = chr(64 + $prefixKey);
            }
            $pre += $key % 26;
            $PHPExcel_Worksheet->setCellValue($prefix . chr($pre) . '1', $value);
        });

        array_walk($body, function ($info, $key) use ($PHPExcel) {
            $setActiveSheetIndex = $PHPExcel->setActiveSheetIndex(0);
            $key += 2;
            foreach ($info as $key2 => $value) {
                $pre = 65;
                $prefix = '';
                if ($prefixKey = intval($key2 / 26)) {
                    $prefix = chr(64 + $prefixKey);
                }
                $pre += $key2 % 26;
                $setActiveSheetIndex->setCellValue($prefix . chr($pre) . $key, $value);
            }
        });

        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        if (empty($file)) {
            $file = '/pocket_admin/upload/excel/' . $title . '.xlsx';
        }
        if ($file == 'php://output') {
            $objWriter->save($file);
        } else {
            $objWriter->save(ROOT_PATH . $file);
        }

        return $file;
    }


    public static function attendXls($file, $start, $body)
    {
        include_once(ROOT_PATH . "/plugins/Classes/PHPExcel.php");
        try {
            $inputFileType = \PHPExcel_IOFactory::identify($file);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($file);
        } catch (\Exception $e) {
            die('加载文件发生错误："' . pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }


        array_walk($body, function ($info, $key) use ($start, $objPHPExcel) {
//            $setActiveSheetIndex = $objPHPExcel->getActiveSheet();
//            $setActiveSheetIndex->insertNewRowBefore($start,1);
            $setActiveSheetIndex = $objPHPExcel->setActiveSheetIndex(0);
            $key += $start + 2;
            foreach ($info as $key2 => $value) {
                $pre = 65;
                $prefix = '';
                if ($prefixKey = intval($key2 / 26)) {
                    $prefix = chr(64 + $prefixKey);
                }
                $pre += $key2 % 26;
                $setActiveSheetIndex->setCellValue($prefix . chr($pre) . $key, $value);
            }
        });

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file);
    }


    public static function downloadHeader($title)
    {
        header("Pragma: public");   //1

        header("Expires: 0"); //2
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");//3

        header("Content-Type:application/force-download");//4
        header("Content-Type:application/octet-stream");//5
        header("Content-Type:application/vnd.ms-excel;");//6

        header("Content-Type:application/download");//7
        header("Content-Disposition:attachment;filename={$title}.xlsx");//8
        header("Content-Transfer-Encoding:binary");//9
    }


    public static function load($file)
    {
        if (!class_exists('PHPExcel')) {
            include_once(ROOT_PATH . "/plugins/Classes/PHPExcel.php");
        }
        $objPHPExcel = PHPExcel_IOFactory::load($file);

        $rowCount = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;//获取表格列数
        $columnCount = $objPHPExcel->getActiveSheet()->getHighestColumn();

        $list = [];
        for ($row = 1; $row < $rowCount; $row++) {
            $dataArr = [];
            for ($column = 'A'; $column <= $columnCount; $column++) {
                $item = trim($objPHPExcel->getActiveSheet()->getCell($column . $row)->getValue());
                $dataArr[] = trim($item, " 　");
            }
            $list[] = $dataArr;
        }
        return $list;
    }


}
