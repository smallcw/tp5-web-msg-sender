<?php
// +----------------------------------------------------------------------
// | tp5-web-msg-sender
// +----------------------------------------------------------------------
// | Copyright (c) 2018 DaliyCode All rights reserved.
// +----------------------------------------------------------------------
// | Author: DaliyCode <3471677985@qq.com> <author_url:dalicode.com>
// +----------------------------------------------------------------------
namespace app\push\controller;

use PHPSocketIO\SocketIO;
use think\Db;
use think\Request;
use Workerman\Lib\Timer;
use Workerman\Worker;

class Server {
    private $sender_io;
    private $uidConnectionMap       = array();
    private $last_online_count      = 0;
    private $last_online_page_count = 0;
    private static $db;

    public function __construct() {
        // PHPSocketIO服务
        $this->sender_io = new SocketIO(2120);
        $this->run();
    }

    public function run() {
        // 客户端发起连接事件时，设置连接socket的各种事件回调
        $this->sender_io->on('connection', function ($socket) {
            // 当客户端发来登录事件时触发
            $socket->on('login', function ($uid) use ($socket) {
                // 已经登录过了
                if (isset($socket->uid)) {
                    return;
                }
                // 更新对应uid的在线数据
                $uid = (string) $uid;
                echo 'uid=' . $uid . "\n";
                if (!isset($this->uidConnectionMap[$uid])) {
                    $this->uidConnectionMap[$uid] = 0;
                }
                // 这个uid有++$this->uidConnectionMap[$uid]个socket连接
                ++$this->uidConnectionMap[$uid];
                // 将这个连接加入到uid分组，方便针对uid推送数据
                $socket->join($uid);
                $socket->uid = $uid;
                // 更新这个socket对应页面的在线数据
                $socket->emit('update_online_count', "当前<b>{$this->last_online_count}</b>人在线，共打开<b>{$this->last_online_page_count}</b>个页面");
                $this->sender_io->emit('user_data', $this->uidConnectionMap);
            });

            // 当客户端断开连接时触发（一般是关闭网页或者跳转刷新导致）
            $socket->on('disconnect', function () use ($socket) {
                if (!isset($socket->uid)) {
                    return;
                }
                //global $this->uidConnectionMap;
                // 将uid的在线socket数减一
                if (--$this->uidConnectionMap[$socket->uid] <= 0) {
                    unset($this->uidConnectionMap[$socket->uid]);
                }
            });
        });

        // 当$this->sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
        $this->sender_io->on('workerStart', function () {
            // 监听一个http端口
            $inner_http_worker = new Worker('http://0.0.0.0:2121');
            // 当http客户端发来数据时触发
            $inner_http_worker->onMessage = function ($http_connection, $data) {
                $_POST = $_POST ? $_POST : $_GET;
                // 推送数据的url格式 type=publish&to=uid&content=xxxx
                switch (@$_POST['type']) {
                case 'publish':
                    $to               = @$_POST['to'];
                    $_POST['content'] = htmlspecialchars(@$_POST['content']);
                    // 有指定uid则向uid所在socket组发送数据
                    if ($to) {
                        $this->sender_io->to($to)->emit('new_msg', $_POST['content']);
                        // 否则向所有uid推送数据
                    } else {
                        $this->sender_io->emit('new_msg', @$_POST['content']);
                    }
                    // http接口返回，如果用户离线socket返回fail
                    if ($to && !isset($this->uidConnectionMap[$to])) {
                        return $http_connection->send('offline');
                    } else {
                        return $http_connection->send('ok');
                    }
                }
                return $http_connection->send('fail');
            };
            // 执行监听
            $inner_http_worker->listen();

            // 一个定时器，定时向所有uid推送当前uid在线数及在线页面数
            Timer::add(1, function () {
                $online_count_now      = count($this->uidConnectionMap);
                $online_page_count_now = array_sum($this->uidConnectionMap);
                // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
                if ($this->last_online_count != $online_count_now || $this->last_online_page_count != $online_page_count_now) {
                    $this->sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
                    $this->sender_io->emit('user_data', $this->uidConnectionMap);
                    $this->last_online_count      = $online_count_now;
                    $this->last_online_page_count = $online_page_count_now;
                }
            });
        });

        Worker::runAll();
    }
}
