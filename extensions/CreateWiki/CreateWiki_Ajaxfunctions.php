<?php
$wgAjaxExportList[] = 'wfCreateWikiCheckWikiKey';
$wgAjaxExportList[] = 'wfCreateWikiKeyList';

function wfCreateWikiCheckWikiKey(  ){

    $key = $_GET['wiki_key'];
    echo RecommendUsers::returnJson( CreateWikiClass::find_Joyme_Key_Exist( $key ) );
    exit;
}

function wfCreateWikiKeyList(){
	$where = 'create_time>'.(time()-86400);
	$list = CreateWikiClass::joyme_Site_List(10,0,$where);
	$data = array();
	if($list){
		foreach($list as $v){
			$data[] = $v->site_key;
		}
		if($data){
			$out = JoymeWikiUser::getJson($data,'1');
		}else{
			$out = JoymeWikiUser::getJson($data,'0');
		}
	}else{
		$out = JoymeWikiUser::getJson($data,'0');
	}
	
	echo $out;exit;
}