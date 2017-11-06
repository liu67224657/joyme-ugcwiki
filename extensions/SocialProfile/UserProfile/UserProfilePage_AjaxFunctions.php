<?php

$wgAjaxExportList[] = 'wfUserManageWikis';
$wgAjaxExportList[] = 'wfUserContributeWikis';
$wgAjaxExportList[] = 'wfUserFollowWikis';
$wgAjaxExportList[] = 'wfUserActivitys';
$wgAjaxExportList[] = 'wfFriendActivitys';


//管理的wiki
function wfUserManageWikis($page,$user_id)
{
    global $wgUser;

    // This feature is only available for logged-in users.
    if (!$wgUser->isLoggedIn()) {
        $out = JoymeWikiUser::getJson(array(
            'html' => '您尚未登录哦'
        ), '0');
        return $out;
    }
    if(empty($page)||empty($user_id)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    $wikiuser = $joymewikiuser->getWikiUser($user_id);
    if($wikiuser->user_name){
        $userprofile = new UserProfile($wikiuser->user_name);
        $managewikis = $userprofile->getUserWikis($user_id, 1, 3, $page);
        if ($managewikis) {
            $restcount = 0;
            $userManagewikicount = $userprofile->getUserWikisCount($user_id,1);
            if($userManagewikicount){
                $restcount = ($userManagewikicount - ($page * 3)) <= 0? 0 : 1 ;
            }
            $rs = $userprofile->displayManageWikis($managewikis);
            $out = JoymeWikiUser::getJson(array(
                'html' => $rs,
                'restcount' =>  $restcount
            ), ($page + 1));
        } else {
            $out = JoymeWikiUser::getJson(array(
                'html' => '没有更多了'
            ), '0');
        }
    }else{
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
    }

    //TODO: use wfMessage instead of hard code
    return $out;
}

//贡献的wiki
function wfUserContributeWikis($page,$user_id)
{
    global $wgUser;

    // This feature is only available for logged-in users.
    if (!$wgUser->isLoggedIn()) {
        $out = JoymeWikiUser::getJson(array(
            'html' => '您尚未登录哦'
        ), '0');
        return $out;
    }

    if(empty($page)||empty($user_id)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    $wikiuser = $joymewikiuser->getWikiUser($user_id);
    if($wikiuser->user_name){
        $userprofile = new UserProfile($wikiuser->user_name);
        $contributewikis = $userprofile->getUserWikis($user_id, 2, 3, $page);
        if ($contributewikis) {
            $restcount = 0;
            $userContributewikicount = $userprofile->getUserWikisCount($user_id,1);
            if($userContributewikicount){
                $restcount = ($userContributewikicount - ($page * 3)) <= 0? 0 : 1 ;
            }
            $rs = $userprofile->displayContributeWikis($contributewikis);
            $out = JoymeWikiUser::getJson(array(
                'html' => $rs,
                'restcount' =>  $restcount
            ), ($page + 1));
        } else {
            $out = JoymeWikiUser::getJson(array(
                'html' => '没有更多了'
            ), '0');
        }
    }else{
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
    }

    //TODO: use wfMessage instead of hard code
    return $out;
}

//关注的wiki
function wfUserFollowWikis($page,$user_id)
{
    global $wgUser;

    // This feature is only available for logged-in users.
    if (!$wgUser->isLoggedIn()) {
        $out = JoymeWikiUser::getJson(array(
            'html' => '您尚未登录哦'
        ), '0');
        return $out;
    }

    if(empty($page)||empty($user_id)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    $wikiuser = $joymewikiuser->getWikiUser($user_id);
    if($wikiuser->user_name){
        $userprofile = new UserProfile($wikiuser->user_name);
        $followwikis = $userprofile->getUserWikis($user_id, 3, 3, $page);
        if ($followwikis) {
            $restcount = 0;
            $userFollowwikicount = $userprofile->getUserWikisCount($user_id,1);
            if($userFollowwikicount){
                $restcount = ($userFollowwikicount - ($page * 3)) <= 0? 0 : 1 ;
            }
            $rs = $userprofile->displayFollowWikis($followwikis);
            $out = JoymeWikiUser::getJson(array(
                'html' => $rs,
                'restcount' =>  $restcount
            ), ($page + 1));
        } else {
            $out = JoymeWikiUser::getJson(array(
                'html' => '没有更多了'
            ), '0');
        }
    }else{
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
    }

    //TODO: use wfMessage instead of hard code
    return $out;
}

function wfUserActivitys($page,$user_id)
{
    global $wgUser;
    // This feature is only available for logged-in users.
    if (!$wgUser->isLoggedIn()) {
        $out = JoymeWikiUser::getJson(array(
            'html' => '您尚未登录哦'
        ), '0');
        return $out;
    }
    if(empty($page)||empty($user_id)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
        return $out;
    }

    $joymewikiuser = new JoymeWikiUser();
    $activitys = $joymewikiuser->getUserActionLog($user_id,10,$page);

    if ($activitys) {
        $wikiuser = $joymewikiuser->getWikiUser($user_id);
        if($wikiuser->user_name){
            $userprofile = new UserProfile($wgUser->getName());
            $restcount = 0;
            $useractionlogcount = $joymewikiuser->getUserActionLogCount($user_id);
            if($useractionlogcount){
                $restcount = ($useractionlogcount - ($page * 10)) <= 0? 0 : 1 ;
            }
            $rs = $userprofile->displayUserActivitys($activitys);
            $out = JoymeWikiUser::getJson(array(
                'html' => $rs,
                'restcount' =>  $restcount
            ), ($page + 1));
        }else{
            $out = JoymeWikiUser::getJson(array(
                'html' => '参数错误'
            ), '0');
        }
    } else {
        $out = JoymeWikiUser::getJson(array(
            'html' => '没有更多了'
        ), '0');
    }
    //TODO: use wfMessage instead of hard code
    return $out;
}

function wfFriendActivitys($page,$user_id)
{
    global $wgUser;

    // This feature is only available for logged-in users.
    if (!$wgUser->isLoggedIn()) {
        $out = JoymeWikiUser::getJson(array(
            'html' => '您尚未登录哦'
        ), '0');
        return $out;
    }
    if(empty($page)||empty($user_id)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '参数错误'
        ), '0');
        return $out;
    }

    $uuf = new UserUserFollow();
    $follows = $uuf->getFollowList($user_id, 1);
    if(empty($follows)){
        $out = JoymeWikiUser::getJson(array(
            'html' => '没有关注用户'
        ), '0');
    }else{
        $friendids = array_column($follows,'user_id');
        $joymewikiuser = new JoymeWikiUser();
        $userprofiles = $joymewikiuser->getProfile($friendids);
        if($userprofiles){
            $usericons = array_column($userprofiles,'icon','uid');
            $usernicks = array_column($userprofiles,'nick','uid');
            $friendsactivitys = $joymewikiuser->getUserActionLog($friendids,10,$page);
            if ($friendsactivitys) {
                $userprofile = new UserProfile($wgUser->getName());
                $rs = $userprofile->displayFriendActivitys($friendsactivitys,$usericons,$usernicks);
                $restcount = 0;
                $factionlogcount = $joymewikiuser->getUserActionLogCount($friendids);
                if($factionlogcount){
                    $restcount = ($factionlogcount - ($page * 10)) <= 0? 0 : 1 ;
                }
                $out = JoymeWikiUser::getJson(array(
                    'html' => $rs,
                    'restcount' =>  $restcount
                ), ($page + 1));
            } else {
                $out = JoymeWikiUser::getJson(array(
                    'html' => '没有更多了'
                ), '0');
            }
        }else{
            $out = JoymeWikiUser::getJson(array(
                'html' => '获取用户信息出错'
            ), '0');
        }
    }

    //TODO: use wfMessage instead of hard code
    return $out;
}