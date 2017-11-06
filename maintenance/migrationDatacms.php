#!/usr/bin/env php
<?php
/**
 * Description:
 * Author: gradydong
 * Date: 2016/8/8
 * Time: 21:01
 * Copyright: Joyme.com
 */

if(empty($argv[1])){
    echo 'no argv[1]';exit;
}
if(empty($argv[2])){
    echo 'no argv[2]';exit;
}
/*if(empty($argv[3])){
    echo 'no argv[3]';exit;
}*/
$_SERVER['HTTP_HOST'] = 'wiki.joyme.'.$argv[2];
$_SERVER['REQUEST_URI'] = '/'.$argv[1].'/';
$_SERVER['QUERY_STRING'] = '';


require_once __DIR__ . '/Maintenance.php';

class MigrationDataCms extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki data migration cms";
    }


    function execute() {
    	$this->output( "starttime ".date('Y-m-d H:i:s')." \n\n" );
    	
    	$this->createtable();
        //添加新的数据库表
        $this->updatedata();
        
    	$this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );
        
    }
    
    public function createtable(){
    	$dbw = wfGetDB(DB_MASTER);
    	 
    	$rs = $dbw->selectDB('homewiki');
    	if($rs == false){
    		$this->output( "use homewiki false \n\n" );
    		$this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );
    		exit;
    	}
    	 
    	//增加用户贡献表
    	$dbw->query("CREATE TABLE `joyme_archives_wiki` (
		  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `channle_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '推荐的栏目名称',
		  `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '条目名称',
		  `sid` INT(5) NOT NULL DEFAULT '0' COMMENT '站点ID',
		  `edit_time` INT(11) NOT NULL DEFAULT '0' COMMENT '条目编辑时间',
		  `rec_time` INT(11) NOT NULL DEFAULT '0' COMMENT '推荐时间',
		  `aid` INT(11) NOT NULL DEFAULT '0' COMMENT '文章ID',
		  PRIMARY KEY (`id`),
		  KEY `edit_time_idx` (`edit_time`),
		  KEY `rec_time_idx` (`rec_time`),
		  KEY `sid_idx` (`sid`),
		  KEY `title_idx` (`title`),
		  KEY `aid_idx` (`aid`)
		) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;");
    	
    	$dbw->commit();
    }
    
    public function updatedata(){
    	$dbr = wfGetDB(DB_SLAVE);
    	
    	$wikilist = $dbr->select(
    			'joyme_sites',
    			'site_key,site_id',
    			array(),
    			__METHOD__,
    			array('limit'=>1000)
    	);
    	if($wikilist){
    		$zz = 0;
    		foreach($wikilist as $k=>$v){
    			if( $v->site_key != 'home' ){
    				$num = $this->creatSql($v->site_key,$v->site_id);
    				//$zz += intval($num);
    			}
    		}
    		//$this->output( "zz ".$zz." \n\n" );
    	}else{
    		$this->output( "no new ugcwiki \n\n" );
    	}
    }
    
    /**
     * 添加新的sql表
     */
    public function creatSql( $wikikey ,$sid){
    
    	$dbw = wfGetDB(DB_MASTER);
    	$rs = $dbw->selectDB( $wikikey.'wiki' );
    	if($rs == false){
    		return false;
    	}
    	/*
    	$rs = $dbw->query('SELECT count(distinct(rc_cur_id)) as num FROM recentchanges WHERE rc_namespace=0 AND rc_timestamp>20161206000000');
    	
    	$res = $rs->fetchRow();
    	return $res['num'];
    	exit;
    	*/
    	
    	//导入joyme_archives_wiki表
    	$dbw->query("INSERT INTO homewiki.joyme_archives_wiki(sid,title,edit_time) 
SELECT ".$sid.",rc_title,UNIX_TIMESTAMP(rc_timestamp) FROM recentchanges WHERE rc_namespace=0 AND rc_timestamp>20170201000000 GROUP BY rc_cur_id;");
    	
    	
    	$dbw->commit();
    
    	$this->output( "source cms ".$wikikey." done\n\n" );
    }

}

$maintClass = 'MigrationDataCms';
require_once RUN_MAINTENANCE_IF_MAIN;
