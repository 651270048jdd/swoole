<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/7 0007
 * Time: 上午 09:34
 */
$table = new swoole_table(1024);
$table->column('fd', swoole_table::TYPE_INT, 4);       //1,2,4,8
$table->column('name', swoole_table::TYPE_STRING, 64);
$table->create();

$ws = new swoole_websocket_server("192.168.0.128", 9502);
$ws->table = $table;

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    $ws->table->set($request->fd, array('fd' => $request->fd,'name'=>$request->get['name']));//获取客户端id插入table
    foreach ($ws->table as $v) {
        $ws->push($v['fd'],responseJson('1','上线',['name'=>$request->get['name']]));//发送信息
        save_log('.',responseJson('1','上线',['name'=>$request->get['name']]));//记录卡日志
    }
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    //echo $frame->fd.":{$frame->data}";
    foreach ($ws->table as $v) {
        if($v['fd']==$frame->fd){//自己发消息
            $ws->push($v['fd'], responseJson('3','send',['name'=>'我','data'=>"{$frame->data}"]));
        }else{
            ////消息广播给所有客户端
            $ws->push($v['fd'], responseJson('3','send',['name'=>$ws->table->get($frame->fd,'name'),'data'=>"{$frame->data}"]));
            save_log('.',responseJson('3','send',['name'=>$ws->table->get($frame->fd,'name'),'data'=>"{$frame->data}"]));
        }
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    //下线通知
    foreach ($ws->table as $v) {
        if( $v['fd'] <>$fd){
            $ws->push($v['fd'],responseJson('2','下线',['name'=>$v['name']]));
        }
    }
    $ws->table->del($fd);//从table中删除断开的id
});
$ws->start();


/**
 * @desc
 * @author jdd
 * @param string $code 1上线 $code 2下线 $code 3发消息
 * @param null $message
 * @param array $result
 * @param array $options
 */
function responseJson($code = '1', $message = null, $result = array(), $options = array()) {
    $result = array('code' => $code, 'msg' => $message, 'result' => $result);
    if (is_array($options)) {
        $result = array_merge($options, $result);
    }
    return json_encode($result);
}

//记录日志
function save_log($path, $msg)
{
    if (! is_dir($path)) {
        mkdir($path);
    }
    $filename = $path . '/log' . date('Ymd') . '.txt';
    $content = date("Y-m-d H:i:s")." ".$msg."\r\n";
    file_put_contents($filename, $content, FILE_APPEND);
}