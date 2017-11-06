<?php
/**
 * Description:wiki活跃用户/wiki管理组
 * Author: gradydong
 * Date: 2016/6/24
 * Time: 10:28
 * Copyright: Joyme.com
 */
function JoymeActiveManages() {

    global $wgParser;
    $wgParser->setHook( "JoymeActiveManages", "ActiveManages" );
}

function ActiveManages($input,$argv) {

    $html = '<div style="width: '.$width.'px;height: '.$height.'px;">
                <h1>WIKI活跃用户</h1>
                <ul>
                    <li> </li>
                </ul>
                <h1>wiki管理组</h1>
                <ul>
                    <li> </li>
                </ul>
            </div>';

    if(!empty($argv['label'])){
        $Label = $argv['label'];
        $html = "<$Label class='wiki_Recently_data'><ul class='wab_list'>";
    }else{
        $html = "<ul class='wab_list'>";
    }
    //获取条数
    $dbr = wfGetDB( DB_SLAVE );

    $res = $dbr->select(
        'user_site_addition',
        array(),
        array()
    );

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

$wgExtensionFunctions[] = "JoymeActiveManages";