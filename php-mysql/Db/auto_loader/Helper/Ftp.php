<?php


class Helper_Ftp
{

    /**
     * 通过文件句柄来上传 ftp
     * @param $fd
     * @param $sUri
     * @param string $sDate
     * @return bool|string
     */
    public function saveToFtpFPut($fd, $sUri, $sDate = '')
    {
        try{
            $array = array("101", "91","92");
            foreach ($array as $val) {
                @fseek($fd, 0);
                $sIp = '192.168.1.' . $val;
                $oConnectId = ftp_connect($sIp, '21');
                if (empty($oConnectId)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步-服务器无法连接', ['result' => $oConnectId,"ip"=>$sIp]
                    ]);
                }
                $oResult = ftp_login($oConnectId, '帐号', '密码');
                if (empty($oResult)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步-登陆失败', ['result' => $oResult]
                    ]);
                }
                ftp_pasv($oConnectId, TRUE);
                $sDate = empty($sDate) ? date("Y-m-d") : $sDate;
                $oResult = @ftp_mkdir($oConnectId, 'api_upload' . '/' . $sDate);
                if (empty($oResult)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步-创建文件夹', ['result' => $oResult]
                    ]);
                }
                $file = 'api_upload/' . $sDate . '/' . basename($sUri);
                $oResult = @ftp_fput($oConnectId, $file, $fd, FTP_BINARY);
                if (empty($oResult)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步结果-失败', [
                            'result' => $oResult, 'ip' => $sIp,
                            'connet_id' => $oConnectId,
                            'file' => base_path('storage') . '/' . $sUri
                        ]
                    ]);
                }
//                给文件666权限， 让其他用户可读写
                @ftp_chmod($oConnectId, 0666, $file);
                @ftp_close($oConnectId);
            }
        }catch (Exception $exception){
            Log_Error::write(Log_Error::FTP_ERROR, [
                'ftp操作异常', [
                    'error' => $exception->getMessage(),
                    "IP"=>$sIp
                ]
            ]);
            $oResult= false;
        }
        return $oResult == false ? false : 'files/api_upload/' . $sDate . '/' . basename($sUri);
    }

    /**
     * 用户更换 fastDfs的文件内容
     * @param $oFd
     * @param $sFastDfsFileName 只允许 fastDfs的文件被替换
     * @return bool
     */
    public static function updateFastDfsFtpFPut($oFd, $sFastDfsFileName)
    {
        $oResult = false;
        if(empty($oFd)){
            return false;
        }
        if(!preg_match('/^group\d+/', $sFastDfsFileName) && !preg_match('/^fast_upload/', $sFastDfsFileName)){
            return false;
        }
        $sIp = '';
        try{
            $array = array("101", "91","92");
            foreach ($array as $val) {
                @fseek($oFd, 0);
                $sIp = '192.168.1.' . $val;
                $oConnectId = ftp_connect($sIp, '21');
                if (empty($oConnectId)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步-服务器无法连接', ['result' => $oConnectId,"ip"=>$sIp]
                    ]);
                    break;
                }
//                这里的用户千万别动
                $oResult = ftp_login($oConnectId, '帐号', '密码');
                if (empty($oResult)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步-登陆失败', ['result' => $oResult]
                    ]);
                    @ftp_close($oConnectId);
                    break;
                }
                ftp_pasv($oConnectId, TRUE);
                $file = preg_replace('/^group\d+\/M00/', '', $sFastDfsFileName);
                $file = preg_replace('/!!.+/', '', $file);
                $oResult = @ftp_fput($oConnectId, $file, $oFd, FTP_BINARY);
                if (empty($oResult)) {
                    Log_Error::write(Log_Error::FTP_ERROR, [
                        'ftp同步结果-失败', [
                            'result' => $oResult,
                            'ip' => $sIp,
                            'file' => $file
                        ]
                    ]);
                    @ftp_close($oConnectId);
                    break;
                }
//                给文件666权限， 让其他用户可读写
                @ftp_chmod($oConnectId, 0666, $file);
                @ftp_close($oConnectId);
            }
        }catch (Exception $exception){
            Log_Error::write(Log_Error::FTP_ERROR, [
                'ftp操作异常', [
                    'error' => $exception->getMessage(),
                    "IP"=>$sIp
                ]
            ]);
            $oResult= false;
        }
        return $oResult;
    }


}
