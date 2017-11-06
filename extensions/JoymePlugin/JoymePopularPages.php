<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-1-113
 * Time: 下午17:15
 * To change this template use File | Settings | File Templates.
 */
function JoymePopularPages(){
    global $wgParser;
    $wgParser->setHook( "JoymePopularPages", "showPopularPages" );
}

function showPopularPages($input, $argv){
    $dbr = wfGetDB( DB_MASTER );

    if(!empty($argv['limit']) && preg_match("/^\d*$/",$argv['limit'])){
        $num = $argv['limit'];
        $res = $dbr->query("SELECT  page_namespace AS namespace,page_namespace AS namespace,page_is_new AS is_new,page_title AS title,page_counter AS VALUE FROM `page`
  WHERE page_is_redirect = '0' AND page_namespace = '0'  ORDER BY VALUE DESC LIMIT $num");
    }else{
        $res = $dbr->query("SELECT  page_namespace AS namespace,page_namespace AS namespace,page_is_new AS is_new,page_title AS title,page_counter AS VALUE FROM `page`
  WHERE page_is_redirect = '0' AND page_namespace = '0'  ORDER BY VALUE DESC LIMIT 6");
    }

    $dbr->commit( __METHOD__ );
    $result = array();

    if(!empty($argv['Label'])){
        $Label = $argv['Label'];
        $html = "<$Label class='wiki_Recently_data'><ul class='wab_list'>";
    }else{
        $html = "<ul class='wab_list'>";
    }

    foreach($res as $v){
        if(!preg_match("|首页|is",$v->title) && !preg_match("|模板|is",$v->title) && !preg_match("|测试|is",$v->title)){
            $html.="<li><a href=/wiki/".$v->title.">$v->title</a></li>";
        }
    }
    if(!empty($argv['Label'])){
        $Label = $argv['Label'];
        $html.= "</ul></$Label>";
    }else{
        $html.= "</ul>";
    }
    return $html;
}
$wgExtensionFunctions[] = "JoymePopularPages";

?>