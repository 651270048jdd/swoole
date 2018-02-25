<?php
/**
 * redis 版本
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19 0019
 * Time: 下午 03:34
 */

//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("192.168.0.128", 9501);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    $array=['fd'=>$request->fd,'name'=>$request->get['name']];
    print_r(getRedis());return ;
   // $GLOBALS['name'][$array['fd']]['name'] = $array['name'];
    if(is_array($GLOBALS['name'])) foreach($GLOBALS['name'] as $k=>$v){
      //  $ws->push($k,responseJson('1','上线',['name'=>$array['name']]));//发送信息
       // save_log('.',responseJson('1','上线',['name'=>$array['name']]));//记录卡日志
    }
});
//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    //所有用户发信息
    if(is_array($GLOBALS['name'])) foreach($GLOBALS['name'] as $k=>&$v){
        if($k==$frame->fd){//自己发消息
            //responseJson('3','下线',$GLOBALS['name'][$fd]['name']){$frame->data}
            //$ws->push($k, responseJson('3','send',"{$frame->data}"));
            $ws->push($k, responseJson('3','send',['name'=>'我','data'=>"{$frame->data}"]));
            //save_log('.',responseJson('3','send',['name'=>$GLOBALS['name'][$frame->fd]['name'],'data'=>"{$frame->data}"]));
        }else{
            $ws->push($k, responseJson('3','send',['name'=>$GLOBALS['name'][$frame->fd]['name'],'data'=>"{$frame->data}"]));
            save_log('.',responseJson('3','send',['name'=>$GLOBALS['name'][$frame->fd]['name'],'data'=>"{$frame->data}"]));
        }
    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    //下线通知
    if(is_array($GLOBALS['name'])) foreach($GLOBALS['name'] as $k=>&$v){
        if( $k <>$fd){
            $ws->push($k,responseJson('2','下线',['name'=>$GLOBALS['name'][$fd]['name']]));
        }
    }
    unset($GLOBALS['name'][$fd]);
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


/**
 * @desc getRedis
 * @author jdd
 */
function getRedis(){
    require_once 'RedisClient.php';
    $redis = new \swoole\RedisClient('192.168.0.200');

    $redis->select('8', function () use ($redis) {
        $redis->set('key', 'value-rango', function ($result, $success) use ($redis) {
             $redis->onFinish($result);
        });
    });
}
