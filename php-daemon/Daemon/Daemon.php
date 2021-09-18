<?php


class Daemon_Daemon
{
    private $uid = 501;
    private $gid = 501;

    private $win;

    // 开启进程数
    private $num = 5;

    // 执行任务类
    private $classname;

    // 是否作为守护进程
    private $isDefend;

    // 执行进程池
    private $processes = [];

    // 子进程池
    private $poolProcesses = [];

    /**
     * 当前进程数量
     * @var int
     */
    private $curProcessCount;

    /**
     * @return bool
     */
    public function isDefend()
    {
        return $this->isDefend;
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
     * @param $className  执行的任務名字
     * @param int $num 开启子进程数量
     * @param bool $isDefend 是否后台守护进程 false：子进程退出后不会重启
     * @param int $uid 脚本的所属用户
     * @param int $gid 所属组
     * @param bool $background 后台运行
     */
    public function __construct($className, $num = 1, $isDefend = false, $uid = 0, $gid = 0, $background = false)
    {
        // 检查是否是 win系统
        $this->win = Helper_System::checkWin();
        if ($this->win) {
            die('操作系统必须是linux');
        }
        $this->uid = $uid;
        $this->isDefend = $isDefend;
        $this->gid = $gid;
        $this->num = $num;
        $this->curProcessCount = 0;
        $this->classname = $className;
        if ($background) {
            $this->withDaemon();
            Daemon_Logger::setBackground(true);
        }
        $this->signal();
        Daemon_Logger::info("开始执行~~");
    }

    public function withDaemon()
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            Daemon_Logger::info("启动失败~~");
        } else if ($pid) {
            exit(0);
        } else {
            posix_setuid($this->uid);
            posix_setuid($this->gid);
        }
    }

    /**
     * 设置信号捕获
     */
    public function signal()
    {
        pcntl_signal(SIGCHLD, function ($signor) /*use ()*/ {

            Daemon_Logger::info("收到子进程退出信号~~ [$signor]【{pid}】", ['pid' => getmypid()]);
            $processes = $this->processes;
            $this->processes = array_values(array_filter($this->processes, function ($process) {
                if ($process->statusProcess()) {
                    return false;
                }
                return true;
            }));
            Daemon_Logger::info("过滤后进程状态");

            if ($this->isDefend) {
                // 把退出的進程加入進程池
                // 当前剩余进程数量
                $this->curProcessCount = count($this->processes);
                $this->poolProcesses = array_merge($this->poolProcesses, array_udiff($processes, $this->processes, function ($process1, $process2) {
                    if ($process1->getPid() < $process2->getPid()) {
                        return -1;
                    } elseif ($process1->getPid() > $process2->getPid()) {
                        return 1;
                    }
                    return 0;
                }));
                Daemon_Logger::info("过滤后进程状态222");
            }
            gc_collect_cycles();
        });

        pcntl_signal(SIGTERM, function ($signor) /*use ()*/ {
            Daemon_Logger::info("收到退出信号~~ [$signor]【{pid}】", ['pid' => getmypid()]);
            self::stop();
            exit(0);
        });
    }

    /**
     * @return Daemon_DaemonProcess
     */
    public function popPoolProcesses()
    {
        return array_pop($this->poolProcesses);
    }

    /**
     * 根据进程池创建进程
     * @return Daemon_DaemonProcess|int|void
     *          0 表示 主进程返回
     *          Daemon_DaemonProcess 子进程返回
     * @throws Exception
     */
    private function daemon()
    {
        if ($this->poolProcesses === null || $this->curProcessCount >= $this->getNum()) {
            // 进程池被未被释放， 表示当前是master进程
            // 当前进程数量 < 创建数量
            return 0;
        }

        $daemonProcess = $this->popPoolProcesses();
        if (empty($daemonProcess)) {
            Daemon_Logger::warning("进程池不足~~,curProcessCount:{curProcessCount}-{num}", [
                'curProcessCount' => $this->curProcessCount,
                'num' => $this->num,
            ]);
            throw new Exception("进程池不足~~");
        }

        // 创建子进程
        $daemonProcess->setPid(pcntl_fork());
        $master = $daemonProcess->isMaster();
        $pid = $daemonProcess->getPid();
        if ($master) {

            // 父进程
            if ($pid == -1) {
                // 子进程创建失败
                self::stop($this->processes);
                throw new Exception("进程创建失败，结束所有进程,进程退出~~");
            }

            // 创建成功
            // 加入执行进程池
            pcntl_wait($status, WNOHANG);
            Daemon_Logger::info("父进程创建子进程成功~~");
            $this->processes[] = $daemonProcess;
            $this->curProcessCount++;
            return 0;
        }

        Daemon_Logger::info("子进程创建成功~~");
        // 子进程
        // 释放子进程的进程池
        unset($this->poolProcesses, $this->processes);
        $this->processes = $this->poolProcesses = null;
        return $daemonProcess;
    }

    /**
     * 子进程
     * @param $daemonProcess
     * @return bool
     */
    private function runChild($daemonProcess)
    {
        while (true) {

            pcntl_signal_dispatch();
            if (!$worker = $daemonProcess->getWorker()) {
                $worker = new Daemon_DaemonWorker($daemonProcess);
                $daemonProcess->setWorker($worker);
            }

            $res = $worker->go();
            if ($res === false) {
                break;
            }
        }

        // 不存在后台守护进程
        // 直接返回
        return $this->isDefend ? true : false;
    }


    private function run($daemonProcess)
    {
        static $masterDaemonProcess = null;
        if ($daemonProcess instanceof Daemon_DaemonProcess) {

            // 子进程
            Daemon_Logger::info("子进程开始执行");
            return $this->runChild($daemonProcess);
        }

        // 主进程
        Daemon_Logger::info("主进程开始执行");
        pcntl_signal_dispatch();
        if (empty($masterDaemonProcess)) {
            $masterDaemonProcess = new Daemon_DaemonProcess($this);
        }
        if (!$worker = $masterDaemonProcess->getWorker()) {
            $worker = new Daemon_DaemonWorkerMaster($masterDaemonProcess);
            $masterDaemonProcess->setWorker($worker);
        }

        $res = $worker->go();
        // 子进程全部退出，主进程自动结束
        return $res === false ? false : true;
    }

    public function start()
    {
        for ($i = 0; $i < $this->num; $i++) {
            // 初始化 对象
            $daemonProcess = new Daemon_DaemonProcess($this, 0);
            $daemonProcess->setNum($i);
            $this->poolProcesses[] = $daemonProcess;
        }

        // 执行
        $daemonProcess = 0;
        for (; ;) {
            try {
                // 创建子进程
                $daemonProcess = $this->daemon();
            } catch (Exception $e) {
                // 进程池不够，重置当前进程数量
                // 防止资源不够，不断重试
                $this->curProcessCount == $this->getNum();
            }

            $res = $this->run($daemonProcess);
            if (empty($res)) {
                // 彻底跳出， 程序自然结束
                break;
            }

            sleep(5);
        }
    }

    /**
     * $processes 为空结束所有子进程
     * @param array $processes
     */
    private function stop($processes = [])
    {
        if (empty($processes)) {
            if ($this->processes === null) {
                Daemon_Logger::warning("子进程退出：stop!!!!");
                return;
            }
            $processes = $this->processes;
            $this->processes = [];
            Daemon_Logger::warning("SIGTERM信号 进程全部结束：stop~~~");
        }

        $c = count($processes);
        for ($i = 0; $i < $c; $i++) {
            $process = $processes[$i];
            $pid = $process->getPid();
            if ($pid == -1) {
                continue;
            }
            Daemon_Logger::warning("主进程向子进程发送结束信号：{pid}!!!!", ['pid' => $pid]);
            posix_kill($pid, SIGTERM);
        }
    }

}

