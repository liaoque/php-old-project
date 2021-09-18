<?php


class Helper_System
{

    /**
     * 检查是否是win
     * @return bool
     */
    public static function checkWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }


    public static function runIsCli()
    {
        return PHP_SAPI === 'cli';
    }


}
