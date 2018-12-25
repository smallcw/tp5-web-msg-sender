<?php

namespace think;

//加载tp框架引导文件
require __DIR__ . '/../thinkphp/base.php';

Container::get('app')->run()->send();
