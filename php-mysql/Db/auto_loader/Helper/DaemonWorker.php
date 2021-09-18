<?php


class Helper_DaemonWorker extends Helper_DaemonWorkerBase
{
    public function __construct(Helper_DaemonProcess $daemonProcess)
    {
        parent::__construct($daemonProcess);
        $this->prefix = "子进程----".getmypid();
    }

    public function run($data = [])
    {
        $worker = $this->getWorker();
        if ($worker instanceof Helper_DaemonWorkerInterface) {
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
            $res = $worker->run($this->getDaemonProcess());
//            var_dump("work-run-end--------".getmypid(), Db_SqlParser::getDb()->link_id);
            if ($res !== null) {
                return $res;
            }
        }
    }
}

