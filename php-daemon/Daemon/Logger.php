<?php

/**
 * 需要用别的， 直接重写这个类就好了
 * Class Daemon_Logger
 */
class Daemon_Logger
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    static $level = self::ERROR;
    static $background = false;

    public static function setBackground($background)
    {
        return Daemon_Logger::$background = $background;
    }

    /**
     * 用上下文信息替换记录信息中的占位符
     * @param $message
     * @param array $context
     * @return string
     */
    static function interpolate($message, $context = array())
    {
        // 构建一个花括号包含的键名的替换数组
        $replace = array();
        foreach ($context as $key => $val) {
            // 检查该值是否可以转换为字符串
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        // 替换记录信息中的占位符，最后返回修改后的记录信息。
        return strtr($message, $replace);
    }

    /**
     * 系统无法使用。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency($message, $context = array())
    {
        self::log(self::EMERGENCY, $message, $context);
    }

    /**
     * 必须立即采取行动。
     *
     * 例如: 整个网站宕机了，数据库挂了，等等。 这应该
     * 发送短信通知警告你.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        self::log(self::ALERT, $message, $context);
    }

    /**
     * 临界条件。
     *
     * 例如: 应用组件不可用，意外的异常。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        self::log(self::CRITICAL, $message, $context);
    }

    /**
     * 运行时错误不需要马上处理，
     * 但通常应该被记录和监控。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error($message, array $context = array())
    {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * 例外事件不是错误。
     *
     * 例如: 使用过时的API，API使用不当，不合理的东西不一定是错误。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * 正常但重要的事件.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        self::log(self::NOTICE, $message, $context);
    }

    /**
     * 有趣的事件.
     *
     * 例如: 用户登录，SQL日志。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info($message, array $context = array())
    {
        self::log(self::INFO, $message, $context);
    }

    /**
     * 详细的调试信息。
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * 可任意级别记录日志。
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        $levels = [
            'emergency' => 7,
            'alert' => 6,
            'critical' => 5,
            'error' => 4,
            'warning' => 3,
            'notice' => 2,
            'info' => 1,
            'debug' => 0
        ];
        if ($levels[$level] < self::$level) {
            return;
        }
        if (!self::$background) {
            printf("level: %s, content: %s\n", $level, self::interpolate($message, $context));
            ob_flush();
            flush();
        } else {
            // 写日志文件
        }

    }

}
