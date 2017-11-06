<?php
/**
 * Description:
 * Author: gradydong
 * Date: 2016/6/27
 * Time: 16:45
 * Copyright: Joyme.com
 */
$wgAjaxExportList[] = 'wfUserCenterUserLogin';
$wgAjaxExportList[] = 'wfUserCenterUserRegister';
$wgAjaxExportList[] = 'wfUserLSMSiteVerifyRegSendCode';
$wgAjaxExportList[] = 'wfUserLSMSiteVerifySendCode';
$wgAjaxExportList[] = 'wfUserRegSendCode';
$wgAjaxExportList[] = 'wfUserCenterUserSendCode';
$wgAjaxExportList[] = 'wfUserCenterUserRecoverPassword';
$wgAjaxExportList[] = 'wfUserModifyPassword';
$wgAjaxExportList[] = 'wfUserSiteFollow';
$wgAjaxExportList[] = 'wfUserCancelSiteFollow';
$wgAjaxExportList[] = 'wfUserUpdateRemindSet';
$wgAjaxExportList[] = 'wfUserBindMobile';
$wgAjaxExportList[] = 'wfUserUnBindThirdAccount';
$wgAjaxExportList[] = 'wfUserUploadIcon';
$wgAjaxExportList[] = 'wfUserModifyMobile';
$wgAjaxExportList[] = 'wfUserModifyInfo';
$wgAjaxExportList[] = 'wfSiteFollowStatus';

//用户登录接口
function wfUserCenterUserLogin( $account, $password , $keeptime ) {

    $data = array(
//        'mobile' => $mobile,
        'account' => trim($account),
        'password' => trim($password),
        'keeptime' => $keeptime
    );
    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->login($data);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('登录成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}

//用户注册接口
function wfUserCenterUserRegister( $mobile, $mobilecode,$password , $repassword, $nick ) {
    $data = array(
        'mobile' => trim($mobile),
        'password' => trim($password),
        'repassword' => trim($repassword),
        'mobilecode' => trim($mobilecode),
        'nick' => urldecode($nick)
    );
    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->register($data);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('注册成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}

//注册发送短信验证码
function wfUserLSMSiteVerifyRegSendCode( $mobile ,$lsmresponse) {
    if(empty($mobile)){
        $out = JoymeWikiUser::getJson('手机号不能为空','0');
        return $out;
    }

    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->sendRegMobileCode($mobile ,$lsmresponse);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('发送成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}


//发送短信验证码
function wfUserLSMSiteVerifySendCode( $mobile , $lsmresponse ) {
    global $wgUser;
    $joymewikiuser = new JoymeWikiUser();
    if($mobile != 'isempty'){
        $mobile = stripslashes( $mobile );
    }else{
        $joymewikiuser->getProfile($wgUser->getId());
        if(empty($joymewikiuser->mobile)){
            $out = JoymeWikiUser::getJson('手机号不能为空哦','0');
            return $out;
        }else{
            $mobile = $joymewikiuser->mobile;
        }
    }
    $ret = $joymewikiuser->sendMobileMsg($mobile,$lsmresponse);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('发送成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }

}
/*

//注册发送短信验证码
function wfUserRegSendCode( $mobile ) {
    if(empty($mobile)){
        $out = JoymeWikiUser::getJson('手机号不能为空','0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->sendRegMobileCode($mobile);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('发送成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}

//发送短信验证码
function wfUserCenterUserSendCode( $mobile ) {
    global $wgUser;
    $joymewikiuser = new JoymeWikiUser();
    if($mobile != 'isempty'){
        $mobile = stripslashes( $mobile );
    }else{
        $joymewikiuser->getProfile($wgUser->getId());
        if(empty($joymewikiuser->mobile)){
            $out = JoymeWikiUser::getJson('手机号不能为空哦','0');
            return $out;
        }else{
            $mobile = $joymewikiuser->mobile;
        }
    }
    $ret = $joymewikiuser->sendMobileMsg($mobile);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('发送成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}*/

//重置密码
function wfUserCenterUserRecoverPassword( $mobile , $pwd , $repeatpwd ,$mobilecode  ){
    $joymewikiuser = new JoymeWikiUser();
    if($mobile == 'modifypassword'){
        global $wgUser;
        $joymewikiuser->getProfile($wgUser->getId());
        if(empty($joymewikiuser->mobile)){
            $out = JoymeWikiUser::getJson('参数错误','0');
            return $out;
        }else{
            $mobile = $joymewikiuser->mobile;
        }
    }

    $ret = $joymewikiuser->recoverPassword($mobile , $pwd , $repeatpwd ,$mobilecode);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('修改成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}


//绑定手机号
function wfUserBindMobile( $mobile,$password, $repassword,$mobilecode){
    global $wgUser;

    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->bindMobile($wgUser->getId(), $mobile, $password , $repassword , $mobilecode);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('绑定成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }
}

//修改密码
function wfUserModifyPassword( $oldpwd,$pwd, $repeatpwd){
    global $wgUser,$wgLoginDomain;

    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
//    $ret = $joymewikiuser->modifyPassword($wgUser->getId(), $oldpwd , $pwd, $repeatpwd, $wgLoginDomain);
    $ret = $joymewikiuser->modifyPassword($wgUser->getId(), $oldpwd , $pwd, $repeatpwd);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('修改成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson($ret,'0');
        return $out;
    }

}



//用户站点关注
function wfUserSiteFollow(){

    global $wgUser,$wgSiteId,$wgWikiname,$wgSiteRealName,$wgServer;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    if(empty($wgSiteId)){
        $out = JoymeWikiUser::getJson('站点信息出错','0');
        return $out;
    }
    $userid = $wgUser->getId();
    $ret = JoymeWikiUser::addUserSiteFollow($userid);
    if($ret){
        /*JoymeWikiUser::addActionLog(
            $wgUser->getId(),
            2,
            '关注了<a href="/'.$wgWikiname.'/首页" target="_blank">'.$wgSiteRealName.'</a>'
        );*/
        JoymeWikiUser::adduseractivity(
            $userid,
            'focus_wiki',
            '关注了 <a href="'.$wgServer.'/'.$wgWikiname.'/首页" target="_blank">'.$wgSiteRealName.'</a>'
        );
        JoymeWikiUser::pointsreport(33,$userid);
        $out = JoymeWikiUser::getJson('关注成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson('关注失败','0');
        return $out;
    }
}
//用户取消站点关注
function wfUserCancelSiteFollow(  ){
    global $wgUser,$wgSiteId;

    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    if(empty($wgSiteId)){
        $out = JoymeWikiUser::getJson('站点信息出错','0');
        return $out;
    }
    $res =  JoymeWikiUser::checkUserFollowSite($wgUser->getId(),$wgSiteId);
    if($res){
        $ret = JoymeWikiUser::deleteUserSiteRelation($wgUser->getId(),$wgSiteId);
        if($ret){
            $out = JoymeWikiUser::getJson('取消关注成功','1');
            return $out;
        }else{
            $out = JoymeWikiUser::getJson('取消关注失败','0');
            return $out;
        }
    }else{
        $out = JoymeWikiUser::getJson('没有关注信息','0');
        return $out;
    }
}

//修改隐私提醒
function wfUserUpdateRemindSet($up_property,$up_value){
    global $wgUser;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }

    $propertys = array(
        'a_remind' => 'echo-subscriptions-web-article-cite-my',
        'comment_remind' => 'echo-subscriptions-web-article-comments',
        'like_remind' => 'echo-subscriptions-web-article-thumb-up',
        'attention_remind' => 'echo-subscriptions-web-article-consider-me',
        'sysmsg_remind' => 'echo-subscriptions-web-echo-system-message',
    );
    $property_keys = array_keys($propertys);

    $joymewikiuser = new JoymeWikiUser();

    if($up_property=='is_attention'){
        $ret = $joymewikiuser->editUserAddition(array(
            'user_id' => $wgUser->getId(),
            'is_attention' => (int)$up_value
        ));
    }
    elseif($up_property=='is_secretchat'){
        $ret = $joymewikiuser->editUserAddition(array(
            'user_id' => $wgUser->getId(),
            'is_secretchat' => (int)$up_value
        ));
    }
    elseif (in_array($up_property,$property_keys)){
        if($up_value==1){
            $ret = $joymewikiuser->delUserRemindSet($wgUser->getId(),$propertys[$up_property]);
        }else{
            $ret = 	$joymewikiuser->userRemindSet($wgUser->getId(),$propertys[$up_property]);
        }
    }
    else{
        $out = JoymeWikiUser::getJson('参数错误','0');
        return $out;
    }
    if($ret){
        $out = JoymeWikiUser::getJson('修改成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson('修改失败','0');
        return $out;
    }

}


function wfUserUnBindThirdAccount($type){
    global $wgUser,$wgJoymeUserInfo;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    $user_id = $wgJoymeUserInfo['uid'];
    $uno = $wgJoymeUserInfo['uno'];
    if($user_id){
        if(!empty($type)){
            $joymewikiuser = new JoymeWikiUser();
            $ret = $joymewikiuser->unbindThirdPartyAccount($type,$uno);
            if($ret == 1){
                $out = JoymeWikiUser::getJson('解绑成功','1');
                return $out;
            }else{
                $out = JoymeWikiUser::getJson($ret,'0');
                return $out;
            }
        }else{
            $out = JoymeWikiUser::getJson('参数错误','0');
            return $out;
        }
    }else{
        $out = JoymeWikiUser::getJson('参数错误','0');
        return $out;
    }
}
//上传头像
function wfUserUploadIcon($icon){
    global $wgUser;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }

    $joymewikiuser = new JoymeWikiUser();
    $ret = $joymewikiuser->modifyIcon($wgUser->getId(),$icon);
    if($ret == 1){
        $out = JoymeWikiUser::getJson('图片上传成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson('修改头像失败','0');
        return $out;
    }
}

//修改手机号
function wfUserModifyMobile( $mobile , $code ,$step){
    global $wgUser;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    if($code){
        $joymewikiuser = new JoymeWikiUser();
        $joymewikiuser->getProfile($wgUser->getId());
        if($joymewikiuser->mobile){
            if($step == '1' && $mobile == 'isempty'){
                $ret = $joymewikiuser->verifyMobile($joymewikiuser->mobile,$code);
                if($ret == 1){
                    $out = JoymeWikiUser::getJson(md5($joymewikiuser->mobile.$wgUser->getId()),'1');
                    return $out;
                }else{
                    $out = JoymeWikiUser::getJson($ret,'0');
                    return $out;
                }
            }
            elseif ($step == '2' && $mobile){
                $ret = $joymewikiuser->verifyMobile($mobile,$code);
                if($ret == 1){
                    $ret = $joymewikiuser->modifyMobile($wgUser->getId(),$mobile,$joymewikiuser->mobile);
                    if($ret == 1){
                        $out = JoymeWikiUser::getJson(md5($mobile.$wgUser->getId()),'1');
                        return $out;
                    }else{
                        $out = JoymeWikiUser::getJson($ret,'0');
                        return $out;
                    }
                }else{
                    $out = JoymeWikiUser::getJson($ret,'0');
                    return $out;
                }
            }
            else{
                $out = JoymeWikiUser::getJson('参数错误','0');
                return $out;
            }
        }else{
            $out = JoymeWikiUser::getJson('参数错误','0');
            return $out;
        }

    }else{
        $out = JoymeWikiUser::getJson('参数错误','0');
        return $out;
    }

}

//修改用户信息
function wfUserModifyInfo($sex,$proviceid,$brief,$birthday,$interest){
    global $wgUser;
    // This feature is only available for logged-in users.
    if ( !$wgUser->isLoggedIn() ) {
        $out = JoymeWikiUser::getJson('您尚未登录哦','0');
        return $out;
    }
    $user_id = $wgUser->getId();
    if(mb_strlen($brief,"UTF8")>16){
        $out = JoymeWikiUser::getJson('参数错误','0');
        return $out;
    }
    $joymewikiuser = new JoymeWikiUser();
    if(is_numeric($sex)||is_numeric($proviceid)){
        if(is_numeric($sex)){
            $sex = (int)$sex;
        }
        $maininfo = $joymewikiuser->modifyInfo($user_id,$sex,$proviceid);
        if($maininfo != 1){
            $out = JoymeWikiUser::getJson('保存失败','0');
            return $out;
        }
        if(empty($brief) && empty($birthday) && empty($interest)){
            $out = JoymeWikiUser::getJson('保存成功','1');
            return $out;
        }
    }
    $data = array(
        'user_id' => $user_id
    );
    $data['brief'] = $brief;
    $data['birthday'] = $birthday;
    $data['interest'] = $interest;

    $ret = $joymewikiuser->editUserAddition($data);
    if($ret){
        $out = JoymeWikiUser::getJson('保存成功','1');
        return $out;
    }else{
        $out = JoymeWikiUser::getJson('保存失败','0');
        return $out;
    }
}

function wfSiteFollowStatus(){

    global $wgUser,$wgSiteId;
    $ret = JoymeWikiUser::getUserFollowSite($wgUser->getId(),$wgSiteId);
    $site_info = JoymeSite::getSiteInfo($wgSiteId);
    if($site_info){
        $site_page_count = $site_info[1]['page_count'];
        $site_edit_count = $site_info[1]['edit_count'];
        $site_follow_usercount = $site_info[1]['follow_usercount'];
    }else{
        $site_page_count = 0;
        $site_edit_count = 0;
        $site_follow_usercount = 0;
    }
    if(isset($ret->status)){
        $out = JoymeWikiUser::getJson($site_page_count.'|'.$site_follow_usercount.'|'.$site_edit_count,$ret->status);
    }else{
        $out = JoymeWikiUser::getJson($site_page_count.'|'.$site_follow_usercount.'|'.$site_edit_count,0);
    }
    return $out;
}