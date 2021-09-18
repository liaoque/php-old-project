<?php


class Daemon_DaemonWorker extends Daemon_DaemonWorkerBase
{
    public function __construct(Daemon_DaemonProcess $daemonProcess)
    {
        parent::__construct($daemonProcess);
        $this->prefix = "子进程----".getmypid();
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function run($data = [])
    {
        $worker = $this->getWorker();
        if (!($worker instanceof Daemon_DaemonWorkerInterface)) {
            throw new Exception("实际业务类必须继承接口：Daemon_DaemonWorkerInterface");
        }
        $res = $worker->run($this->getDaemonProcess());
        if ($res !== null) {
            return $res;
        }
    }
}

