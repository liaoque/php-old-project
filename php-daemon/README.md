# php-daemon
一个守护进程的封装, 在一些老项目中可能用得到

demo 请查看 test_daemon.php


> 定义的类一定要继承 Daemon_DaemonWorkerInterface
```php script
class TestNewDaemon implements Daemon_DaemonWorkerInterface
{
    public function run(Daemon_DaemonProcess $daemonWorker)
    {
        ....
    }
}
```

> 在多进程情况下可根据下面方法进行取模负载
>
> 当前进程下标
```shell script
$num = $daemonWorker->getNum();
``` 
> 当前进程总数量
```shell script
$daemonWorker->getDaemon()->getNum();
```

> 如果需要持久化mysql, 直接在 DaemonWorkerBase.php的init方法内填代码即可


