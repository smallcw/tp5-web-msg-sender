<?php

namespace think;

define('APP_PATH', __DIR__ . '/application/');
//加载tp框架引导文件
require __DIR__ . '/thinkphp/base.php';

Container::get('app')->path(APP_PATH)->bind('push/server')->run()->send();
