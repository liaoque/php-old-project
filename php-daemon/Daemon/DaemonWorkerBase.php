<?php


abstract class Daemon_DaemonWorkerBase
{
    private $worker;
    private $num;
    private $dbSettings;
    private $daemonProcess;
    protected $prefix;

    /**
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * @return mixed
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @return mixed
     */
    public function getDbSettings()
    {
        return $this->dbSettings;
    }

    /**
     * @return Daemon_DaemonProcess
     */
    public function getDaemonProcess()
    {
        return $this->daemonProcess;
    }

    public function __construct(Daemon_DaemonProcess $daemonProcess)
    {
//        $daemonProcess = $daemonProcess;
        $daemon = $daemonProcess->getDaemon();
        $className = $daemon->getClassname();
        $this->worker = new $className;
        $this->num = $daemonProcess->getNum();
        $this->daemonProcess = $daemonProcess;
        $daemonProcess->setWorker($this);
    }

    public function init()
    {
//        mysql 会话保持， 防止短连接断开
//        var_dump("{$this->prefix}---ping-mysql-start----", $GLOBALS['db']->link_id);
//        $result = Db_SqlParser::getDb()->ping();
//        if (empty($result) || empty($this->dbSettings)) {
//            $this->dbSettings = null;
//            global $db_host, $db_user, $db_pass, $db_name;
//            include(ROOT_PATH . '/data/config.php');
//            Db_SqlParser::getDb()->close();
//            $GLOBALS['sess']->db = $db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
//            $GLOBALS['db'] = $db;
//            $this->dbSettings = $db->settings;
//        }
//        var_dump("{$this->prefix}---ping-mysql-end----{$result}");
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function go($data = [])
    {
        static::init();
        return $this->run($data);
    }

    abstract function run($data = []);
}

