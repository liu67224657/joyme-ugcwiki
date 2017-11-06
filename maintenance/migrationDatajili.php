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

class MigrationDataJiLi extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki data migration";
    }


    function execute() {
    	$this->output( "starttime ".date('Y-m-d H:i:s')." \n\n" );
        //添加新的数据库表
        //$this->updatedata();

		//迁移用户动态到java
		//$this->migrationUALtoJava();
		
    	//迁移提醒消息到java
    	//$this->migrationNoticetoJava();
    	
        //迁移用户隐私设置
        //$this->migrationPrivacytoJava();
        
    	$this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );
        
    }
    
    public function updatedata(){
    	$dbr = wfGetDB(DB_SLAVE);
    	
    	$wikilist = $dbr->select(
    			'joyme_sites',
    			'site_key',
    			array(
    					'is_new'=>1
    			),
    			__METHOD__,
    			array('limit'=>1000)
    	);
    	if($wikilist){
    		foreach($wikilist as $k=>$v){
    			if( $v->site_key != 'home' ){
    				$this->creatSql($v->site_key);
    				//$this->updateRankHot($v->site_key);
    			}
    		}
    	}else{
    		$this->output( "no new ugcwiki \n\n" );
    	}
    }
    
    /**
     * 添加新的sql表
     */
    public function creatSql( $wikikey ){
    
    	$dbw = wfGetDB(DB_MASTER);
    	$rs = $dbw->selectDB($wikikey.'wiki');
    	if($rs == false){
    		return false;
    	}
    
    	//更新附加表
    	$dbw->query("alter table `page_addons` 
   add column `contribute_id` int(11) DEFAULT '0' NOT NULL COMMENT '贡献id' after `pa_timestamp`,
   add column `contribute_uid` int(11) DEFAULT '0' NOT NULL COMMENT '核心贡献者id' after `contribute_id`,
   add column `quality` int(11) DEFAULT '0' NOT NULL COMMENT '文章质量' after `contribute_uid`;");
    	
    	//$dbw->query("alter table `page_addons`  add  index quality_idx (  `quality`  );");
    	
    	//增加用户贡献表
    	$dbw->query("CREATE TABLE `page_contribute` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(11) NOT NULL COMMENT '用户id',
  `page_id` INT(11) NOT NULL COMMENT '页面id',
  `edit_bytes` INT(11) NOT NULL DEFAULT '0' COMMENT '编辑字节数',
  `edit_count` INT(10) NOT NULL DEFAULT '0' COMMENT '编辑次数',
  `thanks_count` INT(10) NOT NULL DEFAULT '0' COMMENT '膜拜次数',
  `contributes` DOUBLE(11,2) NOT NULL DEFAULT '0' COMMENT '贡献值',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_pageid_idx` (`uid`,`page_id`)
) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;");
    	
    	//增加感谢大神表
    	$dbw->query("CREATE TABLE `page_contribute_thanks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` INT(11) NOT NULL COMMENT '用户id',
  `contribute_id` INT(11) NOT NULL COMMENT '贡献表id',
  `t_type` SMALLINT(10) NOT NULL DEFAULT '1' COMMENT '操作类型 1感谢2膜拜',
  `t_count` INT(10) NOT NULL DEFAULT '1' COMMENT '次数',
  PRIMARY KEY (`id`),
  KEY `uid_contrubuteid_idx` (`contribute_id`,`uid`,`t_type`)
) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;");
    	
    	//导入历史数据
    	$dbw->query("INSERT INTO page_contribute(uid,page_id,edit_bytes,edit_count,contributes) 
SELECT rev_user,rev_page,rev_len,1 AS num,rev_len AS contributes FROM revision WHERE rev_user>0 AND revision.rev_id IN (
SELECT  r1.rev_id
FROM revision r1       
WHERE  (SELECT COUNT(1) FROM revision r2 WHERE r2.rev_page=r1.rev_page AND r2.rev_user=r1.rev_user  AND r2.rev_id >= r1.rev_id) <=1
);");
    	//导入历史数据
    	//$dbw->query("UPDATE page_addons,page_contribute SET contribute_id=page_contribute.id,contribute_uid=page_contribute.uid 
    	//	WHERE page_addons.page_id=page_contribute.page_id;");
    	
    	$dbw->query("UPDATE page_addons,page_contribute SET contribute_id=page_contribute.id,contribute_uid=page_contribute.uid 
WHERE page_addons.page_id=page_contribute.page_id 
AND page_contribute.id IN (
SELECT  r1.id
FROM page_contribute r1       
WHERE  (SELECT COUNT(1) FROM page_contribute r2 WHERE r2.page_id=r1.page_id AND r2.contributes >= r1.contributes) <=1
);");
    	
    	$dbw->commit();
    
    	$this->output( "creatSql ".$wikikey." done\n\n" );
    }
    
    //更新热度排行榜
    public function updateRankHot( $wikikey ){
    	global $wgMemc;
    	$dbr = wfGetDB(DB_SLAVE);
    	
    	$rs = $dbr->selectDB($wikikey.'wiki');
    	if($rs == false){
    		return false;
    	}
    	
    	$mcwikikey = $wikikey.'wiki';

    	$starttime = mktime(0,0,0,date('m')-1,1,date('Y'));
		
    	$key = 'hot';
    	$key_now_w = wfForeignMemcKey( $mcwikikey,false,'joymerank',$key,date('Y-m-d',(time()-((date('N'))-1)*24*3600)).'w' );
    	
    	$key_now_m = wfForeignMemcKey( $mcwikikey,false,'joymerank',$key,date('Y-m-01',time()).'m' );
    	 
    	$key_last_w = wfForeignMemcKey( $mcwikikey,false,'joymerank',$key,date('Y-m-d',(time()-((date('N'))+6)*24*3600)).'w' );
    	
    	$key_last_m = wfForeignMemcKey( $mcwikikey,false,'joymerank',$key,date('Y-m-01',mktime(0,0,0,date('m')-1,1,date('Y')) ).'m' );
		
    	//短评部分
    	$sql = 'SELECT COUNT(pscl_id) AS num,page_id,DATE_FORMAT(FROM_UNIXTIME(create_time),"%x-%m-%d") AS time FROM page_short_comment_log where create_time>='.$starttime.' GROUP BY page_id,time;';
    	$short_rs = $dbr->query($sql);
    	
    	while ( $row = $short_rs->fetchRow() ) {
    		if( date( 'Y-m-01', strtotime($row['time']) ) == date( 'Y-m-01', time() ) ){
    			$wgMemc->zIncrBy($key_now_m, $row['num'], $row['page_id']);
    		}elseif( date( 'Y-m-01', strtotime($row['time']) ) == date( 'Y-m-01', mktime(0,0,0,date('m')-1,1,date('Y')) ) ){
    			$wgMemc->zIncrBy($key_last_m, $row['num'], $row['page_id']);
    		}
    		
    		if( date( 'Y-m-d', strtotime($row['time'])-((date('N',strtotime($row['time'])))-1)*24*3600 ) == date('Y-m-d',(time()-((date('N'))-1)*24*3600)) ){
    			$wgMemc->zIncrBy($key_now_w, $row['num'], $row['page_id']);
    		}elseif( date( 'Y-m-d', strtotime($row['time'])-((date('N',strtotime($row['time'])))-1)*24*3600 ) == date('Y-m-d',(time()-((date('N'))+6)*24*3600)) ){
    			$wgMemc->zIncrBy($key_last_w, $row['num'], $row['page_id']);
    		}
    	}
    	$this->output( "updateRankHot ".$wikikey." shortcomment done\n\n" );
    	//文章点赞部分
    	
    	$sql = 'SELECT COUNT(pcl_id) AS num,page_id,DATE_FORMAT(FROM_UNIXTIME(create_time),"%x-%m-%d") AS time FROM page_clicklike where create_time>='.$starttime.' GROUP BY page_id,time;';
    	$clicklike_rs = $dbr->query($sql);
    	 
    	while ( $row = $clicklike_rs->fetchRow() ) {
    		if( date( 'Y-m-01', strtotime($row['time']) ) == date( 'Y-m-01', time() ) ){
    			$wgMemc->zIncrBy($key_now_m, $row['num'], $row['page_id']);
    		}elseif( date( 'Y-m-01', strtotime($row['time']) ) == date( 'Y-m-01', mktime(0,0,0,date('m')-1,1,date('Y')) ) ){
    			$wgMemc->zIncrBy($key_last_m, $row['num'], $row['page_id']);
    		}
    	
    		if( date( 'Y-m-d', strtotime($row['time'])-((date('N',strtotime($row['time'])))-1)*24*3600 ) == date('Y-m-d',(time()-((date('N'))-1)*24*3600)) ){
    			$wgMemc->zIncrBy($key_now_w, $row['num'], $row['page_id']);
    		}elseif( date( 'Y-m-d', strtotime($row['time'])-((date('N',strtotime($row['time'])))-1)*24*3600 ) == date('Y-m-d',(time()-((date('N'))+6)*24*3600)) ){
    			$wgMemc->zIncrBy($key_last_w, $row['num'], $row['page_id']);
    		}
    	}
    	
    	$this->output( "updateRankHot ".$wikikey." clicklike done\n\n" );
    	
    }

    //迁移用户动态数据到java
	public function migrationUALtoJava(){
		global $wgServer;
		
		$dbr = wfGetDB(DB_SLAVE);
		$ualcount = $dbr->selectRowCount(
			'user_action_log'
		);
		$ual_times = 0;
		if($ualcount){
			//限制条数
			$limit = 1000;
			for($offset = 0; ($offset*$limit) <= $ualcount; $offset++){
				//偏移量
				$skip = $offset*$limit;
				$res = $dbr->select(
					'user_action_log',
					'*',
					array(),
					__METHOD__,
					array(
						'LIMIT' => $limit,
						'OFFSET' => $skip
					)
				);
				if($res){
					foreach ($res as $k => $row){

						//拼接用户动态内容
						if(strstr($row->content,'"/')){
							$a = explode('"/',$row->content);
							$content = $a[0].'"'.$wgServer.'/'.$a[1];
						}else{
							$content = $row->content;
						}
						
						$time = $row->add_time;

						if($row->type==1){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'add_wiki',
								$content,
								$time
							);
						}
						elseif ($row->type==2){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'focus_wiki',
								$content,
								$time
							);
						}
						elseif ($row->type==3){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'add_page',
								$content,
								$time
							);
						}
						elseif ($row->type==4){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'edit_page',
								$content,
								$time
							);
						}
						elseif ($row->type==5){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'focus_user',
								$content,
								$time
							);
						}
						elseif ($row->type==6){
							$rs = JoymeWikiUser::adduseractivity(
								$row->user_id,
								'favirate_page',
								$content,
								$time
							);
						}
						
						if($rs){
							$ual_times++;
						}
					}
				}
				$this->output( "useractionlog count is ".$ual_times."\n\n" );
			}
			$this->output( "useractionlog over, count is ".$ual_times."\n\n" );
		}else{
			$this->output( "useractionlog over, count is 0\n\n" );
		}
	}
	
	//迁移用户隐私数据到java
	public function migrationPrivacytoJava(){
		global $wgServer;
	
		$dbr = wfGetDB(DB_SLAVE);
		$ualcount = $dbr->selectRowCount(
				'user_properties'
		);
		$properties_times = 0;
		if($ualcount){
			//限制条数
			$limit = 1000;
			for($offset = 0; ($offset*$limit) <= $ualcount; $offset++){
				//偏移量
				$skip = $offset*$limit;
				$res = $dbr->select(
						'user_properties',
						'*',
						array(),
						__METHOD__,
						array(
								'LIMIT' => $limit,
								'OFFSET' => $skip
						)
				);
				if($res){
					foreach ($res as $k => $row){
						$data = array();
						$data['uid'] = $row->up_user;
						switch ($row->up_property){
							case 'echo-subscriptions-web-article-cite-my':
								$data['userat'] = 0;
								break;
							case 'echo-subscriptions-web-article-comments':
								$data['comment'] = 0;
								break;
							case 'echo-subscriptions-web-article-thumb-up':
								$data['agreement'] = 0;
								break;
							case 'echo-subscriptions-web-article-consider-me':
								$data['follow'] = 0;
								break;
							case 'echo-subscriptions-web-echo-system-message':
								$data['systeminfo'] = 0;
								break;
							default:break;
						}
						if(count($data)<=1){
							continue;
						}
						$rs = JoymeWikiUser::updateuserprivacy($data);
						if($rs){
							$properties_times++;
						}
					}
				}
				$this->output( "properties count is ".$properties_times."\n\n" );
			}
			$this->output( "properties over, count is".$properties_times."\n\n" );
		}else{
			$this->output( "properties over, count is 0\n\n" );
		}
	}
	
	//迁移用户提醒消息数据到java
	public function migrationNoticetoJava(){
		global $wgServer,$wgEnv;
		
		$dbw = wfGetDB(DB_MASTER);
		
		$dbw->query('alter table `echo_event` 
				add column `event_uid` int(11) NULL after `event_page_id`,
   				add column `event_create_time` binary(14) NULL after `event_uid`');
		
		$sql = 'UPDATE echo_event AS a,echo_notification AS b SET a.event_uid=b.notification_user,a.event_create_time=b.notification_timestamp WHERE a.event_id=b.notification_event;';
		
		$dbw->query($sql);
		
		$noticetimes = 0;
	
		$dbr = wfGetDB(DB_SLAVE);
		$ualcount = $dbr->selectRowCount(
				'echo_event',
				'*',
				array('event_type'=>array('article-cite-my','article-comments','article-consider-me','article-thumb-up')),
				__METHOD__
		);
		if($ualcount){
			//限制条数
			$limit = 1000;
			for($offset = 0; ($offset*$limit) <= $ualcount; $offset++){
				//偏移量
				$skip = $offset*$limit;
				$res = $dbr->select(
						'echo_event',
						'*',
						array('event_type'=>array('article-cite-my','article-comments','article-consider-me','article-thumb-up')),
						__METHOD__,
						array(
								'LIMIT' => $limit,
								'OFFSET' => $skip
						)
				);
				if($res){
					foreach ($res as $k => $row){
						
						$event_data = unserialize($row->event_extra);

						if(empty($event_data)){
							continue;
						}elseif(empty($event_data['user_id'])){
							continue;
						}elseif(empty($row->event_uid)){
							continue;
						}
						
						$type = empty($event_data['type'])?'':$event_data['type'];
						$event_data['from'] = empty($event_data['from'])?'':$event_data['from'];
						$event_data['pid']  = empty($event_data['pid'])?'':$event_data['pid'];
						
						$url = '';
						$desttype = '';
						$curl = '';
						
						if ( isset( $row->event_create_time ) ) {
							$timestamp = (strtotime($row->event_create_time) +8*3600 )*1000;
						} else {
							$timestamp = time()*1000;
						}
						
						if($row->event_type == 'article-cite-my'){
							$ntype = 'at';
							$url  = '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$event_data['wikikey'].'/'.$event_data['article'].'">'.$event_data['article'].'</a>';
							if(strstr($event_data['synopsis'],'"/')){
								$a = explode('"/',$event_data['synopsis']);
								$curl = $a[0].'"'.$wgServer.'/'.$a[1];
							}else{
								$curl = $event_data['synopsis'];
							}
							
						}elseif($row->event_type == 'article-comments'){
							$ntype = 'reply';
							if($type == 1){
								$desttype = 1;
							}elseif($type == 2){
								$desttype = 2;
							}else{
								$desttype = 3;
							}
							$url  = '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$event_data['wikikey'].'/'.$event_data['article'].'">'.$event_data['article'].'</a>';
							$curl = '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$event_data['wikikey'].'/'.$event_data['article'].'?plid='.$event_data['pid'].'">'.$event_data['synopsis'].'</a>';
						}elseif($row->event_type == 'article-thumb-up'){
							$ntype = 'agree';
							if($type == 1){
								$desttype = 5;
							}else{
								$desttype = 4;
							}
							$curl = '<a target="_blank" href="http://wiki.joyme.'.$wgEnv.'/'.$event_data['wikikey'].'/'.$event_data['article'].'?plid='.$event_data['pid'].'">'.$event_data['synopsis'].'</a>';
						}elseif($row->event_type == 'article-consider-me'){
							$ntype = 'follow';
							if($type == 1){
								$desttype = 10;
							}else{
								$desttype = 9;
							}
						}else{
							continue;
						}
						$rs = JoymeWikiUser::noticereport(array(
								'tuid' => $row->event_uid,
								'uid' => $event_data['user_id'],
								'ntype' => $ntype,
								'url'  => $url,
								'curl' => $curl,
								'desc' => $event_data['from'],
								'desttype' => $desttype,
								'time'=>$timestamp
						));
						if($rs === true){
							$noticetimes++;
						}
					}
				}else{
					$this->output( "no data\n\n" );
				}
				$this->output( "user event count is ".$noticetimes."\n\n" );
			}
			$this->output( "user event over, count is ".$noticetimes."\n\n" );
		}else{
			$this->output( "user event over, count is 0\n\n" );
		}
	}

}

$maintClass = 'MigrationDataJiLi';
require_once RUN_MAINTENANCE_IF_MAIN;
