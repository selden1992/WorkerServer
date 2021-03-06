<?php
/**
 * 服务控制器
 * User: selden
 * Date: 2017/1/13
 * Time: 18:04
 */

namespace app\websocket;


use app\websocket\container\Links;
use app\websocket\container\Users;
use app\websocket\tool\Route;
use app\websocket\tool\Send;
use system\WorkerMan;

class Server extends WorkerMan
{
    /**
     * 启动时的回调函数
     * @param $worker Worker对象
     */
    public function onWorkerStart($worker)
    {
        Route::main();
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection 连接对象
     */
    public function onConnect($connection)
    {
        $connection->linkId = uniqid('link_id');
        Links::push($connection->linkId,$connection);
    }

    /**
     * 当有客户端的连接上有数据发来时触发
     * @param $connection 连接对象
     * @param $data 客户端连接上发来的数据，如果Worker指定了协议，则$data是对应协议decode（解码）了的数据
     */
    public function onMessage($connection, $data)
    {
        $data = json_decode($data, true);
        if( empty($data) && is_array($data) ){
            Route::run('help',[],$connection);
        }elseif( !Users::isLogin($connection) ){
            if( Users::login($connection, $data['data'])==false ){
                $connection->send( Send::error('Login failed',4001) );
            }else{
                $connection->send( Send::success("Login success") );
            }
        }else{
            if( $data['url'] ){
                Route::run($data['url'],$data['data'],$connection);
            }else{
                $connection->send( Send::error('Missing URL parameter') );
            }
        }
    }


    /**
     * 当连接断开时触发的回调函数。不管连接是如何断开的，只要断开就会触发onClose。每个连接只会触发一次onClose
     * @param $connection 连接对象
     */
    public function onClose($connection)
    {
        Links::off($connection->linkId);
    }


    /**
     * 当客户端的连接上发生错误时触发。
     * @param $connection 连接对象，连接对象的说明见下一节
     * @param $code 错误码
     * @param $msg  错误消息
     */
    public function onError($connection, $code, $msg)
    {

    }


    /**
     * 该回调可能会在调用Connection::send后立刻被触发，比如发送大数据或者连续快速的向对端发送数据，由于网络等原因数据被大量积压在对应连接的发送缓冲区，当超过TcpConnection::$maxSendBufferSize上限时触发
     * @param $connection 连接对象
     */
    public function onBufferFull($connection)
    {

    }


    /**
     * 该回调在应用层发送缓冲区数据全部发送完毕后触发。一般与onBufferFull配合使用，例如在onBufferFull时停止向对端继续send数据，在onBufferDrain恢复写入数据。
     * @param $connection 连接对象
     */
    public function onBufferDrain($connection)
    {

    }

    /**
     * 停止时的回调函数，即当Worker收到stop信号后执行Worker::onWorkerStop指定的回调函数
     * @param $worker Worker对象
     */
    public function onWorkerStop($worker)
    {

    }

    /**
     * Worker收到reload信号后执行的回调
     * @param $worker Worker对象
     */
    public function onWorkerReload($worker)
    {

    }
}