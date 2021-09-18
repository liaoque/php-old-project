<?php


spl_autoload_register(function ($class) {
    if (!class_exists($class)) {
        $paths = explode('_', $class);
        $path = implode('/', $paths);
        $root_path = dirname(dirname(__DIR__));
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $root_path);
        }
        $fileName = ROOT_PATH . '/auto_loader/' . $path . '.php';
        if (file_exists($fileName)) {
            require_once $fileName;
        }
    }
});





/*$sql = DbNew_SqlQuery::table('order_info')->find(1);
var_dump($sql->__toString());*/

//$sql = DbNew_SqlQuery::table('order_info')
//    ->where('id', function ( $query){
//        $query->where('ac', 1)->where('b', 1);
//    })->first();
//var_dump($sql->__toString());


//$sql = DbNew_SqlQuery::table('order')
//    ->where('id', 1)
//    ->where('id', 3)
//    ->orWhere('d', 'is', null)
//    ->where('id', 4)
//    ->orWhere('id', 5)
//    ->where(function ($query) {
//        $query->where('id', 7)
//            ->where('id', function ($query) {
//                $query->where('id', 10)
//                    ->orWhere('id', 11);
//            })->orWhere('id', 8);
//    })
//    ->where('uid', function ($query) {
//        $query->from('user')->where('id', 7)->findAll(['uid']);
//    })
//    ->leftJoin('user', 'user.id', '=', 'uid')
//    ->leftJoin('user2', function ($query) {
//        $query->on('user.id', '=', 'uid')
//            ->orOn('user.id', '=', 'uid');
//    })
//    ->rightJoin('user', 'user.id', '=', 'uid')
//    ->join('user', 'user.id', '=', 'uid')
//    ->having('c', 1)
//    ->groupBy('a', 'b')
//    ->orderBy('a')
//    ->orderBy('b')
//    ->get();
//var_dump($sql);


$sql = DbNew_SqlQuery::table('order')->insert([
   'a' => 1,
   'b' => 1,
]);
var_dump($sql);


$sql = DbNew_SqlQuery::table('order')->insert([
 [   'a' => 1,
     'b' => 1,],
    [   'a' => 1,
        'b' => 1,],
]);
var_dump($sql);
