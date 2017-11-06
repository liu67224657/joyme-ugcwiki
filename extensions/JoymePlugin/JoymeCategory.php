<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-1-113
 * Time: 下午17:15
 * To change this template use File | Settings | File Templates.
 * 
 */

$wgExtensionCredits['other'][] = array(
		'path' => __FILE__,
		'name' => 'JoymeCategory',
		'url' => 'http://wiki.joyme.com',
		'author' => array( 'TianMing' ),
		'descriptionmsg' => '自动调用分类',
);

function JoymeCategory(){
    global $wgParser;
    $wgParser->setHook( "JoymeCategory", "showCategory" );
}

function showCategory($input, $argv){
	global $wgServer;
	if(empty($argv['title'])){
		return 'no title';
	}
	
	$title = $argv['title'];
	$limit = empty($argv['limit'])?5:intval($argv['limit']);
	$class = empty($argv['class'])?'':$argv['class'];
	
	if(!empty($argv['order']) && $argv['order'] == 'hot'){
		$orderby = 'page_counter';
	}else{
		$orderby = 'page_latest';
	}
	
    $dbr = wfGetDB( DB_MASTER );
    
	$r = '<ul class="j-box '.$class.'">';
	
	$res = $dbr->select(
			array('categorylinks','page'),
			array('page_id','page_title','page_latest'),
			array('page_namespace'=>'0','cl_to'=>$title),
			__METHOD__,
			array(
			    	'USE INDEX' => array( 'categorylinks' => 'cl_sortkey' ),
					'ORDER BY' => $orderby.' DESC',
					'LIMIT' => $limit
			),
			array(
					'page' => array( 'INNER JOIN', 'page_id = cl_from' )
			)
	);
	
	$datalist = array();
	$pageids = array('0');
	$revids = array('0');
	while ( $row = $res->fetchRow() ) {
		$datalist[] = $row;
		$pageids[]=$row['page_id'];
		$revids[]=$row['page_latest'];
	}
	if($datalist){
		//取出page所有的分类
		$res = $dbr->select(
				array('categorylinks'),
				array('cl_to','cl_from'),
				array('cl_type'=>'page','cl_from'=>$pageids),
				__METHOD__
		);
		$categorylist = array();
		while ( $row = $res->fetchRow() ) {
			$categorylist[] = $row;
		}
			
		//取出page所有的记录 时间
		$res = $dbr->select(
				array('revision'),
				array('rev_id','rev_timestamp'),
				array('rev_id'=>$revids),
				__METHOD__
		);
		$revlist = array();
		while ( $row = $res->fetchRow() ) {
			$revlist[] = $row;
		}
			
		foreach ($datalist as $v){
			$r.='<li>';
			$r.='<a href="/wiki/'.$v['page_title'].'">'.$v['page_title'] . '</a>';
			$categorystr = '<span>文章关键字: ';

			$i = 1;
			foreach ($categorylist as $p){
				if($p['cl_from'] == $v['page_id']){
					if($p['cl_to'] != 'Hot'){
						if($i>5) break;
						$i++;
						$categorystr.='<cite>'.$p['cl_to'].'</cite>';
					}
				}
			}
			$categorystr.='</span>';
			
			$r.=$categorystr;
	
			$edittime = date('Y-m-d H:i',time());
			foreach ($revlist as $p){
				if($p['rev_id'] == $v['page_latest']){
					$edittime = date('Y-m-d',strtotime($p['rev_timestamp'])+8*3600);
					break;
				}
			}
			$r.='<code>'.$edittime.'</code>';
			$r.='</li>';
		}
	}
	$r.='</ul>';
    
    return $r;
}
$wgExtensionFunctions[] = "JoymeCategory";

?>