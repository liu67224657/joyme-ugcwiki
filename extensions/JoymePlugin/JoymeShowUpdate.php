<?php
/**
 * Description: 最近更新/新增
 * Author: gradydong
 * Date: 2016/6/24
 * Time: 10:25
 * Copyright: Joyme.com
 */
function JoymeShowUpdate() {

    global $wgParser;
    $wgParser->setHook( "JoymeShowUpdate", "ShowUpdate" );
}

function ShowUpdate($input,$argv) {

    $width = !empty($argv['width'])?intval($argv['width']):80;
    $height = !empty($argv['height'])?intval($argv['height']):80;

    $html = '<div style="width: '.$width.'px;height: '.$height.'px;">
                <h1>最近更新/新增</h1>
                <ul>';

    //获取条数
    $dbr = wfGetDB( DB_SLAVE );

    $res = $dbr->query("SELECT rc_timestamp,rc_title FROM recentchanges WHERE rc_id  IN (SELECT MAX(rc_id) FROM recentchanges GROUP BY rc_cur_id) AND rc_namespace = 0 ORDER BY rc_id desc LIMIT 10");

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
        $acttime = $date->userAdjust($v->rc_timestamp);
        $time = str_split(substr($acttime,0,12),2);
        $html.="<li><span><a href=/wiki/".$v->rc_title.">".$v->rc_title."</a>";
        $html.="<cite>$time[0]$time[1]/$time[2]/$time[3]</cite></span></li>";
    }


    $html.= "</ul>
            </div>";
    return $html;
}

$wgExtensionFunctions[] = "JoymeShowUpdate";