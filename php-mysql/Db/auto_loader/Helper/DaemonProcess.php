<?php


class Helper_DaemonProcess
{
    private $pid;
    private $uid;
    private $gid;
    private $status;
    private $master;
    private $deamon;
    private $worker = null;
    private $num = 0;

    private $run;

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
     * @return Helper_Daemon
     */
    public function getDeamon()
    {
        return $this->deamon;
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

    public function __construct(Helper_Daemon $deamon, $pid,  $uid, $gid)
    {
        $this->deamon = $deamon;
        $this->setPid($pid);
        $this->uid = $uid;
        $this->gid = $gid;
    }

    public function statusProcess()
    {
//        var_dump(date('Y-m-d H:i:s'). "开始回收子进程----{$this->pid}");

        Log_Info::write(
            Log_Info::WX_USER_SUBSCRIBE_DAEMON,
            date('Y-m-d H:i:s'). "开始回收子进程----{$this->pid}"
        );
        $this->run = pcntl_waitpid($this->pid, $this->status, WNOHANG | WUNTRACED);
//        var_dump(date('Y-m-d H:i:s')."结束回收子进程----{$this->pid}：回收结果: ", $this->run);
        Log_Info::write(
            Log_Info::WX_USER_SUBSCRIBE_DAEMON,
            date('Y-m-d H:i:s'). "结束回收子进程----{$this->pid}：回收结果: {$this->run}"
        );
        return $this->run;
    }

    public static function create($deamon, $uid = 501, $gid = 501)
    {
        $pid = pcntl_fork();
        return new Helper_DaemonProcess($deamon, $pid, $uid, $gid);
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

