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

class MigrationData extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki data migration";
    }


    function execute() {

        //选择皮肤
        //$this->selectSkin();

        //添加新的数据库表
        $this->creatSql();
        //修改现有数据库表结构
        $this->updateSql();

        //更新站点编辑总数和is_new
        $this->updateSiteEditCount();
        //更新站点编辑人数
        $this->updateSiteEditUserCount();
        //更新站点页面总数
        $this->updateSitePageCount();

        //用户相关统计
        $this->updateUserEditCounts();
        //用户动态
        $this->userActionLog();
        
    }

    /**
     * 选择皮肤
     */
    public function selectSkin(){
        $dbw = wfGetDB(DB_MASTER);

        $dbw->query("ALTER TABLE `site_info`
	ADD COLUMN `skin_style` VARBINARY(50) NOT NULL DEFAULT '' COMMENT '站点皮肤' AFTER `mindex_status`;");

        $this->output( "ALTER skin_style done\n\n" );

        global $wgSkinsList,$wgSelectSkin;
        if($wgSkinsList){
            $mwSkins = array_keys($wgSkinsList);
            if(isset($mwSkins[$wgSelectSkin])
                &&$mwSkins[$wgSelectSkin]
            ){
                $skin_style = $mwSkins[$wgSelectSkin];
                $ret = $dbw->update(
                    'site_info',
                    array(
                        'skin_style' =>$skin_style
                    ),
                    array(),
                    __METHOD__
                );
                $dbw->commit(__METHOD__);
                if($ret){
                    $this->output( "selectSkin done\n\n" );
                }else{
                    $this->error( "selectSkin failed", true );
                }
            }else{
                $this->error( "skin is empty", true );
            }
        }else{
            $this->error( "wgSkinsList is empty", true );
        }
    }



    /**
     * 添加新的sql表
     */
    public function creatSql(){

        $dbw = wfGetDB(DB_MASTER);

        //文章附加表
        $dbw->query("CREATE TABLE `page_addons` (
  `page_id` int(11) NOT NULL DEFAULT '0' COMMENT '文章id',
  `like_count` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞',
  `short_comment_count` int(11) NOT NULL DEFAULT '0' COMMENT '短评总数',
  `last_edit_user` varbinary(255) DEFAULT NULL COMMENT '最后编辑人',
  `edit_count` int(11) NOT NULL DEFAULT '0' COMMENT '编辑次数',
  `pa_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更改时间',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章附加表';");

        //文章短评
        $dbw->query("CREATE TABLE IF NOT EXISTS `page_short_comment` (
  `psc_id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_id` INT(11) NOT NULL DEFAULT '0' COMMENT '文章id',
  `like_count` INT(11) NOT NULL DEFAULT '0' COMMENT '短评点赞',
  `body` VARCHAR(100) NOT NULL DEFAULT '0' COMMENT '文章短评',
  PRIMARY KEY (`psc_id`),INDEX(page_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章短评';");

        // 短评，短评点赞记录表
        $dbw->query("CREATE TABLE IF NOT EXISTS `page_short_comment_log` (
  `pscl_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` int(11) NOT NULL COMMENT '页面id',
  `psc_id` int(11) NOT NULL COMMENT '短评id',
  `user` varchar(32) NOT NULL COMMENT '用户',
  `type` smallint(6) NOT NULL COMMENT '1-添加，2-点赞',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`pscl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='wiki短评记录表';");


        //wiki点赞记录表
        $dbw->query("CREATE TABLE `page_clicklike` (
  `pcl_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `page_id` INT(11) NOT NULL COMMENT '页面id',
  `user` VARCHAR(32) NOT NULL COMMENT '用户',
  `create_time` INT(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`pcl_id`),
  UNIQUE KEY `user` (`user`,page_id)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='wiki点赞记录表';");
        
        //增加1.27新增表
        $dbw->query("CREATE TABLE `bot_passwords` (
        `bp_user` int(11) NOT NULL,
        `bp_app_id` varbinary(32) NOT NULL,
        `bp_password` tinyblob NOT NULL,
        `bp_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
        `bp_restrictions` blob NOT NULL,
        `bp_grants` blob NOT NULL,
        PRIMARY KEY (`bp_user`,`bp_app_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        //修改1.27索引
        $dbw->query("alter table `imagelinks` drop key `il_backlinks_namespace`, add index `il_backlinks_namespace` (`il_from_namespace`, `il_to`, `il_from`);");
        $dbw->query("alter table `pagelinks` drop key `pl_backlinks_namespace`, add index `pl_backlinks_namespace` (`pl_from_namespace`, `pl_namespace`, `pl_title`, `pl_from`);");
        $dbw->query("alter table `templatelinks` drop key `tl_backlinks_namespace`, add index `tl_backlinks_namespace` (`tl_from_namespace`, `tl_namespace`, `tl_title`, `tl_from`);");
        $dbw->query("alter table `watchlist` add column `wl_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT first, add primary key(`wl_id`);");
        
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

        $this->output( "creatSql done\n\n" );
    }

    /**
     * 修改数据库表
     */
    public function updateSql(){
        $dbw = wfGetDB(DB_MASTER);

        $dbw->query("ALTER TABLE `ajaxpoll_info` ADD COLUMN `poll_img` VARCHAR(256) NULL AFTER `poll_txt`;");

        $this->output( "updateSql done\n\n" );

    }


    /**
     * 更新站点页面总数
     */
    public function updateSitePageCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            $this->error( "wgSiteId empty", true );
        }
        $dbr = wfGetDB(DB_SLAVE);
        $pageCount = $dbr->selectRowCount(
            'page',
            '1',
            array(
                'page_namespace' => 0
            )
        );
        if($pageCount){
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->update(
                'joyme_sites',
                array(
                    'site_page_count' => $pageCount
                ),
                array('site_id' => $wgSiteId),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            if($ret){
                $this->output( "updateSitePageCount done\n\n" );
            }else{
                $this->error( "updateSitePageCount failed", true );
            }
        }else{
            $this->output( "pageCount empty\n\n" );
        }
    }


    /**
     * 更新站点编辑总数
     */
    public function updateSiteEditCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            $this->error( "wgSiteId empty", true );
        }
        $dbr = wfGetDB(DB_SLAVE);
        $pageCount = $dbr->selectRowCount('revision');
        if($pageCount){
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->update(
                'joyme_sites',
                array(
                    'site_edit_count' => $pageCount,
                	'is_new'=>1
                ),
                array('site_id' => $wgSiteId),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            if($ret){
                $this->output( "updateSiteEditCount done\n\n" );
            }else{
                $this->error( "updateSiteEditCount failed", true );
            }
        }else{
            $this->output( "updateSiteEditCount done\n\n" );
        }
    }

    //更新站点编辑人数
    public function updateSiteEditUserCount()
    {
        global $wgSiteId;

        if (empty($wgSiteId)) {
            $this->error( "wgSiteId empty", true );
        }
        $dbr = wfGetDB(DB_SLAVE);
        $editusers = $dbr->selectRowCount(
            'revision',
            '*',
            array('rev_user != 0'),
            __METHOD__,
            array(
                'GROUP BY' => 'rev_user'
            )
        );
        if($editusers){
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->update(
                'joyme_sites',
                array(
                    'site_edituser_count' => $editusers
                ),
                array('site_id' => $wgSiteId),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            if($ret){
                $this->output( "updateSiteEditUserCount done\n\n" );
            }else{
                $this->error( "updateSiteEditUserCount failed", true );
            }
        }else{
            $this->error( "editusers empty", true );
        }
    }

    /**
     * 1.用户在CQWIKI中的总编辑次数、编辑记录（总编辑次数点击后进入编辑明细）
    3.在管理WIKI中，WIKI页面总数、编辑总次数、编辑人数、关注人数（等同于编辑人数）
    4.在贡献WIKI中，用户贡献总次数
     */
    public function updateUserEditCounts(){
        global $wgSiteId;

        if (empty($wgSiteId)) {
            $this->error( "wgSiteId empty", true );
        }

        $dbr = wfGetDB(DB_SLAVE);
        $results = $dbr->select(
            'revision',
            "rev_user,count(rev_id) as edit_count",
            array('rev_user != 0'),
            __METHOD__,
            array(
                'GROUP BY' => 'rev_user'
            )
        );
        if($results){
            $dbw = wfGetDB(DB_MASTER);
            foreach ($results as $result){
                //用户总编辑数
                $user_addition = $dbr->selectRowCount(
                    'user_addition',
                    '*',
                    array('user_id' => $result->rev_user),
                    __METHOD__
                );
                if($user_addition){
                    $ret = $dbw->update(
                        'user_addition',
                        array("total_edit_count=total_edit_count+".$result->edit_count ),
                        array( 'user_id' => $result->rev_user  ),
                        __METHOD__
                    );
                    $dbw->commit(__METHOD__);
                    if($ret){
                        $this->output( $result->rev_user." update total_edit_count done\n\n" );
                    }else{
                        $this->error( $result->rev_user." update total_edit_count failed" , true);
                    }
                }else{
                    $ret = $dbw->insert(
                        'user_addition',
                        array(
                            'user_id' => $result->rev_user,
                            'total_edit_count' => $result->edit_count,
                        ),
                        __METHOD__
                    );
                    if($ret){
                        $this->output( $result->rev_user." insert total_edit_count done\n\n" );
                    }else{
                        $this->error( $result->rev_user." insert total_edit_count failed" , true);
                    }
                }


                //用户站点关系
                $user_groups = $dbr->selectRowCount(
                    'user_groups',
                    '*',
                    array(
                        'ug_user' => $result->rev_user,
                        "ug_group = 'bureaucrat' or ug_group = 'sysop'",
                    ),
                    __METHOD__
                );
                //判断是否是管理员
                if($user_groups){
                    $ret = JoymeWikiUser::addUserSiteManage($result->rev_user);
                    if($ret){
                        $this->output( $result->rev_user." user_site_relation done\n\n" );
                    }else{
                        $this->error( $result->rev_user." user_site_relation failed" , true);
                    }
                }else{
                    $ret = JoymeWikiUser::addUserSiteContribute($result->rev_user);
                    if($ret){
                        $this->output( $result->rev_user." user_site_relation done\n\n" );
                    }else{
                        $this->error( $result->rev_user." user_site_relation failed" , true);
                    }
                }

                //用户贡献次数
                $res = $dbr->selectRowCount(
                    'user_site_addition',
                    '*',
                    array(
                        'user_id' => $result->rev_user,
                        'site_id' => $wgSiteId,
                    )
                );
                if ($res) {
                    $ret = $dbw->update(
                        'user_site_addition',
                        array("contribution_count=contribution_count+".$result->edit_count ),
                        array(
                            'user_id' => $result->rev_user,
                            'site_id' => $wgSiteId,
                        ),
                        __METHOD__
                    );
                    $dbw->commit(__METHOD__);
                    if($ret){
                        $this->output( $result->rev_user." update contribution_count done\n\n" );
                    }else{
                        $this->error( $result->rev_user." update contribution_count failed" , true);
                    }
                } else {
                    $ret = $dbw->insert(
                        'user_site_addition',
                        array(
                            'user_id' => $result->rev_user,
                            'site_id' => $wgSiteId,
                            'contribution_count' => $result->edit_count
                        ),
                        __METHOD__
                    );
                    if($ret){
                        $this->output( $result->rev_user." insert contribution_count done\n\n" );
                    }else{
                        $this->error( $result->rev_user." insert contribution_count failed" , true);
                    }
                }

            }
        }else{
            $this->output( "updateUserEditCounts results is empty\n\n" );
        }
    }

    /**
     * 2.用户在CQWIKI中的动态放入到 我的动态内
     */
    public function userActionLog(){
        global $wgWikiname,$wgServer;

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'recentchanges',
            "*",
            array('rc_user != 0'),
            __METHOD__
        );
        if(!$res){
            $this->output( "recentchanges results is empty\n\n" );
        }else{
            foreach($res as $k=>$v){
                if($v->rc_new == 1){
                    /*$ret = $this->addActionLog(
                        $v->rc_user,
                        3,
                        '创建了页面<a href="/'.$wgWikiname.'/'.$v->rc_title.'" target="_blank">'.$v->rc_title.'</a>',
                        $v->rc_timestamp
                    );*/
                    $ret = JoymeWikiUser::adduseractivity(
                        $v->rc_user,
                        'add_page',
                        '创建了页面 <a href="'.$wgServer.'/'.$wgWikiname.'/'.$v->rc_title.'" target="_blank">'.$v->rc_title.'</a>'
                    );
                    if($ret){
                        $this->output( $v->rc_user." insert userActionLog done\n\n" );
                    }else{
                        $this->error( $v->rc_user." insert userActionLog failed" , true);
                    }
                }else{
                    /*$ret = $this->addActionLog(
                        $v->rc_user,
                        4,
                        '修改了页面<a href="/'.$wgWikiname.'/'.$v->rc_title.'" target="_blank">'.$v->rc_title.'</a>',
                        $v->rc_timestamp
                    );*/
                    $ret = JoymeWikiUser::adduseractivity(
                        $v->rc_user,
                        'edit_page',
                        '修改了页面 <a href="'.$wgServer.'/'.$wgWikiname.'/'.$v->rc_title.'" target="_blank">'.$v->rc_title.'</a>'
                    );
                    if($ret){
                        $this->output( $v->rc_user." update userActionLog done\n\n" );
                    }else{
                        $this->error( $v->rc_user." update userActionLog failed" , true);
                    }
                }
            }
        }
    }

    /**
     * 添加用户动态
     */
    public function addActionLog($user_id, $type, $content,$add_time)
    {
        $data = array();
        if (empty($user_id)) {
            $this->error( $user_id." userActionLog user_id empty" , true);
        } else {
            $data['user_id'] = $user_id;
        }
        if (empty($type)) {
            $this->error( $user_id." userActionLog type empty" , true);
        }
        $joymewikiuser = new JoymeWikiUser();
        $types = array_keys($joymewikiuser->useractiontypes);
        if (!in_array($type, $types)) {
            $this->error( $user_id." userActionLog type error" , true);
        }

        $data['type'] = (int)$type;

        if (empty($content)) {
            $this->error( $user_id." userActionLog content empty" , true);
        } else {
            $data['content'] = $content;
        }

        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->insert(
            'user_action_log',
            array(
                'user_id' => $user_id,
                'type' => $type,
                'content' => $content,
                'add_time' => strtotime($add_time),
            )
        );
        $dbw->commit();
        return $ret;
    }

}

$maintClass = 'MigrationData';
require_once RUN_MAINTENANCE_IF_MAIN;
