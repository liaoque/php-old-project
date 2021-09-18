<?php


abstract class Helper_DaemonWorkerBase
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
     * @return Helper_DaemonProcess
     */
    public function getDaemonProcess()
    {
        return $this->daemonProcess;
    }

    public function __construct(Helper_DaemonProcess $daemonProcess)
    {
//        $daemonProcess = $daemonProcess;
        $daemon = $daemonProcess->getDeamon();
        $className = $daemon->getClassname();
        $this->worker = new $className;
        $this->num = $daemonProcess->getNum();
        $this->daemonProcess = $daemonProcess;
        $daemonProcess->setWorker($this);
    }

    public function init()
    {
//        var_dump("{$this->prefix}---ping-mysql-start----", $GLOBALS['db']->link_id);
        $result = Db_SqlParser::getDb()->ping();
        if (empty($result) || empty($this->dbSettings)) {
            $this->dbSettings = null;
            global $db_host, $db_user, $db_pass, $db_name;
            include(ROOT_PATH . '/data/config.php');
            Db_SqlParser::getDb()->close();
            $GLOBALS['sess']->db = $db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
            $GLOBALS['db'] = $db;
            $this->dbSettings = $db->settings;
        }
//        var_dump("{$this->prefix}---ping-mysql-end----{$result}");
    }

    public function go($data = [])
    {
        static::init();
        return $this->run($data);
//        $worker = $this->worker;
//        if ($worker instanceof Helper_DaemonWorkerInterface) {
//            if (empty($this->dbSettings)) {
//                $db_host = $db_user = $db_pass = $db_name = null;
//                include(ROOT_PATH . '/data/config.php');
////            global $db_host,$db_user, $db_pass, $db_name;
//                var_dump(getmypid(), "------------", $db_host, $db_user, $db_pass, $db_name);
//                $GLOBALS['sess']->db = $db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
//                Db_SqlParser::getDb()->close();
//                $GLOBALS['db'] = $db;
//                $this->dbSettings = $db->settings;
//                var_dump(getmypid(), Db_SqlParser::getDb()->settings);
//            }
//            $res = $worker->run($this->daemonProcess);
//            var_dump(getmypid(), Db_SqlParser::getDb()->link_id);
//            if ($res !== null) {
//                return $res;
//            }
//        }
    }

    abstract function run($data = []);
}

