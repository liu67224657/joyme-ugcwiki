<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-1-113
 * Time: 下午2:47
 * To change this template use File | Settings | File Templates.
 */
function JoymeChangeInfo() {

    global $wgParser;
    $wgParser->setHook( "JoymeChangeInfo", "ChangeInfo" );
}

function ChangeInfo($input,$argv) {

    $num = !empty($argv['limit'])?intval($argv['limit']):6;

    if(!empty($argv['label'])){
        $Label = $argv['label'];
        $html = "<$Label class='wiki_Recently_data'><ul class='wab_list'>";
    }else{
        $html = "<ul class='wab_list'>";
    }
    //获取条数
    $dbr = wfGetDB( DB_MASTER );

    $res = $dbr->query("SELECT rc_timestamp,rc_title FROM recentchanges WHERE rc_id  IN (SELECT MAX(rc_id) FROM recentchanges GROUP BY rc_cur_id) AND rc_namespace = 0 ORDER BY rc_id desc LIMIT 30");

    $dbr->commit( __METHOD__ );

    $date = new Language();
	if(isset($argv['stopwords'])){
		$stopwordsarr = explode('|', $argv['stopwords']);
	}
    $i=0;
    foreach($res as $k=>$v){

        if(isset($stopwordsarr)){
            $isstop = false;
            foreach($stopwordsarr as $val){
                if(preg_match("|$val|is", $v->rc_title)){
                    $isstop = true;
                    break;
                }
            }
            if($isstop){
                continue;
            }
        }
        $i++;
        if($i>$num){
            break;
        }
        $acttime = $date->userAdjust($v->rc_timestamp);
        $time = str_split(substr($acttime,0,12),2);
        $html.="<li><span><a href=/wiki/".$v->rc_title.">".$v->rc_title."</a>";
        $html.="<cite>$time[0]$time[1]/$time[2]/$time[3]</cite></span></li>";
    }

    if(!empty($argv['label'])){
        $Label = $argv['label'];
        $html.= "</ul></$Label>";
    }else{
        $html.= "</ul>";
    }
    return $html;
}

$wgExtensionFunctions[] = "JoymeChangeInfo";
