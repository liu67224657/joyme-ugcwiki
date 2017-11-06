#!/usr/bin/env php
<?php
/**
 * 私信功能socket服务
 *
 * @file
 * @ingroup Maintenance
 */

if(empty($argv[1])){
	echo 'no env';exit;
}
$_SERVER['HTTP_HOST'] = 'wiki.joyme.'.$argv[1];
$_SERVER['REQUEST_URI'] = '/home/';
$_SERVER['QUERY_STRING'] = '';

$IP = strval( getenv( 'MW_INSTALL_PATH' ) ) !== ''
	? getenv( 'MW_INSTALL_PATH' )
	: realpath( dirname( __FILE__ ) . "/../../../" );
// Can use __DIR__ once we drop support for MW 1.19

require "$IP/maintenance/Maintenance.php";

/**
 * 
 * @ingroup Maintenance
 */
class BoardWebSocket extends Maintenance {
	public function __construct() {
		//parent::__construct();
		$this->mDescription = 'user board websocket init';
		//$this->addOption( 'websocket', 'websocket init' );
		//$this->addArg( 'title', 'websocket' );
	}

	public function execute() {
		global $wgUserBoardWebSocketHost,$wgUserBoardWebSocketPort,$wgUserBoardWebSocketConfig;

		$b = new UserBoard();
		$b->clearAllUserClient();
		
		$server = new swoole_websocket_server($wgUserBoardWebSocketHost, $wgUserBoardWebSocketPort);

		$server->set($wgUserBoardWebSocketConfig);
		
		$server->on('open', function (swoole_websocket_server $server, $request) {
		    echo "server: handshake success with fd{$request->fd}\n";
		});
		
		$server->on('message', function (swoole_websocket_server $server, $frame) {
		    //echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
			
			// 客户端传递的是json数据
			$message = $frame->data;
			$message_data = json_decode($message, true);
			if(!$message_data)
			{
				return;
			}
			$client_id = $frame->fd;
			//发送者的uid
			$sender_uid = empty($message_data['uid'])?'':intval($message_data['uid']);
			$receiver_uid = empty($message_data['to_uid'])?'':intval($message_data['to_uid']);
			if(!$sender_uid)
			{
				return;
			}
		
			// 根据类型执行不同的业务
			switch($message_data['type'])
			{
				// 客户端回应服务端的心跳
				case 'pong':
					return;
				// 客户端上线
				case 'login':
					$b = new UserBoard();
					$b->setBoardClientidByUid($sender_uid,$client_id);
					
					if($receiver_uid){
						$msg = $b->getUserBoardUnReadMessages($sender_uid, $receiver_uid);
						$msg = empty($msg)?'':$msg;
						$new_message = array(
								'type'=>'login',
								'from_client_id'=>$receiver_uid,
								'content'=>$msg,
								'to_client_id'=>$sender_uid,
								'code'=>'1'
						);
						$server->push($client_id, json_encode($new_message));
					}
					
					echo "uid:{$sender_uid},clientid: {$client_id} connect\n";
					return;
				// 获取消息
				case 'getmsg':
					
					//$sender = User::newFromId($sender_uid);
					//$receiver = User::newFromId($receiver_uid);
					
					$ub_id = intval($message_data['id']);
					
					$b = new UserBoard();
					$msg = $b->getMessage($ub_id,$sender_uid,$receiver_uid);
					/*if($msg){
						$new_message = array(
								'type'=>'getmsg',
								'from_client_id'=>$receiver_uid,
								'from_client_name'=>$receiver->getName(),
								'to_client_id'=>$sender_uid,
								'time'=>date('Y-m-d H:i:s'),
								'id'=>$ub_id,
								'code'=>'1'
						);
					}else{
						$new_message = array(
								'type'=>'getmsg',
								'from_client_id'=>$receiver_uid,
								'from_client_name'=>$receiver->getName(),
								'to_client_id'=>$sender_uid,
								'time'=>date('Y-m-d H:i:s'),
								'id'=>$ub_id,
								'code'=>'0'
						);
					}
					*/
					//$server->push($client_id, json_encode($new_message));
					
					return;
					
				// 客户端发言 message: {type:say, to_client_id:xx, content:xx}
				case 'say':
					$b = new UserBoard();
					
					
					if(empty($receiver_uid)){
						return;
					}
					//getBoardClientidByUid
					$toclientid = $b->getBoardClientidByUid($receiver_uid);
					
					$message = empty($message_data['content'])?'':$message_data['content'];
					$sender_icon = empty($message_data['icon'])?'':$message_data['icon'];
					$uuid = empty($message_data['uuid'])?'':$message_data['uuid'];
					
					$sender = User::newFromId($sender_uid);
					$receiver = User::newFromId($receiver_uid);
					
					$receiver_stats = new UserStats( $receiver->getID(), $receiver->getName() );
					$receiver_stats_data = $receiver_stats->getUserStats();
					
					$is_secretchat = $receiver_stats_data['is_secretchat'];
					
					if($is_secretchat == 0){
						$new_message = array(
								'type'=>'say',
								'from_client_id'=>$sender_uid,
								'from_client_name'=>$sender->getName(),
								'from_client_headicon'=>'',
								'to_client_id'=>$receiver_uid,
								'content'=>'',
								'time'=>date('Y-m-d H:i:s'),
								'isfollow'=>'1',
								'id'=>'0',
								'code'=>'0'
						);
						$server->push($client_id, json_encode($new_message));
						return;
					}
					
					$uuf = new UserUserFollow();
					$sender_isfollow = $uuf->checkUserUserFollow($sender, $receiver);
					$sender_isfollow = $sender_isfollow==false?'2':'1';
					
					$receiver_isfollow = $uuf->checkUserUserFollow($receiver, $sender);
					$receiver_isfollow = $receiver_isfollow==false?'2':'1';
					
					$b = new UserBoard();
					$ub_id = $b->sendBoardMessage($sender_uid,$receiver_uid,$message,$sender_isfollow,$receiver_isfollow);
					
					global $wgOut;
					$message = urldecode($message);
					//$parser = new Parser();
					//$title = new Title();
					//$message_text = $parser->parse( $message, $title, $wgOut->parserOptions(), true );
					//$message = $message_text->getText();
					$message = nl2br(str_replace(chr(32),'&nbsp;',htmlspecialchars($message)));
					
					$new_message = array(
						'type'=>'say',
						'from_client_id'=>$sender_uid, 
						'from_client_name'=>$sender->getName(), 
						'from_client_headicon'=>$sender_icon,
						'to_client_id'=>$receiver_uid,
						'content'=>$message,
						'time'=>date('Y-m-d H:i:s'),
						'isfollow'=>$receiver_isfollow,
						'uuid'=>$uuid,
						'id'=>$ub_id,
						'code'=>'1'
					);
					
					if(!empty($toclientid)){
						$toclientidarr = explode('|', $toclientid);
						foreach ($toclientidarr as $cid){
							$rs = $server->push($cid, json_encode($new_message));
							if(!$rs){
								$b->clearBoardClientidByUid($receiver_uid,$cid);
								echo "clear clientid:{$cid} for uid:{$receiver_uid}\n";
							}
						}
					}
					
					$new_message['isfollow'] = $sender_isfollow;
					$server->push($client_id, json_encode($new_message));
					return;
		
			}
			return;
		});
		
		$server->on('close', function ($ser, $fd) {
		    echo "client {$fd} closed\n";
		});
		
		$server->start();
	}
}

$maintClass = "BoardWebSocket";
require_once RUN_MAINTENANCE_IF_MAIN;
