<?php


spl_autoload_register(function ($class) {
    if (!class_exists($class)) {
        $paths = explode('_', $class);
        $path = implode('/', $paths);
        $root_path = dirname(__DIR__);
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $root_path);
        }
        // 这里改成自己的项目路径
        $fileName = ROOT_PATH . '/includes_new/auto_loader/' . $path . '.php';
        if (file_exists($fileName)) {
            require_once $fileName;
        }
    }
});


class TestNewDaemon implements Daemon_DaemonWorkerInterface
{
    public function run(Daemon_DaemonProcess $daemonWorker)
    {

        //  这两个参数可以做 分发
        // 如果量大， 可设置多个子进程跑
//        $num = $daemonWorker->getNum();
//        $rang = $daemonWorker->getDaemon()->getNum();
        

        var_dump(getmypid() . "运行2秒钟开始：" . $daemonWorker->getNum());
        ob_flush();
        flush();
        sleep(2);
        var_dump(getmypid() . "运行2秒钟结束：" . $daemonWorker->getDaemon()->getNum());
        ob_flush();
        flush();

    }
}




$helperDaemon = new Daemon_Daemon(TestNewDaemon::class, 3, true);
$helperDaemon->start();
