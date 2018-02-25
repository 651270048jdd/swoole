<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19 0019
 * Time: 下午 04:02
 */

swoole_timer_tick(2000, function ($timer_id) {
    $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

//注册连接成功回调
    $client->on("connect", function($cli) {
        $cli->send("hello world\n");
    });

//注册数据接收回调
    $client->on("receive", function($cli, $data){
        echo "Received: ".$data."\n";
    });

//注册连接失败回调
    $client->on("error", function($cli){
        echo "Connect failed\n";
    });

//注册连接关闭回调
    $client->on("close", function($cli){
        echo "Connection close\n";
    });

    $client->connect('192.168.0.128', 9501, 0.5);
});