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
        'host' => "172.16.75.32",
        'dbname' => $wgWikiname . "wiki",
        'user' => "root",
        'password' => "123456",
        'type' => "mysql",
        'flags' => DBO_DEFAULT,
        'load' => 0,
    ),
);

#object cache setting

$wgObjectCaches['redis'] = array(
    'class' => 'RedisBagOStuff',
    'servers' => array('172.16.75.32:6379'),
    'persistent' => true,
);
$wgMainCacheType = 'redis';

$wgRedis_host = '172.16.75.32';
$wgRedis_port = 6379;

$joyme_u_key = 'as__-d(*^(';

$joyme_u_adminid = array(1064942);
$wgDBerrorLog = '/var/log/mediawiki/db.err.log';
$wgDebugLogGroups['phpwiki'] = '/opt/servicelogs/phplog/wiki_debug.log';
//$wgDebugLogGroups['phpwiki'] = 'D:\log\wiki\wiki_debug.log';
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
$wgQiNiuPath = 'joymetest.qiniudn.com';
$wgQiNiuBucket = 'joymetest';


