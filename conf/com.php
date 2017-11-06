<?php

$wgDBname = $wgWikiname . "wiki";
$wgDBservers = array(
	array(
		'host' => "rm-2zem35fsj37eqzu3p.mysql.rds.aliyuncs.com",
		'dbname' => $wgWikiname . "wiki",
		'user' => "wikicr",
		'password' => "bscIM5yK",
		'type' => "mysql",
		'flags' => DBO_DEFAULT,
		'load' => 0,
	),
);

#object cache setting
$wgObjectCaches['redis'] = array(
    'class' => 'RedisBagOStuff',
    'servers' => array( 'r-2zef16817404a374.redis.rds.aliyuncs.com:6379' ),
    'persistent' => true,
    'password'=>'zIGMyY12'
);
$wgMainCacheType = 'redis';


$joyme_u_key = 'as__-d(*^(';


//七牛云存储配置
$wgQiNiuPath = 'joymepic.joyme.com';
$wgQiNiuBucket = 'joymepic';

//配置加载PHP公共库的具体路径
$GLOBALS['libPath'] = '/opt/www/joymephplib/prod/phplib.php';
if (!file_exists($GLOBALS['libPath'])) {
    die('公共库加载失败，未找到入口文件');
}
include($GLOBALS['libPath']);

use Joyme\core\Log;

Log::config(Log::ERROR);


//squid缓存
$wgUseSquid = true;
$wgSquidServers = array(
	'10.172.82.206',
	'10.51.113.180',
);

$wgForcedRawSMaxage = 300;

$wgJobRunRate = 0;

//私信服务
$wgUserBoardWebSocketHost = '0.0.0.0';
$wgUserBoardWebSocketPort = '9501';
$wgUserBoardWebSocketUrl = 'ws://60.205.90.33:'.$wgUserBoardWebSocketPort;
$wgUserBoardWebSocketConfig = array(
	'max_conn' => 1024,
	'reactor_num' => 4, //reactor thread num
	'worker_num' => 8,    //worker process num
	'max_request' => 0,
	'daemonize'=>1,
	'dispatch_mode' => 1
);
