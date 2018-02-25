<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19 0019
 * Time: 下午 04:28
 */
$db = new Swoole\MySQL;
$server = array(
    'host' => '192.168.0.200',
    'user' => 'root',
    'password' => '',
    'database' => 'imptrip',
);

$db->connect($server, function ($db, $result) {
    $db->query("show tables", function (Swoole\MySQL $db, $result) {
        if ($result === false) {
            var_dump($db->error, $db->errno);
        } elseif ($result === true) {
            var_dump($db->affected_rows, $db->insert_id);
        } else {
            var_dump($result);
            $db->close();
        }
    });
});