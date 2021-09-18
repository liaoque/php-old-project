<?php


class Daemon_DaemonWorkerMaster extends Daemon_DaemonWorkerBase
{

    public function __construct(Daemon_DaemonProcess $daemonProcess)
    {
        parent::__construct($daemonProcess);
        $this->prefix = "父进程----" . getmypid();
    }

    /**
     * @param array $data
     * @return bool    true 持续执行， false 结束
     */
    public function run($data = [])
    {
        $daemon = $this->getDaemonProcess()->getDaemon();
        $processes = $daemon->getProcesses();
        $isDefend = $daemon->isDefend();
        if (empty($isDefend) && empty($processes)) {
            // 不是守护进程 && 子进程已经全部结束
            // 结束master进程
            Daemon_Logger::info("子进程全部结束, 关闭自己~~");
            return false;
        }
        sleep(5);
        Daemon_Logger::info("父进程等待5秒钟~~");
        return true;
    }
}

