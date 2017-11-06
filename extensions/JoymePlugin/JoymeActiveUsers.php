<?php
/**
 * Created by PhpStorm.
 * User: xinshi
 * Date: 2016/7/27
 * Time: 10:24
 */
function JoymeActiveUsers() {

    global $wgParser;
    $wgParser->setHook( "JoymeActiveUsers", "JoymeActiveUsersLsit" );
}

function JoymeActiveUsersLsit( $input,$argv ){

    //默认4条活跃用户
    $user_limit = 4;
    $site_limt = 4;
    $type = '';
    $users = array();

    if(key_exists('userlimit',$argv)){
        $user_limit = intval($argv['userlimit'])<=0?4:intval($argv['userlimit']);
        $user_limit = $user_limit>=30?30:$user_limit;
    }
    if(key_exists('sitelimit',$argv)){
        $site_limt = intval($argv['sitelimit'])<=0?4:intval($argv['sitelimit']);
        $site_limt = $site_limt>=30?30:$site_limt;
    }

    $userstop = array();
    if(key_exists('userstop',$argv)){
        $userstop = explode('|', $argv['userstop']);
    }

    $sitestop = array();
    if(key_exists('sitestop',$argv)){
        $sitestop = explode('|', $argv['sitestop']);
    }

    if(key_exists('style',$argv)){
        $type = $argv['style'];
    }
    
    $user_data = JoymeActiveUsers::GetActiveUser($user_limit);
    $model = new JoymeWikiUser();

    $user_html = '';
    $uflag = false;
    $sflag = false;
    
    if($user_data->numRows()){
        $ui=0;
        foreach($user_data as $uk=>$uv){
            $users[] = $uv->user_id;
        }
        if($users){
            $res = $model->getProfile( $users );
            if($res){
                foreach($res as $uik=>$uiv){
                    if(isset($userstop)){
                        $isstop = false;
                        foreach($userstop as $val){
                            if(!empty($val)){
                                if(preg_match("|$val|is", @$uiv['nick'])){
                                    $isstop = true;
                                    break;
                                }
                            }
                        }
                        if($isstop){
                            continue;
                        }
                    }
                    if(isset( $uiv['nick'] )){
                        $ui++;
                        if($ui>$user_limit){
                            break;
                        }
                        $uflag = true;
                        $user_html .='<li><a target="_blank" href="'.getUserHomeLink( $uiv['profileid'] ).'"><div class="userheader-box userinfo" data-username="'.$uiv['profileid'].'"><img src="'.$uiv['icon'].'" title="用户信息" width="45px" height="45px"/><span class="'.(empty($uiv['headskin'])?'':'daoju daoju'.$uiv['headskin']).'"></span>'.($uiv['vtype']>0?'<span class="user-vip" title="'.$uiv['vdesc'].'"></span>':'').'</div><span class="userinfo" data-username="'.$uiv['profileid'].'">'.$uiv['nick'].'</span></a></li>';
                    }
                }
            }
        }
    }

    $site_data = JoymeActiveUsers::GetSiteadministrator($site_limt);

    $site_html = '';
    $users = array();
    if($site_data->numRows()){
        $si = 0;
        foreach($site_data as $sk=>$svv){
            $users[] = $svv->user_id;
        }
        if($users){
            $res = $model->getProfile( $users );
            if($res){
                foreach($res as $sik=>$siv){
                    if(isset($sitestop)){
                        $sistop = false;
                        foreach($sitestop as $val){
                            if(!empty($val)){
                                if(preg_match("|$val|is", @$siv['nick'])){
                                    $sistop = true;
                                    break;
                                }
                            }
                        }
                        if($sistop){
                            continue;
                        }
                    }
                    if(isset( $siv['nick'] )){
                        $si++;
                        if($si>$site_limt){
                            break;
                        }
                        $sflag = true;
                        $site_html.='<li><a target="_blank" href="'.getUserHomeLink( $siv['profileid'] ).'"><div class="userheader-box userinfo" data-username="'.$siv['profileid'].'"><img src="'.$siv['icon'].'" title="用户信息" width="45px" height="45px"/><span class="'.(empty($siv['headskin'])?'':'daoju daoju'.$siv['headskin']).'"></span>'.($siv['vtype']>0?'<span class="user-vip" title="'.$siv['vdesc'].'"></span>':'').'</div><span class="userinfo" data-username="'.$siv['profileid'].'">'.$siv['nick'].'</span></a></li>';
                    }
                }
            }
        }
    }
    $user = '';
    if($uflag){
        $user = '<div class="socal-user">
                    <div class="user-info">
                        <h2 class="user-title"><cite></cite>WIKI 活跃用户</h2>
                        <ul>'.$user_html.'</ul>
                    </div>
                 </div>
                 <div class="fn-clear"></div>';
    }

    $site = '';
    if($sflag){
        $site = '<div class="socal-user">
                    <div class="user-info">
                        <h2 class="user-title"><cite></cite>WIKI 管理组</h2>
                        <ul>'.$site_html.'</ul>
                    </div>
                 </div>
                 <div class="recent-update"></div>';
    }
    return ' <div class="wiki-info" style="'.$type.'">'.$user.$site.'</div>';
}


function getUserHomeLink( $profileid ){

    global $wgUserCenterUrl;
    return $wgUserCenterUrl.$profileid;

}

$wgExtensionFunctions[] = "JoymeActiveUsers";


class JoymeActiveUsers{

    //获取活跃用户
    static function GetActiveUser($limit){

        global $wgSiteId;
        $dbr = wfGetDB( DB_MASTER );
        return $dbr->select(
            'user_site_addition',
            'user_id,contribution_count',
            array(
                'site_id'=>$wgSiteId
            ),
            __METHOD__,
            array(
                'ORDER BY'=>'contribution_count DESC',
                'LIMIT'=>$limit
            )
        );
    }


    //获取站点管理员
    static function GetSiteadministrator($limit){

        global $wgSiteId;
        $dbr = wfGetDB( DB_MASTER );
        return $dbr->select(
            'user_site_relation',
            'user_id',
            array(
                'site_id'=>$wgSiteId,
                'status'=>1
            ),
            __METHOD__,
            array(
                'LIMIT'=>$limit
            )
        );
    }
}
