<?php


class Daemon_DaemonProcess
{
    private $pid;
    private $uid;
    private $gid;
    private $status;
    private $master;
    private $daemon;
    private $worker = null;
    private $num = 0;

    /**
     * @return null
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param null $worker
     */
    public function setWorker($worker)
    {
        $this->worker = $worker;
    }

    /**
     * @return Daemon_Daemon
     */
    public function getDaemon()
    {
        return $this->daemon;
    }


    /**
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * @param int $num
     */
    public function setNum($num)
    {
        $this->num = $num;
    }

    /**
     * @return mixed
     */
    public function isMaster()
    {
        return $this->master;
    }

    /**
     * @return null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $master
     */
    public function setMaster($master)
    {
        $this->master = $master;
    }

    public function __construct(Daemon_Daemon $daemon, $pid, $uid, $gid)
    {
        $this->daemon = $daemon;
        $this->setPid($pid);
        $this->uid = $uid;
        $this->gid = $gid;
    }

    public function statusProcess()
    {
        Daemon_Logger::info(
            date('Y-m-d H:i:s') . "开始回收子进程----{$this->pid}"
        );
        $run = pcntl_waitpid($this->pid, $this->status, WNOHANG | WUNTRACED);
        Daemon_Logger::info(
            date('Y-m-d H:i:s') . "结束回收子进程----{$this->pid}：回收结果: {$run}"
        );
        return $run;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid($pid)
    {
        if ($pid) {
            $this->master = true;
            $this->pid = $pid;
        } else {
            $this->master = false;
            $this->pid = getmypid();
        }
    }


}

