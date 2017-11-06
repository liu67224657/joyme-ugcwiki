<?php
/**
 * Created by Zend Studio.
 * User: TianMing
 * Date: 2016/12/12
 * Time: 15:30
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

use Joyme\net\Curl;

$wgExtensionCredits['parserhook'][] = array(
		'path' => __FILE__,
		'name' => 'Rank',
		'version' => '1.1.0',
		'author' => array( 'TianMing' ),
		'descriptionmsg' => '添加<joymerank>标签用于排行榜',
		'url' => 'https://wiki.joyme.com',
);

// Classes
$wgAutoloadClasses['JoymeRank'] = __DIR__ . '/JoymeRankClass.php';

$wgExtensionFunctions[] = "wfJoymeRankHook";
function wfJoymeRankHook() {
    global $wgParser;
    $wgParser->setHook( "joymerank", "JoymeRankList" );
}

// type: 1:声望榜、2:积分榜、3:积分消费榜、4:最热攻略、5:最新攻略、6:最佳攻略

function JoymeRankList( $input,$argv ){
	
    global $wgWikiname,$wgParser,$wgLang,$wgUser,$wgUserCenterUrl;
    
    $wgParser->disableCache();
    $out = $wgParser->getOutput();
    
    $out->addModuleScripts('ext.joymescript.rank.js');
    $out->addModuleStyles('ext.joymescript.rank.css');

    $limit = isset($argv['limit'])?intval($argv['limit']):10;
    $type  = !empty($argv['type'])?intval($argv['type']):1;
    
    if($limit <=0 ){
    	return '';
    }
    
    $limit = intval($limit)>50?50:$limit;
    
    $rankList = array(
    	1=>array(
    		'name'=>'大神榜',
    		'des'=>'获得声望',
    		'class'=>'sheng'
    		),
    	2=>array(
    		'name'=>'财富榜',
    		'des'=>'获得积分',
    		'class'=>'jifen'
    		),
    	3=>array(
    		'name'=>'土豪榜',
    		'des'=>'消费积分',
    		'class'=>'xiaofei'
    		),
    	4=>array(
    		'name'=>'最热攻略',
    		'des'=>'',
    		'class'=>' hot-plan'
    		),
    	5=>array(
    		'name'=>'最新攻略',
    		'des'=>'',
    		'class'=>' hot-plan new-plan'
    		),
    	6=>array(
    		'name'=>'最佳攻略',
    		'des'=>'',
    		'class'=>' hot-plan best-plan'
    		),		
    );
    
    switch ($type){
    	case 1:
    		$data = JoymeRank::getRankData('prestige',$limit);
    		break;
    	case 2:
    		$data = JoymeRank::getRankData('point',$limit);
    		break;
    	case 3:
    		$data = JoymeRank::getRankData('consumption',$limit);
    		break;
    	case 4:
    		$data = JoymeRank::getContentRankData('hot',$limit);
    		break;
    	case 5:
    		$data = JoymeRank::getContentRankData('new',$limit);
    		break;
    	case 6:
    		$data = JoymeRank::getContentRankData('good',$limit);
    		break;
    	default:
    		return '';
    }
    
    //判断异常
    if($data['rs'] != 1){
    	return 'rank error';
    }

    $html = '<div class="sort-list'.($type>3?$rankList[$type]['class']:'').'">
      <div class="list-header0 clearfix">';
    	if($type != 5){
        $html .='<ul class="sort-btns fr">
          <li class="sort-btn on">周榜</li>|
          <li class="sort-btn">月榜</li>
          '.($type<=3?'|<li class="sort-btn">历史</li>':'').'
        </ul>';
    	}
        $html.='<h1 class="fl sort-list-header">'.$rankList[$type]['name'].'</h1>
      </div>
      <ul class="sort-list-option-box">
        <li class="sl-option on">
          <ul class="sl-op-inul">';
    		if(empty($data['result']['week'])){
    			$html .='<li>暂无数据</li>';
    		}else{
    			if($type<=3){
		    		foreach($data['result']['week'] as $k=>$v){
		            $html .= '<li class="sl-op-op">
		              <div class="opop-left">'
		            	.'<a class="opop-l-link" href="'.$wgUserCenterUrl.$v['pid'].'"><img class="opop-item-img" src="'.$v['pic'].'" alt="'.$v['nick'].'"></a>
		              </div>
		              <div class="opop-right">
		                <p class="op-r-h"><a href="'.$wgUserCenterUrl.$v['pid'].'">'.$v['nick'].'</a></p>
		                <p class="op-r-s op-r-'.$rankList[$type]['class'].' ">'.$rankList[$type]['des'].'：<span>'.$v['value'].'</span></p>'
		                .'<span class="list-num00 list-num'.($k+1).'"></span>
		              </div>
		            </li>';
		    		}
    			}elseif($type==5){
    				foreach($data['result']['week'] as $k=>$v){
    					
    					$uptime = $wgLang->userTimeAndDate($v['rc_timestamp'], $wgUser );
    					$uplen = $v['rc_new_len']-$v['rc_old_len'];
    					$uplen = $uplen>0?'+'.$uplen:$uplen;
    					$html .= '<li class="sl-op-op">
		              <div class="opop-right">
		                <p class="op-r-h">'.$uptime.' <a href="/home/用户:'.$v['rc_user_text'].'">'.$v['rc_user_text'].'</a>'.($v['rc_new'] == 1?'新增':'更新').'了<a href="/'.$wgWikiname.'/'.$v['rc_title'].'">'.$v['rc_title'].'</a>（'.$v['rc_new_len'].'字节） （'.$uplen.'）</p>
		              </div>
		            </li>';
    				}
    			}else{
    				foreach($data['result']['week'] as $k=>$v){
    					$html .= '<li class="sl-op-op">
		              <div class="opop-right">
		                <p class="op-r-h"><a href="/'.$wgWikiname.'/'.$v['page_title'].'">'.$v['page_title'].'</a></p>
		                <p class="op-r-s"><a href="/home/用户:'.$v['last_edit_user'].'">'.$v['last_edit_user'].'</a></p>'
    					.'<span class="hot-num">'.$v['value'].'</span>
		              </div>
		            </li>';
    				}
    			}
    		}
          $html .='</ul>
        </li>';
        if($type!=5){
        $html .='
        <li class="sl-option">
          <ul class="sl-op-inul">';
          if(empty($data['result']['month'])){
          	$html .='<li>暂无数据</li>';
          }else{
    		if($type<=3){
    			foreach($data['result']['month'] as $k=>$v){
    				$html .= '<li class="sl-op-op">
		              <div class="opop-left">'
    						.'<a class="opop-l-link" href="'.$wgUserCenterUrl.$v['pid'].'"><img class="opop-item-img" src="'.$v['pic'].'" alt="'.$v['nick'].'"></a>
		              </div>
		              <div class="opop-right">
		                <p class="op-r-h"><a href="'.$wgUserCenterUrl.$v['pid'].'">'.$v['nick'].'</a></p>
		                <p class="op-r-s op-r-'.$rankList[$type]['class'].' ">'.$rankList[$type]['des'].'：<span>'.$v['value'].'</span></p>'
    				    .'<span class="list-num00 list-num'.($k+1).'"></span>
		              </div>
		            </li>';
    			}
    		}else{
    			foreach($data['result']['month'] as $k=>$v){
    				$html .= '<li class="sl-op-op">
		              <div class="opop-right">
		                <p class="op-r-h"><a href="/'.$wgWikiname.'/'.$v['page_title'].'">'.$v['page_title'].'</a></p>
		                <p class="op-r-s"><a href="/home/用户:'.$v['last_edit_user'].'">'.$v['last_edit_user'].'</a></p>'
    					.'<span class="hot-num">'.$v['value'].'</span>
		              </div>
		            </li>';
    			}
    		}
          }
          $html .='</ul>
          	</li>';
        }
        if($type<=3){
        $html .='
        <li class="sl-option">
            <ul class="sl-op-inul">';
	          if(empty($data['result']['all'])){
	          	$html .='<li>暂无数据</li>';
	          }else{
	    		foreach($data['result']['all'] as $k=>$v){
	            $html .= '<li class="sl-op-op">
	              <div class="opop-left">'
	            	.'<a class="opop-l-link" href="'.$wgUserCenterUrl.$v['pid'].'"><img class="opop-item-img" src="'.$v['pic'].'" alt="'.$v['nick'].'"></a>
	              </div>
	              <div class="opop-right">
	                <p class="op-r-h"><a href="'.$wgUserCenterUrl.$v['pid'].'">'.$v['nick'].'</a></p>
	                <p class="op-r-s op-r-'.$rankList[$type]['class'].' ">'.$rankList[$type]['des'].'：<span>'.$v['value'].'</span></p>'
	                .'<span class="list-num00 list-num'.($k+1).'"></span>
	              </div>
	            </li>';
	    		}
	          }
          $html .='</ul>
        </li>';
        }
      $html.='</ul>
    </div>';
          
    return $html;
}


$wgResourceModules['ext.joymescript.rank.css'] = array(
		'styles' => 'rank.css',
		'position' => 'top',
		'localBasePath' => __DIR__ . '/modules/rank',
		'remoteExtPath' => 'JoymePlugin/modules/rank',
);

$wgResourceModules['ext.joymescript.rank.js'] = array(
		'scripts' => 'rank.js',
		'position' => 'bottom',
		'localBasePath' => __DIR__ . '/modules/rank',
		'remoteExtPath' => 'JoymePlugin/modules/rank',
);


