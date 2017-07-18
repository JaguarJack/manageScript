# manageScript
脚本管理

基于swoole的简单的 Master Worker模型

由单独的守护进程监听端口 
php Master.php 启动守护进程

由 Client 端发送请求操作命令 start|stop|status
php Client.php start script_name

约定：
script目录为脚本开发目录

目的：
  使没有服务器权限的开发人员也可以启动脚本

后期改进：
  利用websocket直接在后台进行管理
