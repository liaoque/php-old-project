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

Model_ActivityShares::getInstance()->getAll([
    'activity_id' => 1,
    'from_user_id' => 2,
], 'user_id');
