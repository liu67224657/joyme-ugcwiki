<?php
$wgDBname = $wgWikiname."wiki";
$wgDBservers = array(
	array(
		'host' => "10.171.101.30",
		'dbname' => $wgWikiname . "wiki",
		'user' => "wikiuser",
		'password' => "I87DCXp6",
		'type' => "mysql",
		'flags' => DBO_DEFAULT,
		'load' => 0,
	),
);

#object cache setting

$wgObjectCaches['redis'] = array(
'class' => 'RedisBagOStuff',
'servers' => array( 'r-2ze25cf88632c7b4.redis.rds.aliyuncs.com:6379' ),
'persistent' => true,
'password'=>'FHW2n2Gh'
);
$wgMainCacheType = 'redis';

$joyme_u_key = 'as__-d(*^(';

//配置加载PHP公共库的具体路径
$GLOBALS['libPath'] = '/opt/www/joymephplib/beta/phplib.php';
if(!file_exists($GLOBALS['libPath'])){
	die('公共库加载失败，未找到入口文件');
}
include($GLOBALS['libPath']);
use Joyme\core\Log;
Log::config(Log::DEBUG);


//七牛云存储配置
$wgQiNiuPath = 'joymepic.joyme.com';
$wgQiNiuBucket = 'joymepic';


//squid缓存
$wgUseSquid = true;
$wgSquidServers = array(
	'10.51.114.101'
);

$wgForcedRawSMaxage = 300;

$wgJobRunRate = 1;

//私信服务
$wgUserBoardWebSocketHost = '10.51.114.138';
$wgUserBoardWebSocketPort = '9501';
$wgUserBoardWebSocketUrl = 'ws://60.205.108.213:'.$wgUserBoardWebSocketPort;
$wgUserBoardWebSocketConfig = array(
	'max_conn' => 1024,
	'reactor_num' => 4, //reactor thread num
	'worker_num' => 8,    //worker process num
	'max_request' => 0,
	'daemonize'=>1,
	'dispatch_mode' => 1
);
