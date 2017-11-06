<?php

## Database settings
/*
  $wgDBtype = "mysql";
  $wgDBserver = "127.0.0.1";
  $wgDBuser = "root";
  $wgDBpassword = "123456";
 */
$wgDBname = $wgWikiname . "wiki";
$wgDBservers = array(
    array(
        'host' => "172.16.75.143",
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
    'servers' => array('172.16.75.132:6380'),
    'persistent' => true,
);
$wgMainCacheType = 'redis';

$joyme_u_key = 'as__-d(*^(';

$joyme_u_adminid = array(1064942);
$wgDBerrorLog = '/opt/servicelogs/php/db.err.log';
//$wgDebugLogGroups['phpwiki'] = '/opt/servicelogs/phplog/wiki_debug.log';
$wgDebugLogFile = '/opt/servicelogs/php/wiki_debug.log';
//配置加载PHP公共库的具体路径
$GLOBALS['libPath'] = '/opt/www/joymephplib/alpha/phplib.php';
//$GLOBALS['libPath'] = 'D:\wamp\www\workspace\joymephplib\trunk\phplib.php';

if (!file_exists($GLOBALS['libPath'])) {
    die('公共库加载失败，未找到入口文件');
}
include($GLOBALS['libPath']);

use Joyme\core\Log;

Log::config(Log::DEBUG);

$wgShowSQLErrors = true;

//七牛云存储配置
$wgQiNiuPath = 'joymepic.joyme.com';
$wgQiNiuBucket = 'joymepic';


//squid缓存

$wgUseSquid = true;
$wgSquidServers = array('172.16.75.111');

$wgSquidMaxage = 300;
$wgForcedRawSMaxage = 300;


$wgJobRunRate = 1;

//私信服务
$wgUserBoardWebSocketHost = '0.0.0.0';
$wgUserBoardWebSocketPort = '9501';
$wgUserBoardWebSocketUrl = 'ws://172.16.75.121:'.$wgUserBoardWebSocketPort;
$wgUserBoardWebSocketConfig = array(
    'max_conn' => 1024,
    'reactor_num' => 4, //reactor thread num
    'worker_num' => 8,    //worker process num
    'max_request' => 0,
    'daemonize'=>1,
    'dispatch_mode' => 1,
	'log_file' =>'/opt/servicelogs/php/wiki_userboard_websocket.log'
);

$wgGroupPermissions['autoconfirmed']['upload_by_url'] = true;
$wgAllowCopyUploads = true;
$wgCopyUploadsFromSpecialUpload = true;

$joyme_u_adminid = array(1064942);

//wfLoadExtension( 'MobileFrontend' );
//$wgMFAutodetectMobileView = true;