tp5-web-msg-sender tp5示例
==============

下载安装
======
git clone https://github.com/smallcw/tp5-web-msg-sender

另附下载地址：
![tp5-web-msg-sender](http://www.daliycode.com/codes-15-90.html)


效果演示
======
![tp5-web-msg-sender-demo](http://www.daliycode.com/uploads/ueditor/image/20181225/1545719291714879.gif)


后端服务启动停止
======
## Linux系统
cd tp5-web-msg-sender
### 启动服务
php start.php start -d
### 停止服务
php start.php stop
### 服务状态
php start.php status

## windows系统
可以直接使用上面命令直接运行
或者
双击start_for_win.bat

如果启动不成功请参考 [Workerman手册](http://doc3.workerman.net/install/requirement.html) 配置环境


前端代码类似：
====
```javascript
// 引入前端文件
<script src='//cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
<script>
// 初始化io对象
var socket = io('http://'+document.domain+':2120');
// uid 可以为网站用户的uid，作为例子这里用session_id代替
var uid = '<?php echo session_id();?>';
// 当socket连接后发送登录请求
socket.on('connect', function(){socket.emit('login', uid);});
// 当服务端推送来消息时触发，这里简单的aler出来，用户可做成自己的展示效果
socket.on('new_msg', function(msg){alert(msg);});
</script>
```


后端调用api向任意用户推送数据
====
推送地址：/index.php/index/index/push
推送参数：
to_uid 推送用户(为空向所有用户推送)
data 推送数据(可为字符串或数组)

常见问题：
====
如果通信不成功检查防火墙
/sbin/iptables -I INPUT -p tcp --dport 2120 -j ACCEPT
/sbin/iptables -I INPUT -p tcp --dport 2121 -j ACCEPT
/sbin/iptables -I INPUT -p tcp --dport 2123 -j ACCEPT


workerman相关参见 [www.workerman.net](http://www.workerman.net/)
=================


author: daliycode.com
