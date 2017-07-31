# manageScript
脚本管理

基于swoole的简单的 Master Worker模型

由单独的守护进程监听端口 
php Master.php 启动守护进程

由 Client 端发送请求操作命令 start|stop|status
php Client.php start script_name

利用守护进程进行管理好处，可实时监控脚本的运行状况，对脚本进程可控制。

如果不想进行脚本进行管理，单独运行脚本,这样对于脚本没有一定掌控能力
php index.php script_name

约定：
script目录为脚本开发目录,每个脚本必须继承Base,必须实现exec方法
class script extends Base
{
    public function exec(){}
}

目的：
  没有服务器权限的开发人员 也具备脚本运行权限

以client.php为原型，可开发后台管理，便于开发人员在后台直接管理脚本。
