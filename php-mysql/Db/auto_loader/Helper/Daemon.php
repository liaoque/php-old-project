<?php


class Helper_Daemon
{
    private $uid = 501;
    private $gid = 501;

    private $win;

    // 开启进程数
    private $num = 5;

    // 执行任务类
    private $classname;

    // 是否作为守护进程
    private $isDeamon;

    // 子进程
    private $processes = [];

    /**
     * @return bool
     */
    public function isDeamon()
    {
        return $this->isDeamon;
    }

    /**
     * @return array
     */
    public function getProcesses()
    {
        return $this->processes;
    }

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
    public function getClassname()
    {
        return $this->classname;
    }


    /**
     * Helper_Daemon constructor.
     * @param $className  执行的人物名字
     * @param int $num 开启子进程数量
     * @param bool $isDeamon 是否后台守护进程
     * @param int $uid 脚本的所属用户
     * @param int $gid 所属组
     */
    public function __construct($className, $num = 1, $isDeamon = false, $uid = 501, $gid = 501)
    {
        // 检查是否是 win系统
        $this->win = Helper_System::checkWin();
        if ($this->win) {
            die('操作系统必须是linux');
        }
        $this->uid = $uid;
        $this->isDeamon = $isDeamon;
        $this->gid = $gid;
        $this->num = $num;
        $this->classname = $className;
        $this->signal();
        Log_Info::write(Log_Info::WX_USER_SUBSCRIBE_DAEMON, date('Y-m-d H:i:s'). "启动进程");
    }

    /**
     * 设置信号捕获
     */
    public function signal()
    {
//        pcntl_signal(SIGHUP, function ($signo) /*use ()*/ {
//            //echo "\n This signal is called. [$signo] \n";
//            printf("The process has been SIGHUP.\n");
//            var_dump($this);
//            self::stop();
//        });

        pcntl_signal(SIGCHLD, function ($signo) /*use ()*/ {
//            var_dump("收到子进程退出信号 SIGCHLD.\n");
//            var_dump(getmypid(), $this, Db_SqlParser::getDb()->link_id);

            $this->processes = array_filter($this->processes, function ($process) {
                // 如果需要作为守护进程， 重启子进程， 改这部分代码
                if ($process->statusProcess()) {
                    return false;
                }
                return true;
            });
//            var_dump($this->processes);
//            ob_flush();
//            $this->num = count($this->processes);
            gc_collect_cycles();
        });

        pcntl_signal(SIGTERM, function ($signo) /*use ()*/ {
            //echo "\n This signal is called. [$signo] \n";
//            printf("收到退出信号.\n");
//            var_dump(getmypid(), $this);
            self::stop();
//            ob_flush();
        });
    }

    private function daemon()
    {
        $processes = [];
        for ($i = 0; $i < $this->num; $i++) {
            $daemonProcess = Helper_DaemonProcess::create($this);
            $master = $daemonProcess->isMaster();
            $pid = $daemonProcess->getPid();
            $daemonProcess->setNum($i);
            if ($master) {
                // 主进程
                if ($pid == -1) {
                    self::stop($processes);
                    return -1;
                } else {
                    pcntl_wait($status, WNOHANG);
                    $this->processes[] = $daemonProcess;
                }
            } else {
                // 子进程
                return $daemonProcess;
            }
        }
//        $this->processes = $processes;
        return 0;
    }

    private function run($daemonProcess)
    {
        static $masterDaemonProcess = null;
        if ($daemonProcess instanceof Helper_DaemonProcess) {
            // 子进程
            static $res = null;
            while (true) {
//                printf("The process begin.\n");
                pcntl_signal_dispatch();
                if (!$worker = $daemonProcess->getWorker()) {
                    $worker = new Helper_DaemonWorker($daemonProcess);
                }
                $res = $worker->go();
//                printf("The process end.\n");
                if ($res === false) {
                    break;
                }
            }
            if (!$this->isDeamon) {
                return false;
            }
        } else {
            // 主进程
//            printf("The master-process begin.\n");
            pcntl_signal_dispatch();
            /* if(empty($this->isDeamon) && empty($this->processes)){
                 // 不是守护进程 && 子进程已经全部结束
                 // 结束master进程
                 var_dump("子进程全部结束, 关闭自己");
                 return false;
             }*/
            if(empty($masterDaemonProcess)){
                $masterDaemonProcess = new Helper_DaemonProcess($this);
            }
            if (!$worker = $masterDaemonProcess->getWorker()) {
                $worker = new Helper_DaemonWorkerMaster($masterDaemonProcess);
            }
            $res = $worker->go();
//            printf("The master-process end.\n");
            if ($res === false) {
                return false;
            }
        }
        return true;
    }

    public function start()
    {
        // 创建后台进程
        $daemonProcess = $this->daemon();
        if ($daemonProcess != -1) {
            // 执行
            for (; ;) {
                $res = $this->run($daemonProcess);
                if (empty($res)) {
                    break;
                }
                sleep(100);
            }
        }
    }

    /**
     * $processes 为空结束所有子进程
     * @param array $processes
     */
    private function stop($processes = [])
    {
        if (empty($processes)) {
            $processes = $this->processes;
            $this->processes = [];
        }
        $c = count($processes);
        for ($i = 0; $i < $c; $i++) {
            $processes = $processes[$i];
            $pid = $processes->getPid();
            if ($pid == -1) {
                continue;
            }
            posix_kill($pid, SIGTERM);
        }
    }

}

