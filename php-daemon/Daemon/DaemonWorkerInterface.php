<?php


interface Daemon_DaemonWorkerInterface
{
    /**
     * @param Daemon_DaemonProcess $daemonWorker
     * @return mixed
     *          false 结束当前子进程
     *              如果设置守护进程 == true， 则会重新创建一个新的子进程
     */
    public function run(Daemon_DaemonProcess $daemonWorker);
}

