#!/usr/bin/env php
<?php
/**
 * Description: wikiapp相关wiki文章数据导入到wikiservice
 * Author: gradydong
 * Date: 2017/4/24
 * Time: 14:01
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
use Joyme\net\Curl;
class MigrationDatajwikiadmin extends Maintenance {

    function __construct() {
        parent::__construct();
        $this->mDescription = "MediaWiki data migration wikiadmin";
    }

    function execute() {
		global $wgEnv, $wgWikiname, $wgSiteGameTitle;
    	$this->output( "starttime ".date('Y-m-d H:i:s')." \n\n" );
		$dbw = wfGetDB(DB_MASTER);
		$results = $dbw->select(
			'page',
			array("page_title","page_touched"),
			array(
				'page_namespace' => 0
			),
			__METHOD__
		);
		if ($results) {
			$curl = new Curl();
			foreach ($results as $result) {
				if($result->page_touched){
					$page_touched = (strtotime($result->page_touched)+28800);
					$publishtime = floatval($page_touched) * 1000;
				}else{
					$publishtime = time()*1000;
				}
				$data = array(
					'wikikey' => $wgWikiname,
					'title' => $result->page_title,
					'wikiname' => $wgSiteGameTitle,
					'weburl' => 'http://wiki.joyme.'.$wgEnv.'/' . $wgWikiname . '/' . $result->page_title . '?useskin=MediaWikiBootstrap2',
					'publishtime' => $publishtime
				);
				$url = 'http://wikiservice.joyme.' . $wgEnv . '/api/wiki/content/wikipost';
				$res = $curl->Post($url, $data);
				$res = json_decode($res, true);
				if ($res['rs'] != '1') {
					$res['data'] = $data;
					$this->output( var_export($res,true)." \n\n" );
				}else{
					$this->output( $result->page_title." end \n\n" );
				}
			}
		}
    	$this->output( "endtime ".date('Y-m-d H:i:s')." \n\n" );
    }


}

$maintClass = 'MigrationDatajwikiadmin';
require_once RUN_MAINTENANCE_IF_MAIN;
