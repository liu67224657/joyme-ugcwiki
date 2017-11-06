#!/usr/bin/env php
<?php

if(empty($argv[1])){
  echo 'no env';exit;
}

$env = $argv[1];

$wgDBservers = array(
		'alpha'=>array(
				'host' => "172.16.75.32",
				'user' => "root",
				'password' => "123456"
		),
		'beta'=>array(
				'host' => "alyweb002.prod",
				'user' => "wikiuser",
				'password' => "123456"
		),
		'com'=>array(
				'host' => "rdsnu7brenu7bre.mysql.rds.aliyuncs.com",
				'user' => "wikiuser",
				'password' => "123456"
		),
);

if(empty($wgDBservers[$env])){
	echo 'no this env';exit;
}

$db = $wgDBservers[$env];

$mysqli = new mysqli($db['host'], $db['user'], $db['password']);

if ($mysqli->connect_errno)
{
	echo "Failed to connect to MySQL: " . $mysqli->connect_error;
	exit;
}

$sql     = "SELECT `SCHEMA_NAME` FROM `information_schema`.`SCHEMATA`";
$db_list = $mysqli->query($sql);

$sql = getsql();

while ($row = mysqli_fetch_object($db_list)) {
     if(substr($row->SCHEMA_NAME,-4) == 'wiki'){
		$db_selected = $mysqli->select_db($row->SCHEMA_NAME);
		if (!$db_selected){
			reportProgress("Can't use " .$row->SCHEMA_NAME.":".mysqli_error().".\n");
			mysqli_close();
			exit;
		}
		reportProgress("update ".$row->SCHEMA_NAME." smw start .\n");
		foreach($sql as $v){
			$sqlstr = $v.';';
			$mysqli->query($sqlstr);
		}
	 }
}


$mysqli->close();

function getsql(){
	if(file_exists('smw.sql')){
		$lines=file('smw.sql');
		$sqlstr="";
		foreach($lines as $line){
			$line=trim($line);
			if($line!=""){
				if(!($line{0}=="#" || $line{0}.$line{1}=="--")){
					$sqlstr.=$line;
				}
			}
		}
		$sqls=explode(";",rtrim($sqlstr,";"));
		return $sqls;
	}else{
		reportProgress('Could not find wiki.sql '.".\n");
		exit();
	}
}




function reportProgress( $msg, $verbose = true ) {
	if ( $verbose ) {
		if ( ob_get_level() == 0 ) { // be sure to have some buffer, otherwise some PHPs complain
			ob_start();
		}

		print $msg;
		ob_flush();
		flush();
	}
}
?>