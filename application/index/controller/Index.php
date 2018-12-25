<?php
// +----------------------------------------------------------------------
// | tp5-web-msg-sender
// +----------------------------------------------------------------------
// | Copyright (c) 2018 DaliyCode All rights reserved.
// +----------------------------------------------------------------------
// | Author: DaliyCode <3471677985@qq.com> <author_url:dalicode.com>
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Controller;

class Index extends Controller {
    public function index() {
        return $this->fetch();
    }

    public function push($to_uid = '', $data = '这个是后台推送的默认测试数据', $type = 'publish') {
        // 指明给谁推送，为空表示向所有在线用户推送
        // 推送的url地址，上线时改成自己的服务器地址（端口）
        $push_api_url = $this->request->domain() . ":2121/";
        $post_data    = array(
            'type'    => $type,
            'content' => $data,
            'to'      => (string) $to_uid,
        );
        $r = $this->httpcurl($push_api_url, $post_data);
        if ($r === 'ok') {
            $this->success('推送成功');
        } elseif ($r === 'offline') {
            $this->error('用户已离线');
        }
        $this->error($r);
    }

    private function httpcurl($url = '', $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($ch);
        curl_close($ch);
        //var_export($return);
        return $return;
    }
}
