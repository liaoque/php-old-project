<?php


class Helper_Uploader
{
    public static function getImageType(){
        return [
            '.gif',
            '.jpg',
            '.jpeg',
            '.png',
        ];
    }

    public static function getFileType($picname){
        $type = substr($picname, strrpos($picname, "."));
        $type = strtolower($type);
        return $type;
    }

    public static function checkPic($picName)
    {
        if ((isset($_FILES[$picName]['error']) && $_FILES[$picName]['error'] == 0) ||
            (!isset($_FILES[$picName]['error']) && isset($_FILES[$picName]['tmp_name']) &&
                $_FILES[$picName]['tmp_name'] != 'none')) {
            $_picname = $_FILES[$picName]['name'];
            $picsize = $_FILES[$_picname]['size'];
            if ($_picname != "") {
                if ($picsize > 3024000) {
                    echo json_encode(array('error' => '1', 'info' => '图片大小不能超过3M'));
                    exit;
                }
            }
            $type = self::getFileType($_picname);
            if (!in_array($type, self::getImageType())) {
                echo json_encode(array('error' => '1', 'info' => '图片格式不对'));
                exit;
            }
        }
    }

    /**
     * 用于表单上传文件
     * @param $picNameList
     * $uploadPicList = Help_Uploader::uploadPic([
     *       'pic', 'pic_small', 'share_icon'
     * ]);
     * if ($uploadPicList) {
     *   $pic_small = var_def($uploadPicList['pic_small']['src'], '');
     *   $pic = var_def($uploadPicList['pic']['src'], '');
     *   $share_icon = var_def($uploadPicList['share_icon']['src'], '');
     *   }
     * @return array|bool|float|int|mixed|stdClass|string|null
     */
    public static function uploadPic($picNameList)
    {
        $list = [];
        foreach ($picNameList as $picName) {
            if (empty($_FILES[$picName]) || empty($_FILES[$picName]['tmp_name'])) {
                continue;
            }
            $picname = $_FILES[$picName]['name'];
            $type = self::getFileType($picname);
            if(in_array($type, self::getImageType())){
                self::checkPic($picName);
            }
        }
        foreach ($picNameList as $picName) {
            if (empty($_FILES[$picName]) || empty($_FILES[$picName]['tmp_name'])) {
                continue;
            }
            $_picName = $_FILES[$picName]['name'];
            $picFile = ROOT_PATH . "/upload/" . $_picName;
            $res = move_uploaded_file($_FILES[$picName]['tmp_name'], $picFile);
            if(empty($res)){
                continue;
            }
            $list[$picName] = $picFile;
        }
        $result = [];
        if($list){

            $result = Http_Base::fastDfsCreateForce($list);
        }
        foreach ($list as $picName => $val) {
            $picFile = $list[$picName];
            @unlink($picFile);
        }
        return $result;
    }


    /**
     * 用于上传连接, 或者文件路径，js这种
     * @param $picNameList
     *      [
     *          'http://192.168.1.130:22080/group1/M00/00/02/wKgBZl8Xy6mAcOm4AABdkYZ-HGw704.png!!5f1956abafccf.png',
     *          '本地图片绝对路径'
     *      ]
     * $uploadPicFile = Help_Uploader::uploadPicFile([
     *   $filename1, $filename2
     *   ]);
     * $uploadPicFile = Help_Uploader::uploadPicFile([
     *   'pic' => $filename1, 'share_icon' => $filename2
     *   ]);
     * @return array|bool|float|int|mixed|stdClass|string|null
     */
    public static function uploadPicFile($picNameList)
    {

        $list = [];
        $resultList = [];
        $dirname = dirname(ROOT_PATH);
        foreach ($picNameList as $picName => $fileName) {
            if (empty($fileName)) {
                continue;
            }
            if (preg_match('/^group\d+\//', $fileName)) {
//                如果是图片服务器存在的图片，直接返回
                $resultList[$picName] = [
                    'src' => $fileName,
                    'url' => image($fileName),
                ];
            } else {
                if (file_exists($fileName)) {
//                需要上传到图片服务器
                    $list[$picName] = $fileName;
                    continue;
                } elseif (file_exists(ROOT_PATH . $fileName)) {
//                需要上传到图片服务器
                    $list[$picName] = ROOT_PATH . $fileName;
                    continue;
                }elseif (file_exists($dirname . $fileName)) {
//                需要上传到图片服务器
                    $list[$picName] = $dirname  . $fileName;
                    continue;
                } elseif (mb_substr($fileName, 0, 4) == 'http') {
                    $list[$picName] = $fileName;
                    continue;
                }
            }
        }
        if (empty($list)) {
            return $resultList;
        }
        $result = Http_Base::fastDfsCreateForce($list);
        foreach ($list as $picName => $val) {
            $picFile = $list[$picName];
            @unlink($picFile);
        }
        return $result + $resultList;
    }


    public static function deCodeFileArray($list)
    {
        $delist = [];
        foreach ($list as $key => $value) {
            $pos = strpos( $key, '___');
            if ($pos === false) {
                $delist[$key] = $value;
            } else {
                $perKey = mb_substr($key, 0, $pos);
                $afterKey = mb_substr($key, $pos +3);
                if (is_numeric($afterKey)) {
                    $delist[$perKey][$afterKey] = $value;
                } else {
                    if(empty($delist[$perKey] )){
                        $delist[$perKey] = [];
                    }
                    $res = self::deCodeFileArray([$afterKey => $value]);
                    if(empty($delist[$perKey][key($res)])){
                        $delist[$perKey][key($res)] = [];
                    }
                    $current = current($res);
                    if(is_array($current)){
                        $delist[$perKey][key($res)] += $current;
                    }else{
                        $delist[$perKey][key($res)] = $current;
                    }
                }
            }
        }
        return $delist;
    }

    public static function enCodeFileArray($picNameList, $prefix = '')
    {
        $list = [];
        foreach ($picNameList as $key => $value) {
            if (is_array($value)) {
                $_list = self::enCodeFileArray($value, $prefix.$key . '___');
                $list = $list + $_list;
            } else {
                $list[$prefix . $key] = $value;
            }
        }
        return $list;
    }


    public static function uploadPicFileExt($picNameList)
    {
        $enCodeFileArray = self::enCodeFileArray($picNameList);
        $list = self::uploadPicFile($enCodeFileArray);
//        var_dump($list);
        $deCodeFileArray = self::deCodeFileArray($list);
        return $deCodeFileArray;
    }

    /**
     * 上传文件, 替换 fast_dfs的 对应的文件
     * @param $fileName
     * @param $sFastDfsFileName
     * @return bool
     */
    public static function updateFastDfsPic($fileName, $sFastDfsFileName)
    {
        if(!file_exists($fileName)){
            return false;
        }
        $fd = fopen($fileName, 'r');
        $result = Helper_Ftp::updateFastDfsFtpFPut($fd, $sFastDfsFileName);
        fclose($fd);
        return $result;
    }




}
