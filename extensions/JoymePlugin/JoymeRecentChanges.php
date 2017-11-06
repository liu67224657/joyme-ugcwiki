<?php
/**
 * Created by PhpStorm.
 * User: xinshi
 * Date: 2016/7/27
 * Time: 12:15
 */
function JoymeRecentChanges() {

    global $wgParser;
    $wgParser->setHook( "JoymeRecentChanges", "JoymeRecentChangesList" );
}

function JoymeRecentChangesList( $input,$argv ){

    global $wgWikiname;

    $limit = !empty($argv['limit'])?intval($argv['limit']):8;
    $type = '';
    if(key_exists('style',$argv)){
        $type = $argv['style'];
    }

    $data = JoymeRecentChanges::getRecentChanges();

    $recent_html = '';

    if($data->numRows()){
        if(key_exists('stopwords',$argv)){
            $stopwordsarr = explode('|', $argv['stopwords']);
        }
        $i=0;
        foreach($data as $k=>$v){
            if(isset($stopwordsarr)){
                $isstop = false;
                foreach($stopwordsarr as $val){
                    if(!empty($val)){
                        if(preg_match("|$val|is", $v->rc_title)){
                            $isstop = true;
                            break;
                        }
                    }
                }
                if($isstop){
                    continue;
                }
            }
            $i++;
            if($i>$limit){
                break;
            }
            if($v->rc_new == 1){
                $rec_new = '<div class="li-line1"><span class="update newadd">新增</span>';
            }else{
                $rec_new = '<div class="li-line1"><span class="update">更新</span>';
            }
            $recent_html.='<li class="fn-clear">'.$rec_new.'<a target="_blank" href="/'.$wgWikiname.'/'.$v->rc_title.'">'.$v->rc_title.'</a></div><div class="li-line2">贡献者：<a class="contributor" target="_blank" href=""></a><em>'.$v->time.'</em></div></li>';
        }
    }



    return ' <div class="wiki-update" style="'.$type.'">
                <h2 class="user-title"><cite></cite>最新更新 / 新增</h2>
                <ul class="update-content">'.$recent_html.'</ul>
            </div><div class="fn-clear"></div>';
}

$wgExtensionFunctions[] = "JoymeRecentChanges";


class JoymeRecentChanges{

    static function getRecentChanges(){

        $dbr = wfGetDB( DB_MASTER );
        $res = $dbr->query("SELECT DATE_FORMAT(rc_timestamp,'%m-%d') as time,rc_title,rc_new FROM recentchanges WHERE rc_log_action !='delete' AND rc_id  IN (SELECT MAX(rc_id) FROM recentchanges GROUP BY rc_cur_id) AND rc_namespace = 0 ORDER BY rc_id desc LIMIT 60");
        $dbr->commit( __METHOD__ );
        return $res;
    }
}
