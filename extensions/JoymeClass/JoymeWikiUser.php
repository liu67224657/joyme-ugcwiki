<?php

/**
 * Description: wiki用户中心类
 * Author: gradydong
 * Date: 2016/6/15
 * Time: 18:17
 * Copyright: Joyme.com
 */
use Joyme\net\Curl;
use Joyme\core\Log;

class JoymeWikiUser extends User
{
    //主站用户中心请求地址
    public $registerurl;
    public $loginurl;
    public $sendcodeurl;
    public $sendregcodeurl;
    public $logouturl;
    public $bindmobileurl;
    public $bindqqurl;
    public $bindsinaweibourl;
    public $modifypasswordurl;
    public $recoverpasswordurl;
    public $modifymobileurl;
    public $getprofileurl;
    public $getprofilebyuidurl;
    public $cityinfourl;
    public $modifynickurl;
    public $modifyiconurl;
    public $modifyinfourl;
    public $verifymobileurl;
    public $updateuserprivacyurl;


    //用户信息
    public $mobile;
    public $password;
    public $sina;
    public $qq;
    public $bindsinaflag;
    public $bindqqflag;
    public $bindmobileflag;
    public $nick;
    public $sex;
    public $proviceid;
    public $cityid;
    public $address;
    public $interest;
    public $icon;
    public $user_profileid;
    public $total_like_count;
    public $total_comment_count;
    public $brief;
    public $birthday;
    public $is_attention;
    public $is_secretchat;
    public $token;
    public $uno;

    //用户默认头像地址
    public $defaulticonurl;

    //第三方账户
    public $thirdAccounts = array(
        'qq',
        'sinaweibo',
    );

    //第三方账号解绑地址
    public $unbindurldata = array();


    //用户操作记录类型
    public $useractiontypes = array(
        '1' => 'user_create_site',
        '2' => 'user_follow_site',
        '3' => 'user_create_page',
        '4' => 'user_edit_page',
        '5' => 'user_user_follow',
        '6' => 'user_watch_page',
    );


    //用户中心profileflag的使用
    public $profileflags = array(
        'clientid' => 1,
        'sina' => 2,
        'qq' => 3,
        'mobile' => 12,
    );


    public function __construct()
    {
        parent::__construct();
    }

    public function initData()
    {
        global $wgEnv;

//        $this->defaulticonurl = 'http://static.joyme.' . $wgEnv . '/pc/ugcwiki/images/user_icon.jpg';
        $this->defaulticonurl = 'http://lib.joyme.' . $wgEnv . '/static/theme/default/img/head_is_m.jpg';

        $this->registerurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/register';
        $this->loginurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/login';
        $this->sendregcodeurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/mobile/sendcode';
        $this->sendcodeurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/existsmobile/sendcode';
        $this->logouturl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/logout';
        $this->bindmobileurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/bind/mobile';
        $this->bindqqurl = '
http://passport.joyme.' . $wgEnv . '/auth/thirdapi/qq/bind?rl=true';
        $this->bindsinaweibourl = 'http://passport.joyme.' . $wgEnv . '/auth/thirdapi/sinaweibo/bind?rl=true';
        $this->modifypasswordurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/modify/password';
        $this->recoverpasswordurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/recover/password';
        $this->modifymobileurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/modify/mobile';
        $this->getprofileurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/getprofile';
        $this->getprofilebyuidurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/getprofilebyuid';
        $this->cityinfourl = 'http://servapi.joyme.' . $wgEnv . '/servapi/config/cityinfo';
        $this->modifynickurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/profile/modify/nick';
        $this->modifyiconurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/profile/modify/icon';
        $this->modifyinfourl = 'http://servapi.joyme.' . $wgEnv . '/servapi/profile/modify/info';
        $this->verifymobileurl = 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/verify/mobile';
        $this->updateuserprivacyurl = 'http://api.joyme.' . $wgEnv . '/api/privacy/updateuserprivacy';

        $this->unbindurldata = array(
            'qq' => 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/qq/unbind',
            'sinaweibo' => 'http://servapi.joyme.' . $wgEnv . '/servapi/auth/sinaweibo/unbind'
        );


    }

    ####################################################################
    ##########################Java接口调用开始###########################
    ####################################################################


    /**
     * 声望和积分上报
     */
    public static function  pointsreport($actiontype,$rpid,$cpid=null,$num=null)
    {
        global $wgEnv;

        $data = array();
        if(empty($actiontype)){
            return false;
        }else{
            $data["actiontype"] = $actiontype;
        }

        $joymewikiuser = new JoymeWikiUser();
        if(empty($rpid)){
            return false;
        }else{
            $rpid = $joymewikiuser->getProfileid($rpid);
            if($rpid){
                $data["rpid"] = $rpid;
            }
        }

        if($cpid){
            $cpid = $joymewikiuser->getProfileid($cpid);
            if($cpid){
                $data["cpid"] = $cpid;
            }
        }

        if($num){
            $data['num'] = $num;
        }

        $url = 'http://api.joyme.'.$wgEnv.'/joyme/api/point/report';
        $curl = new Curl();
        $res = $curl->Post($url,$data);
        $res = json_decode($res, true);
        if($res['rs'] == '1'){
            return true;
        }
        elseif ($res['rs'] == '-1000'){
            return '系统错误';
        }
        elseif ($res['rs'] == '-10104'){
            return '用户不存在';
        }
        elseif ($res['rs'] == '-2001'){
            return '超过次数限制';
        }
        elseif ($res['rs'] == '-2000'){
            return '积分不足';
        }
        else{
            $res['data'] = $data;
            Log::error($res);
            return false;
        }
    }
    
    /**
     * 积分扣除
     */
    public static function  reducepoint($pid,$point)
    {
    	global $wgEnv;
    
    	if(empty($pid)){
    		return false;
    	}
    	if(empty($point)){
    		return false;
    	}
    	$url = 'http://api.joyme.'.$wgEnv.'/joyme/api/point/reducepoint';
    	$curl = new Curl();
    	$res = $curl->Post($url,array(
    			'pid' => $pid,
    			'desc' => '惩罚',
    			'point' => $point
    	));
    	$res = json_decode($res, true);
    	if($res['rs'] == '1'){
    		return true;
    	}
    	elseif ($res['rs'] == '-1000'){
    		return '系统错误';
    	}
    	elseif ($res['rs'] == '-10104'){
    		return '用户不存在';
    	}
    	elseif ($res['rs'] == '-2001'){
    		return '超过次数限制';
    	}
    	else{
    		return false;
    	}
    }
    
    /**
     * cms上报
     */
    public static function  cmsreport($sid,$title,$time)
    {
    	global $wgEnv;
    	
    	$time = $time+8*3600;
    
    	$url = 'http://article.joyme.'.$wgEnv.'/plus/api.php?a=addCmsForWikipage';
    	$curl = new Curl();
    	$res = $curl->Post($url,array(
    			'sid' => $sid,
    			'title' => $title,
    			'edit_time' => $time
    	));
    	$res = json_decode($res, true);
    	if(empty($res) || $res['code'] != 0){
    		$rs = array('url'=>$url,'sid'=>$sid,'title'=>$title);
    		$rs['data'] = res;
    		Log::error($res);
    		return false;
    	}else{
    		return true;
    	}
    	//var_dump($res);exit;
    }
    
    /**
     * cms上报
     */
    public static function  cmsaddsite($params)
    {
    	global $wgEnv;
    	
    	$url = 'http://article.joyme.'.$wgEnv.'/plus/api.php?a=addCmsSite';
    	$curl = new Curl();
    	$res = $curl->Post($url,$params);
    	$res = json_decode($res, true);
    	if(empty($res) || $res['code'] != 0){
    		$res['params'] = $params;
    		Log::error($res);
    		return false;
    	}else{
    		return true;
    	}
    	//var_dump($res);exit;
    }


    /**
     * 用户激励消息上报
     */
    public static function noticereport($param=array())
    {
        global $wgEnv;
        $url = 'http://api.joyme.'.$wgEnv.'/joyme/api/notice/report';

        //参数
        $data = array();

        $joymewikiuser = new JoymeWikiUser();
        // 接收消息人的pid
        $pid = $joymewikiuser->getProfileid($param['tuid']);
        if (empty($pid)) {
            return false;
        } else {
            $data['pid'] = $pid;
        }
        //操作人的pid
        $destpid = $joymewikiuser->getProfileid($param['uid']);
        if (empty($destpid)) {
            return false;
        } else {
            $data['destpid'] = $destpid;
        }
        //类型
        if(empty($param['ntype'])){
            return false;
        }else{
            $data['ntype'] = $param['ntype'];
        }

        if(!empty($param['url'])){
            $data['url'] = $param['url'];
        }

        if(!empty($param['curl'])){
        	$data['curl'] = $param['curl'];
        }
        
        if(!empty($param['otherpid'])){
            //操作人的pid
            $otherpid = $joymewikiuser->getProfileid($param['otherpid']);
            if (empty($otherpid)) {
            	return false;
            } else {
            	$data['otherpid'] = $otherpid;
            }
        }

        if(!empty($param['desc'])){
            $data['desc'] = $param['desc'];
        }

        if(!empty($param['desttype'])){
            $data['desttype'] = $param['desttype'];
        }
        
        if(!empty($param['time'])){
        	$data['time'] = $param['time'];
        }

        $curl = new Curl();
        $res = $curl->Post($url,$data);
        $res = json_decode($res, true);
        if($res['rs'] == '1'){
            return true;
        }
        elseif ($res['rs'] == '-1000'){
            return '系统错误';
        }
        else{
            $res['data'] = $data;
            Log::error($res);
            return false;
        }
    }
    
    /**
     * 用户隐私更新接口
     * userat: 非必传，接受@我的提醒 1是 0否
     * comment: 非必传 ，接受评论&回复的提醒 1是 0否
     * agreement： 非必传 ，接受点赞的提醒 1是 0否
     * follow：非必传 ，接受关注的提醒 1是 0否
     * systeminfo：非必传 ，接受系统通知的提醒 1是 0否
     */
    public static function updateuserprivacy($param=array())
    {
    	global $wgEnv;
    	//参数
    	$data = array();
    	
    	$url = 'http://api.joyme.' . $wgEnv . '/api/privacy/updateuserprivacy';
    
    	if (empty($param['uid'])) {
    		return false;
    	} else {
    		$data['uid'] = $param['uid'];
    	}
    	
    	if(isset($param['userat'])){
    		$data['userat'] = $param['userat']==1?1:0;
    	}
    	if(isset($param['comment'])){
    		$data['comment'] = $param['comment']==1?1:0;
    	}
    	if(isset($param['agreement'])){
    		$data['agreement'] = $param['agreement']==1?1:0;
    	}
    	if(isset($param['follow'])){
    		$data['follow'] = $param['follow']==1?1:0;
    	}
    	if(isset($param['systeminfo'])){
    		$data['systeminfo'] = $param['systeminfo']==1?1:0;
    	}
    
    	$curl = new Curl();
    	$res = $curl->Post($url,$data);
    	$res = json_decode($res, true);
    	if($res['rs'] == '1'){
    		return true;
    	}
    	elseif ($res['rs'] == '-1000'){
    		return '系统错误';
    	}
    	else{
    		$res['data'] = $data;
    		Log::error($res);
    		return false;
    	}
    }

    /**
     * 用户动态上报
     * actionType 的值为array(
     *      add_wiki => 增加wiki
     *      edit_wiki => 编辑wiki
     *      focus_wiki => 关注wiki
     *      add_page => 增加页面
     *      edit_page => 编辑页面
     *      focus_user => 关注用户
     *      favirate_page => 收藏页面
     * )
     */
    public static function adduseractivity($userId,$actionType,$extendBody,$time=null)
    {
        global $wgEnv;

        $url = 'http://api.joyme.'.$wgEnv.'/api/timeline/addusertimeline';
        $data = array(
            'type' => 'wiki'
        );

        $joymewikiuser = new JoymeWikiUser();
        //用户profileid
        $profileid = $joymewikiuser->getProfileid($userId);
        if (empty($profileid)) {
            return false;
        } else {
            $data['profileid'] = $profileid;
        }
        //操作类型
        if(empty($actionType)){
            return false;
        }else{
            $data['actionType'] = $actionType;
        }
        //操作时间
        if(!empty($time)){
        	$data['time'] = $time*1000;
        }
        //动态内容
        if(empty($extendBody)){
            return false;
        }else{
            $data['extendBody'] = $extendBody;
        }
        
        

        $curl = new Curl();
        $res = $curl->Post($url,$data);
        $res = json_decode($res, true);
        //var_dump($res);exit;
        if($res['rs'] == '1'){
            return true;
        }
        else{
            $res['data'] = $data;
            Log::error($res);
            return false;
        }
    }




    /**
     * 注册
     */
    public function register($param = array())
    {
        $this->initData();
        //参数
        $data = array();

        //手机号不能为空
        if (empty($param['mobile'])) {
            return '手机号不能为空';
        } else {
            $data['loginkey'] = $param['mobile'];
        }
        //密码
        if (empty($param['password'])) {
            return '密码不能为空';
        } else {
            $data['password'] = $param['password'];
        }

        //验证码
        if (empty($param['mobilecode'])) {
            return '验证码不能为空';
        } else {
            $data['mobilecode'] = $param['mobilecode'];
        }

        //确认密码
        if (empty($param['repassword'])) {
            return '确认密码不能为空';
        } else {
            if ($param['repassword'] != $param['password']) {
                return '两次输入的密码不一致，请重新输入';
            }
        }
        //昵称
        if (empty($param['nick'])) {
            return '昵称不能为空';
        }
        
        $data['nick'] = $param['nick'];

        $data['profilekey'] = 'www';
        $data['logindomain'] = 'mobile';

        $curl = new Curl();

        $res = $curl->Post($this->registerurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == '1') {
            $ret = $this->jmuc_reguser($res['profile']['uid'], $res['profile']['nick'], $res['profile']['profileid']);
            if ($ret) {
                //设置cookie
                $this->setJoymeUserCookie(
                    $res['profile']['uid'],
                    $res['profile']['uno'],
                    $res['token']['token'],
                    $res['profile']['profileid']
                );

                return $res['rs'];
            } else {
                return '添加用户信息错误';
            }
        }
        elseif ($res['rs'] == '-10103') {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return '您输入的手机号已注册';
        }
        elseif ($res['rs'] == '-10112') {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return '昵称已存在';
        }
        elseif ($res['rs'] == '-10126') {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return '昵称含有特殊字符或敏感词';
        }
        elseif ($res['rs'] == '-10204') {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return '验证码错误';
        }
        elseif ($res['rs'] == '-10206') {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return '验证码错误';
        }
        else {
            $res['usercentertype'] = 'register';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 注册发送手机验证码
     */
    public function sendRegMobileCode($mobile,$luotestresponse)
    {
        $this->initData();
        if (empty($mobile)) {
            return false;
        }
        if (!preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $mobile)) {
            return false;
        }
        if (empty($luotestresponse)) {
            return false;
        }

        $curl = new Curl();

        $res = $curl->Post($this->sendregcodeurl, array(
            'mobile' => $mobile,
            'luotestresponse' => $luotestresponse
        ));

        $res = json_decode($res, true);
        if ($res['rs'] == '1') {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-10201') {
            $res['usercentertype'] = 'sendRegMobileCode';
            Log::error($res);
            return '该手机号发送已超出今日限制';
        }
        elseif ($res['rs'] == '-10207') {
            $res['usercentertype'] = 'sendRegMobileCode';
            Log::error($res);
            return '发送失败';
        }
        elseif ($res['rs'] == '-110') {
            $res['usercentertype'] = 'sendRegMobileCode';
            Log::error($res);
            return '螺丝帽校验失败';
        }
        else {
            $res['usercentertype'] = 'sendRegMobileCode';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 登录
     */
    public function login($param = array())
    {
        $this->initData();
        //参数
        $data = array(
            'profilekey' => 'www',
//            'logindomain' => 'mobile'
        );

        //账号不能为空
        if (empty($param['account'])) {
            return '账号不能为空';
        } else {
            $data['loginkey'] = $param['account'];
        }

        //手机号不能为空
        /*if (empty($param['mobile'])) {
            return '手机号不能为空';
        } else {
            $data['loginkey'] = $param['mobile'];
        }*/
        //密码不能为空
        if (empty($param['password'])) {
            return '密码不能为空';
        } else {
            $data['password'] = $param['password'];
        }

        $curl = new Curl();

        $res = $curl->Post($this->loginurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == '1') {
            $dbr = wfGetDB(DB_SLAVE);
            $user_addition_count = $dbr->selectRowCount(
                'user_addition',
                '*',
                array('user_id' => $res['profile']['uid']),
                __METHOD__
            );
            if (empty($user_addition_count)) {
                $dbw = wfGetDB(DB_MASTER);
                $dbw->insert(
                    'user_addition',
                    array(
                        'user_id' => $res['profile']['uid'],
                        'profileid' => $res['profile']['profileid'],
                    ),
                    __METHOD__
                );
            }

            //设置cookie
            $this->setJoymeUserCookie(
                $res['profile']['uid'],
                $res['profile']['uno'],
                $res['token']['token'],
                $res['profile']['profileid'],
                $param['keeptime']
            );

            return $res['rs'];
        }
        elseif ($res['rs'] == '-10103' || $res['rs'] == '-10106') {
            $res['usercentertype'] = 'login';
            Log::error($res);
            return '您输入的账号或密码错误';
        }
        elseif ($res['rs'] == '-10126'){
            $res['usercentertype'] = 'login';
            Log::error($res);
            return '昵称不合法';
        }
        else {
            $res['usercentertype'] = 'login';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 退出
     */
    public function userlogout($token)
    {
        $this->initData();
        //参数
        $data = array();

        //token不能为空
        if (empty($token)) {
            return false;
        } else {
            $data['token'] = $token;
        }

        $curl = new Curl();

        $res = $curl->Post($this->logouturl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == '1') {
            return true;
        } else {
            $res['usercentertype'] = 'userlogout';
            Log::error($res);
            return $res['msg'];
        }
    }

    //发送手机短信
    public function sendMobileMsg($mobile,$luotestresponse)
    {
        $this->initData();
        if (empty($mobile)) {
            return false;
        }
        if (!preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $mobile)) {
            return false;
        }
        if (empty($luotestresponse)){
            return false;
        }

        $curl = new Curl();

        $res = $curl->Post($this->sendcodeurl, array(
            'mobile' => $mobile,
            'luotestresponse' => $luotestresponse
        ));

        $res = json_decode($res, true);
        if ($res['rs'] == '1') {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-10103') {
            $res['usercentertype'] = 'sendMobileMsg';
            Log::error($res);
            return '手机号不存在';
        }
        elseif ($res['rs'] == '-10201') {
            $res['usercentertype'] = 'sendMobileMsg';
            Log::error($res);
            return '该手机号发送已超出今日限制';
        }
        elseif ($res['rs'] == '-10207') {
            $res['usercentertype'] = 'sendMobileMsg';
            Log::error($res);
            return '发送失败';
        }
        elseif ($res['rs'] == '-110') {
            $res['usercentertype'] = 'sendMobileMsg';
            Log::error($res);
            return '螺丝帽校验失败';
        }
        else {
            $res['usercentertype'] = 'sendMobileMsg';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 绑定手机号
     */
    public function bindMobile($userId, $mobile, $password, $repassword, $mobilecode)
    {
        $this->initData();
        $data = array(
            'profilekey' => 'www'
        );

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return 'profileid不能为空';
        } else {
            $data['profileid'] = $profileid;
        }
        //手机号不能为空
        if (empty($mobile)) {
            return '手机号不能为空';
        } else {
            $data['mobile'] = $mobile;
        }
        if (!preg_match('/0?(13|14|15|18)[0-9]{9}/', $mobile)) {
            return false;
        }
        //密码不能为空
        if (empty($password)) {
            return '密码不能为空';
        } else {
            $data['password'] = $password;
        }

        //确认密码不能为空
        if (empty($repassword)) {
            return '确认密码不能为空';
        } else {
            if ($password != $repassword) {
                return '两次输入的密码不一致';
            }
        }
        //验证码不能为空
        if (empty($mobilecode)) {
            return '验证码不能为空';
        } else {
            $data['mobilecode'] = $mobilecode;
        }

        $curl = new Curl();

        $res = $curl->Post($this->bindmobileurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-10123') {
            $res['usercentertype'] = 'bindMobile';
            Log::error($res);
            return array(
                'code' => '10123',
                'msg' => '手机号已绑定'
            );
        }
        elseif ($res['rs'] == '-10124') {
            $res['usercentertype'] = 'bindMobile';
            Log::error($res);
            return array(
                'code' => '10124',
                'msg' => '手机号已绑定'
            );
        }
        elseif ($res['rs'] == '-10204') {
            $res['usercentertype'] = 'bindMobile';
            Log::error($res);
            return array(
                'code' => '10204',
                'msg' => '验证码错误'
            );
        }
        elseif ($res['rs'] == '-10206') {
            $res['usercentertype'] = 'bindMobile';
            Log::error($res);
            return array(
                'code' => '10206',
                'msg' => '验证码错误'
            );
        }
        else {
            $res['usercentertype'] = 'bindMobile';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 修改密码
     */
    public function modifyPassword($userId, $oldpwd , $pwd, $repeatpwd, $logindomain = 'mobile')
    {
        $this->initData();
        //参数
        $data = array();

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return 'profileid不能为空';
        } else {
            $data['profileid'] = $profileid;
        }

        //旧密码
        if (empty($oldpwd)) {
            return '旧密码不能为空';
        } else {
            $data['oldpwd'] = $oldpwd;
        }

        //新密码
        if (empty($pwd)) {
            return '新密码不能为空';
        } else {
            if ($pwd == $oldpwd) {
                return '新密码不能和旧密码一样';
            }else{
                $data['pwd'] = $pwd;
            }
        }

        //确认密码
        if (empty($repeatpwd)) {
            return '确认密码不能为空';
        } else {
            if ($pwd != $repeatpwd) {
                return '两次输入的密码不一致';
            }
        }

        //登录方式
        if (empty($logindomain)) {
            return false;
        } else {
            $data['logindomain'] = $logindomain;
        }

        $curl = new Curl();

        $res = $curl->Post($this->modifypasswordurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return true;
        }
        elseif ($res['rs'] == '-1000') {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return '系统错误';
        }
        elseif ($res['rs'] == '-1001') {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return '参数为空';
        }
        elseif ($res['rs'] == '-10103') {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return '账号不存在';
        }
        elseif ($res['rs'] == '-10106') {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return '登录方式不正常';
        }
        elseif ($res['rs'] == '-10116') {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return '密码不正确';
        }
        else {
            $res['usercentertype'] = 'modifyPassword';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 重置密码
     */
    public function recoverPassword($mobile, $pwd, $repeatpwd, $mobilecode)
    {
        $this->initData();
        //参数
        $data = array();

        //手机号不能为空
        if (empty($mobile)) {
            return '手机号不能为空';
        } else {
            $data['mobile'] = $mobile;
        }
        if (!preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $mobile)) {
            return '手机号格式不正确';
        }

        //新密码
        if (empty($pwd)) {
            return '密码不能为空';
        } else {
            $data['pwd'] = $pwd;
        }
        //重复输入新密码
        if (empty($repeatpwd)) {
            return '确认密码不能为空';
        } else {
            $data['repeatpwd'] = $repeatpwd;
        }
        //两次输入密码不一致
        if ($pwd != $repeatpwd) {
            return '两次输入密码不一致';
        }
        //验证码
        if (empty($mobilecode)) {
            return '验证码不能为空';
        } else {
            $data['mobilecode'] = $mobilecode;
        }

        $curl = new Curl();
        $res = $curl->Post($this->recoverpasswordurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-10103') {
            $res['usercentertype'] = 'recoverPassword';
            Log::error($res);
            return '手机号不存在';
        }
        elseif ($res['rs'] == '-10125') {
            $res['usercentertype'] = 'recoverPassword';
            Log::error($res);
            return '新密码不能与老密码相同';
        }
        elseif ($res['rs'] == '-10204') {
            $res['usercentertype'] = 'recoverPassword';
            Log::error($res);
            return '验证码错误';
        }
        elseif ($res['rs'] == '-10206') {
            $res['usercentertype'] = 'recoverPassword';
            Log::error($res);
            return '验证码错误';
        }
        else {
            $res['usercentertype'] = 'recoverPassword';
            Log::error($res);
            return $res['msg'];
        }
    }


    /**
     * 换绑手机号
     */
    public function modifyMobile($userId, $mobile, $oldmobile)
    {
        $this->initData();
        //参数
        $data = array(
            'logindomain' => 'mobile' //登录方式
        );

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return 'profileid不能为空';
        } else {
            $data['profileid'] = $profileid;
        }
        //手机号
        if (empty($mobile)) {
            return '手机号不能为空';
        } else {
            $data['mobile'] = $mobile;
        }
        //旧手机号不能为空
        if (empty($oldmobile)) {
            return '旧手机号不能为空';
        } else {
            $data['oldmobile'] = $oldmobile;
        }

        $curl = new Curl();

        $res = $curl->Post($this->modifymobileurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-1000'){
            $res['usercentertype'] = 'modifyMobile';
            Log::error($res);
            return '系统错误';
        }
        elseif ($res['rs'] == '-10104'){
            $res['usercentertype'] = 'modifyMobile';
            Log::error($res);
            return '用户profile不存在';
        }
        elseif ($res['rs'] == '-10124'){
            $res['usercentertype'] = 'modifyMobile';
            Log::error($res);
            return '手机号已存在';
        }
        else {
            $res['usercentertype'] = 'modifyMobile';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 根据用户ids获取主站用户信息
     */
    public function getProfile($userIds)
    {
        $this->initData();
        //参数
        $data = array();

        $profileid = $this->getProfileid($userIds);
        if (empty($profileid)) {
            return false;
        } else {
            $data['profileid'] = $profileid;
        }

        $curl = new Curl();

        $res = $curl->Post($this->getprofileurl, $data);

        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            if (!empty($res['result'])) {
                if (!is_array($userIds)) {
                    $this->mobile = $res['result'][0]['mobile'];
                    $this->nick = $res['result'][0]['nick'] = ucfirst($res['result'][0]['nick']);
                    $this->uno = $res['result'][0]['uno'];
                    $this->sex = $res['result'][0]['sex'];
                    $this->proviceid = $res['result'][0]['province'];
                    $this->cityid = $res['result'][0]['city'];
                    if (isset($res['result'][0]['icon'])
                        && $res['result'][0]['icon']
                    ) {
                        $this->icon = $res['result'][0]['icon'];
                    } else {
                        $this->icon = $res['result'][0]['icon'] = $this->defaulticonurl;
                    }
                    $this->bindsinaflag = $res['result'][0]['bindsinaflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['sina']);
                    $this->bindqqflag = $res['result'][0]['bindqqflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['qq']);
                    $this->bindmobileflag = $res['result'][0]['bindmobileflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['mobile']);

                } else {
                    if ($res['result']) {
                        foreach ($res['result'] as $k => $result) {
                            $res['result'][$k]['nick'] = ucfirst($result['nick']);
                            if (empty($result['icon'])) {
                                $res['result'][$k]['icon'] = $this->defaulticonurl;
                            }else{
                                $res['result'][$k]['icon'] = $result['icon'];
                            }
                            $res['result'][$k]['bindsinaflag'] = $result['flag'] & (1 << $this->profileflags['sina']);
                            $res['result'][$k]['bindqqflag'] = $result['flag'] & (1 << $this->profileflags['qq']);
                            $res['result'][$k]['bindmobileflag'] = $result['flag'] & (1 << $this->profileflags['mobile']);
                        }
                    }
                }
            }

            return $res['result'];
        } else {
            $res['usercentertype'] = 'getProfile';
            Log::error($res);
            return false;
        }

    }

    /**
     * 根据profileid获取用户信息
     */
    public function getProfilebyid($profileid)
    {
        $this->initData();
        //参数
        $data = array();

        if (empty($profileid)) {
            return false;
        } else {
            $data['profileid'] = $profileid;
        }

        $curl = new Curl();

        $res = $curl->Post($this->getprofileurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {

            $this->mobile = $res['result'][0]['mobile'];
            $this->nick = $res['result'][0]['nick'];
            $this->uno = $res['result'][0]['uno'];
            $this->sex = $res['result'][0]['sex'];
            $this->proviceid = $res['result'][0]['province'];
            $this->cityid = $res['result'][0]['city'];
            if (isset($res['result'][0]['icon'])
                && $res['result'][0]['icon']
            ) {
                $this->icon = $res['result'][0]['icon'];
            } else {
                $this->icon = $res['result'][0]['icon'] = $this->defaulticonurl;
            }
            $this->bindsinaflag = $res['result'][0]['bindsinaflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['sina']);
            $this->bindqqflag = $res['result'][0]['bindqqflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['qq']);
            $this->bindmobileflag = $res['result'][0]['bindmobileflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['mobile']);

            return $res['result'];
        } else {
            $res['usercentertype'] = 'getProfilebyid';
            Log::error($res);
            return false;
        }

    }
    
    /**
     * 根据uid获取用户信息
     */
    public function getProfilebyUid($uid)
    {
    	$this->initData();
    	//参数
    	$data = array();
    
    	if (empty($uid)) {
    		return false;
    	} else {
    		$data['uid'] = $uid;
    	}
    
    	$curl = new Curl();
    
    	$res = $curl->Post($this->getprofilebyuidurl, $data);
    	$res = json_decode($res, true);
    	if ($res['rs'] == 1 && !empty($res['result'])) {
    
    		$this->mobile = $res['result'][0]['mobile'];
    		$this->nick = $res['result'][0]['nick'];
    		$this->uno = $res['result'][0]['uno'];
    		$this->sex = $res['result'][0]['sex'];
    		$this->proviceid = $res['result'][0]['province'];
    		$this->cityid = $res['result'][0]['city'];
    		if (isset($res['result'][0]['icon'])
    				&& $res['result'][0]['icon']
    		) {
    			$this->icon = $res['result'][0]['icon'];
    		} else {
    			$this->icon = $res['result'][0]['icon'] = $this->defaulticonurl;
    		}
    		$this->bindsinaflag = $res['result'][0]['bindsinaflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['sina']);
    		$this->bindqqflag = $res['result'][0]['bindqqflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['qq']);
    		$this->bindmobileflag = $res['result'][0]['bindmobileflag'] = $res['result'][0]['flag'] & (1 << $this->profileflags['mobile']);
    
    		return $res['result'];
    	} else {
    		$res['usercentertype'] = 'getProfilebyid';
    		Log::error($res);
    		return false;
    	}
    
    }


    /**
     * 修改用户昵称
     */
    public function modifyNick($userId, $nick)
    {
        $this->initData();
        //参数
        $data = array();

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return false;
        } else {
            $data['profileid'] = $profileid;
        }


        //昵称
        if (empty($nick)) {
            return false;
        } else {
            $data['nick'] = $nick;
        }

        $curl = new Curl();

        $res = $curl->Post($this->modifynickurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        } else {
            $res['usercentertype'] = 'modifyNick';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 修改用户头像
     */
    public function modifyIcon($userId, $icon)
    {
        $this->initData();
        //参数
        $data = array();

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return false;
        } else {
            $data['profileid'] = $profileid;
        }

        //头像
        if (empty($icon)) {
            return false;
        } else {
            $data['icon'] = $icon;
        }

        $curl = new Curl();

        $res = $curl->Post($this->modifyiconurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return true;
        } else {
            $res['usercentertype'] = 'modifyIcon';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 修改用户信息
     */
    public function modifyInfo($userId, $sex = 0, $proviceid = '', $cityid = '')
    {
        $this->initData();
        //参数
        $data = array();

        //profileid不能为空
        $profileid = $this->getProfileid($userId);
        if (empty($profileid)) {
            return '用户id不能为空';
        } else {
            $data['profileid'] = $profileid;
        }

        //性别
        if ($sex || $sex === 0) {
            $data['sex'] = $sex;
        }

        //所在地
        if ($proviceid) {
            $data['proviceid'] = $proviceid;
        }
        if ($cityid) {
            $data['cityid'] = $cityid;
        }

        $curl = new Curl();

        $res = $curl->Post($this->modifyinfourl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        } else {
            $res['usercentertype'] = 'modifyInfo';
            Log::error($res);
            return $res['msg'];
        }
    }


    /**
     * 解绑第三方账号
     */
    public function unbindThirdPartyAccount($thirdtype, $uno)
    {
        $this->initData();
        //参数
        $data = array(
            'profilekey' => 'www'
        );

        //第三方账号类型
        if (empty($thirdtype)) {
            return '第三方账号类型不能为空';
        }

        if (!in_array($thirdtype, $this->thirdAccounts)) {
            return '不支持当前第三方账号';
        }

        //userno
        if (empty($uno)) {
            return 'userno不能为空';
        } else {
            $data['uno'] = $uno;
        }
        $url = $this->unbindurldata[$thirdtype];

        $curl = new Curl();
        $res = $curl->Post($url, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        } else {
            $res['usercentertype'] = 'unbindThirdPartyAccount';
            Log::error($res);
            return $res['msg'];
        }

    }

    /**
     * 验证手机合法性
     */
    public function verifyMobile($mobile, $mobilecode)
    {
        $this->initData();
        //参数
        $data = array();

        //手机号不能为空
        if (empty($mobile)) {
            return '手机号不能为空';
        } else {
            $data['mobile'] = $mobile;
        }
        if (!preg_match('/0?(13|14|15|17|18)[0-9]{9}/', $mobile)) {
            return '手机号格式不正确';
        }
        //验证码不能为空
        if (empty($mobilecode)) {
            return '验证码不能为空';
        } else {
            $data['mobilecode'] = $mobilecode;
        }

        $curl = new Curl();

        $res = $curl->Post($this->verifymobileurl, $data);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return $res['rs'];
        }
        elseif ($res['rs'] == '-10204') {
            $res['usercentertype'] = 'verifyMobile';
            Log::error($res);
            return '验证码错误';
        }
        elseif ($res['rs'] == '-10206') {
            $res['usercentertype'] = 'verifyMobile';
            Log::error($res);
            return '验证码错误';
        }
        else {
            $res['usercentertype'] = 'verifyMobile';
            Log::error($res);
            return $res['msg'];
        }
    }

    /**
     * 获取城市配置接口
     */
    public function cityInfo()
    {
        $this->initData();
        $curl = new Curl();

        $res = $curl->Post($this->cityinfourl);
        $res = json_decode($res, true);
        if ($res['rs'] == 1) {
            return true;
        } else {
            return $res['msg'];
        }
    }


    ####################################################################
    ##########################Java接口调用结束###########################
    ####################################################################


    ####################################################################
    ##########################Luosimao接口调用开始#######################
    ####################################################################

    public static function LSMSiteVerify($response)
    {
        $lsmverifyurl = 'https://captcha.luosimao.com/api/site_verify';
        //参数
        $data = array(
            'api_key' => 'b835a45eb28ec46b8f79bb87eea9cb05'
        );

        if (empty($response)) {
            return 'response不能为空';
        } else {
            $data['response'] = $response;
        }

        $curl = new Curl();

        $res = $curl->Post($lsmverifyurl, $data);
        $res = json_decode($res, true);
        Log::error($res);
        if ($res['error'] == 0) {
            return true;
        } else {
            Log::error($res);
            return false;
        }
    }

    ####################################################################
    ##########################Luosimao接口调用结束#######################
    ####################################################################


    /**
     * 修改用户附加表
     */
    public function editUserAddition($param = array())
    {

        global $wgMemc;
        $data = array();

        //用户id
        if (isset($param['user_id'])
            && !empty($param['user_id'])
        ) {
            $user_id = $param['user_id'];
        } else {
            return false;
        }

        //profileid
        if (isset($param['profileid'])
            && !empty($param['profileid'])
        ) {
            $data['profileid'] = $param['profileid'];
        }

        //被赞数
        if (isset($param['total_like_count'])
            && !empty($param['total_like_count'])
        ) {
            $data['total_like_count'] = $param['total_like_count'];
        }
        //被评数
        if (isset($param['total_comment_count'])
            && !empty($param['total_comment_count'])
        ) {
            $data['total_comment_count'] = $param['total_comment_count'];
        }
        //用户编辑数
        if (isset($param['total_edit_count'])
            && !empty($param['total_edit_count'])
        ) {
            $data['total_edit_count'] = $param['total_edit_count'];
        }
        //简介
        if (isset($param['brief'])
        ) {
            $data['brief'] = $param['brief'];
        }
        //兴趣
        if (isset($param['interest'])
        ) {
            $data['interest'] = $param['interest'];
        }
        //生日
        if (isset($param['birthday'])
        ) {
            $data['birthday'] = $param['birthday'];
        }
        //是否允许他人关注(1：允许，0：不允许)
        if (isset($param['is_attention'])
        ) {
            $data['is_attention'] = $param['is_attention'];
        }
        //是否允许他人私信(1：允许，0：不允许)
        if (isset($param['is_secretchat'])
        ) {
            $data['is_secretchat'] = $param['is_secretchat'];
        }

        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->update(
            'user_addition',
            $data,
            array('user_id' => $user_id),
            __METHOD__
        );
        $dbw->commit(__METHOD__);
        if ($ret) {
            // clear stats cache for current user
            $key = wfForeignMemcKey('homewiki',false,'user','stats',$user_id);
            $wgMemc->delete($key);
        }

        return $ret;
    }

    /**
     * 获取用户附加信息
     */
    public function getUserAddition($userIds)
    {
        if (empty($userIds)) {
            return false;
        }
        if (is_array($userIds)) {
            $userIds = implode(',', $userIds);
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_addition',
            '*',
            array(
                'user_id IN (' . $userIds . ')'
            ),
            __METHOD__
        );
        if($res){
            $user_add = array();
            foreach ($res as $k => $row) {
                $user_add[$k]['user_profileid'] = $row->profileid;
                $user_add[$k]['total_like_count'] = $row->total_like_count;
                $user_add[$k]['total_comment_count'] = $row->total_comment_count;
                $user_add[$k]['total_edit_count'] = $row->total_edit_count;
                $user_add[$k]['brief'] = $row->brief;
                $user_add[$k]['interest'] = $row->interest;
                $user_add[$k]['birthday'] = $row->birthday;
                $user_add[$k]['is_attention'] = $row->is_attention;
                $user_add[$k]['is_secretchat'] = $row->is_secretchat;
            }
            if(!is_array($userIds)){
                $this->user_profileid = isset($user_add[1]['user_profileid']) ? $user_add[1]['user_profileid'] : '';
                $this->total_like_count = isset($user_add[1]['total_like_count']) ? $user_add[1]['total_like_count'] : 0;
                $this->total_comment_count = isset($user_add[1]['total_comment_count']) ? $user_add[1]['total_comment_count'] : 0;
                $this->total_edit_count = isset($user_add[1]['total_edit_count']) ? $user_add[1]['total_edit_count'] : 0;
                $this->brief = isset($user_add[1]['brief']) ? $user_add[1]['brief'] : '';
                $this->interest = isset($user_add[1]['interest']) ? $user_add[1]['interest'] : '';
                $this->birthday = isset($user_add[1]['birthday']) ? $user_add[1]['birthday'] : '';
                $this->is_attention = isset($user_add[1]['is_attention']) ? $user_add[1]['is_attention'] : 0;
                $this->is_secretchat = isset($user_add[1]['is_secretchat']) ? $user_add[1]['is_secretchat'] : 0;
            }
            return $user_add;
        }else{
            return false;
        }
    }

    //添加用户附加表
    public function addUserAddtion($param = array())
    {
        $data = array();

        //用户id
        if (isset($param['user_id'])
            && !empty($param['user_id'])
        ) {
            $data['user_id'] = $user_id = $param['user_id'];
        } else {
            return false;
        }

        //profileid
        if (isset($param['profileid'])
            && !empty($param['profileid'])
        ) {
            $data['profileid'] = $param['profileid'];
        }

        //被赞数
        if (isset($param['total_like_count'])
            && !empty($param['total_like_count'])
        ) {
            $data['total_like_count'] = $param['total_like_count'];
        }
        //被评数
        if (isset($param['total_comment_count'])
            && !empty($param['total_comment_count'])
        ) {
            $data['total_comment_count'] = $param['total_comment_count'];
        }
        //简介
        if (isset($param['brief'])
            && !empty($param['brief'])
        ) {
            $data['brief'] = $param['brief'];
        }
        //兴趣
        if (isset($param['interest'])
            && !empty($param['interest'])
        ) {
            $data['interest'] = $param['interest'];
        }
        //生日
        if (isset($param['birthday'])
            && !empty($param['birthday'])
        ) {
            $data['birthday'] = $param['birthday'];
        }
        //是否允许他人关注(1：允许，0：不允许)
        if (isset($param['is_attention'])
        ) {
            $data['is_attention'] = $param['is_attention'];
        }
        //是否允许他人私信(1：允许，0：不允许)
        if (isset($param['is_secretchat'])
        ) {
            $data['is_secretchat'] = $param['is_secretchat'];
        }

        $dbr = wfGetDB(DB_SLAVE);
        $user_addition_count = $dbr->selectRowCount(
            'user_addition',
            '*',
            array('user_id' => $user_id),
            __METHOD__
        );
        if (empty($user_addition_count)) {
            $dbw = wfGetDB(DB_MASTER);
            return $dbw->insert(
                'user_addition',
                $data
            );
        }else{
            return false;
        }
    }

    /**
     * 用户点赞数
     */
    public static function updateUserLikeCount($user_id)
    {
        global $wgMemc;
        if(empty($user_id)){
            return false;
        }
        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->update(
            'user_addition',
            array("total_like_count=total_like_count+1" ),
            array( 'user_id' => $user_id  ),
            __METHOD__
        );
        $dbw->commit(__METHOD__);
        if ($ret) {
            // clear stats cache for current user
            $key = wfForeignMemcKey('homewiki',false,'user','stats',$user_id);
            $wgMemc->delete($key);
        }

        return $ret;
    }

    /**
     * 用户评论数
     */
    public static function updateUserCommentCount($user_id)
    {
        global $wgMemc;
        if(empty($user_id)){
            return false;
        }
        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->update(
            'user_addition',
            array("total_comment_count=total_comment_count+1" ),
            array( 'user_id' => $user_id  ),
            __METHOD__
        );
        $dbw->commit(__METHOD__);
        if ($ret) {
            // clear stats cache for current user
            $key = wfForeignMemcKey('homewiki',false,'user','stats',$user_id);
            $wgMemc->delete($key);
        }

        return $ret;
    }


    /**
     * 获取用户信息
     */
    public function getUser($user_id)
    {
        $this->getUserAddition($user_id);
        $this->getProfile($user_id);
    }


    //获取主站用户id
    public function getProfileid($userIds)
    {
        if (empty($userIds)) {
            return false;
        }
        if (is_array($userIds)) {
            $userIds = implode(',', $userIds);
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_addition',
            array(
                'profileid'
            ),
            array(
                'user_id IN (' . $userIds . ')'
            ),
            __METHOD__
        );
        $profileids = array();
        foreach ($res as $row) {
        	if($row->profileid){
        		$profileids[] = $row->profileid;
        	}
        }

        if (empty($profileids)) {
            return false;
        } else {
            return implode(',', $profileids);
        }
    }

    //获取wiki用户user信息
    public function getWikiUser($userIds)
    {

        if (empty($userIds)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRow(
            'user',
            array('*','DATE_FORMAT(user_registration,"%Y-%m-%d %H:%i:%s") as time'),
            array(
                'user_id' => $userIds
            )
        );
    }

    /**
     * 获取wiki站点信息
     */
    public function getSiteInfo($site_keys)
    {
        if (empty($site_keys)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $result = array();
        if (is_array($site_keys)) {
            $keystr = implode('","', $site_keys);
            $where = array(
                'site_key in ("' . $keystr . '")'
            );
        } else {
            $where = array(
                'site_key' => $site_keys
            );
        }
        $res = $dbr->select(
            'joyme_sites',
            array(
                'site_id',
                'site_name',
                'site_key',
                'site_icon',
                'site_type',
                'site_page_count',
                'site_edit_count'
            ),
            $where
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['site_id'] = $row->site_id;
                $result[$k]['site_name'] = $row->site_name;
                $result[$k]['site_key'] = $row->site_key;
                $result[$k]['site_type'] = $row->site_type;
                $result[$k]['site_icon'] = $row->site_icon;
                $result[$k]['page_count'] = $row->site_page_count;
                $result[$k]['edit_count'] = $row->site_edit_count;
            }
            $site_ids = array_column($result, 'site_id');
            $joymesite = new JoymeSite();
            $siteyescounts = $joymesite->getSiteYesCount($site_ids);
            if ($siteyescounts) {
                $sycounts = array_column($siteyescounts, 'edit_count', 'site_id');
                foreach ($res as $k => $row) {
                    //昨日编辑次数
                    if (isset($sycounts[$row->site_id])
                        && $sycounts[$row->site_id]
                    ) {
                        $result[$k]['yes_editcount'] = $sycounts[$row->site_id];
                    } else {
                        $result[$k]['yes_editcount'] = 0;
                    }
                }
            }
        }
        return $result;
    }


    /**
     * 获取用户今日编辑数
     */
    public function getUserTodayEditCount($user_id)
    {
        //今日编辑
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRow(
            'user_editcount_log',
            array('edit_count'),
            array(
                'user_id' => $user_id,
                'edit_date' => date('Y-m-d')
            ),
            __METHOD__
        );
        if ($res) {
            $today_edit_count = $res->edit_count;
        } else {
            $today_edit_count = 0;
        }
        return $today_edit_count;
    }

    /**
     * 添加用户编辑数记录
     */
    public static function editUserEditCountlog()
    {
        global $wgUser;
        $user_id = $wgUser->getId();

        if (empty($user_id)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_editcount_log',
            '*',
            array(
                'user_id' => $user_id,
                'edit_date' => date('Y-m-d')
            ),
            __METHOD__
        );
        $dbw = wfGetDB(DB_MASTER);
        if ($res) {
            $ret = $dbw->update(
                'user_editcount_log',
                array("edit_count=edit_count+1" ),
                array(
                    'user_id' => $user_id,
                    'edit_date' => date('Y-m-d')
                ),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            return $ret;
        } else {
            return $dbw->insert(
                'user_editcount_log',
                array(
                    'user_id' => $user_id,
                    'edit_count' => 1,
                    'edit_date' => date('Y-m-d'),
                ),
                __METHOD__
            );
        }
    }

    //更新用户总的编辑次数
    public static function updateUserEditCount()
    {
        global $wgUser,$wgMemc;
        $user_id = $wgUser->getId();

        if (empty($user_id)) {
            return false;
        }

        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->update(
            'user_addition',
            array("total_edit_count=total_edit_count+1" ),
            array( 'user_id' => $user_id  ),
            __METHOD__
        );
        $dbw->commit(__METHOD__);
        if ($ret) {
            // clear stats cache for current user
            $key = wfForeignMemcKey('homewiki',false,'user','stats',$user_id);
            $wgMemc->delete($key);
            return true;
        }else{
            return false;
        }
    }


    /**
     * 添加用户站点的贡献次数
     */
    public static function addUserSiteOfferCount()
    {

        global $wgSiteId, $wgUser;

        if (empty($wgSiteId)) {
            return false;
        }
        $user_id = $wgUser->getId();
        if (empty($user_id)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_site_addition',
            '*',
            array(
                'user_id' => $user_id,
                'site_id' => $wgSiteId,
            )
        );
        $dbw = wfGetDB(DB_MASTER);
        if ($res) {
            $ret = $dbw->update(
                'user_site_addition',
                array("contribution_count=contribution_count+1" ),
                array(
                    'user_id' => $user_id,
                    'site_id' => $wgSiteId,
                ),
                __METHOD__
            );
            $dbw->commit(__METHOD__);
            return $ret;
        } else {
            return $dbw->insert(
                'user_site_addition',
                array(
                    'user_id' => $user_id,
                    'site_id' => $wgSiteId,
                    'contribution_count' => 1
                ),
                __METHOD__
            );
        }
    }

    /**
     * 获取用户站点的贡献次数
     */
    public function getUserSiteOfferCount($user_ids = array(), $site_ids = array())
    {
        $result = array();
        $where = array();
        if (empty($user_ids) && empty($site_ids)) {
            return false;
        } elseif ($user_ids && empty($site_ids)) {
            if (is_array($user_ids)) {
                $keystr = implode(',', $user_ids);
                $where[] = 'user_id in ( ' . $keystr . ')';
            } else {
                $where[] = 'user_id = ' . $user_ids;
            }
        } elseif (empty($user_ids) && $site_ids) {
            if (is_array($site_ids)) {
                $keystr = implode(',', $site_ids);
                $where[] = 'site_id in ( ' . $keystr . ')';
            } else {
                $where[] = 'site_id = ' . $site_ids;
            }
        } else {
            if (is_array($user_ids)) {
                $keystr = implode(',', $user_ids);
                $where[] = 'user_id in ( ' . $keystr . ')';
            } else {
                $where[] = 'user_id = ' . $user_ids;
            }

            if (is_array($site_ids)) {
                $keystr = implode(',', $site_ids);
                $where[] = 'site_id in ( ' . $keystr . ')';
            } else {
                $where[] = 'site_id = ' . $site_ids;
            }
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_site_addition',
            array(
                'user_id',
                'site_id',
                'contribution_count',
            ),
            $where
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['user_id'] = $row->user_id;
                $result[$k]['site_id'] = $row->site_id;
                $result[$k]['offer_count'] = $row->contribution_count;
            }
        }

        return $result;
    }


    /**
     * 获取用户关注站点个数
     */
    public function getUserSiteFollowCount($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRowCount(
            'user_site_relation',
            '*',
            array(
                'user_id' => $user_id,
                'status' => 3
            )
        );
    }


    /**
     * 用户站点关系
     * (1：管理，2：贡献，3：关注)
     *
     */
    public static function addUserSiteRelation($user_id, $site_id, $status)
    {
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            return false;
        }
        if (empty($status)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_site_relation',
            '*',
            array(
                'user_id' => $user_id,
                'site_id' => $site_id
            ),
            __METHOD__
        );
        $dbw = wfGetDB(DB_MASTER);
        if ($res) {
            $ret = $dbw->update(
                'user_site_relation',
                array(
                    'status' => $status
                ),
                array(
                    'user_id' => $user_id,
                    'site_id' => $site_id
                ),
                __METHOD__
            );

        } else {
            $ret = $dbw->insert(
                'user_site_relation',
                array(
                    'user_id' => $user_id,
                    'site_id' => $site_id,
                    'status' => $status,
                    'create_time'=>time()
                ),
                __METHOD__
            );
        }
        $dbw->commit(__METHOD__);
        return $ret;
    }

    //检查是否是管理员
    public static function checkUserSiteManager($user_id, $site_id)
    {
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_site_relation',
            '*',
            array(
                'user_id' => $user_id,
                'site_id' => $site_id,
                'status' => 1
            ),
            __METHOD__
        );
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    //添加用户管理站点
    public static function addUserSiteManage($user_id, $site_id = '')
    {
        global $wgSiteId;
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            if ($wgSiteId) {
                $site_id = $wgSiteId;
            } else {
                return false;
            }
        }
        if (!self::checkUserSiteManager($user_id, $site_id)) {
            return self::addUserSiteRelation($user_id, $site_id, 1);
        } else {
            return false;
        }
    }

    //添加用户贡献站点
    public static function addUserSiteContribute($user_id, $site_id = '')
    {
        global $wgSiteId;
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            if ($wgSiteId) {
                $site_id = $wgSiteId;
            } else {
                return false;
            }
        }
        if (!self::checkUserSiteManager($user_id, $site_id)) {
            return self::addUserSiteRelation($user_id, $site_id, 2);
        } else {
            return false;
        }
    }

    //joyme_site增加关注人数
    public static function AddJoymeSiteFollow($site_id){

        $random = rand(8,12);
        $dbr = wfGetDB(DB_MASTER);
        $ret = $dbr->update(
            'joyme_sites',
            array(
                "site_follow =site_follow+1+$random"
            ),
            array(
                'site_id'=>$site_id
            )
        );
        $dbr->commit();
        return $ret;
    }

    //joyme_site取消关注人数
    public static function DelJoymeSiteFollow($site_id){

        $dbr = wfGetDB(DB_MASTER);
        $ret = $dbr->update(
            'joyme_sites',
            array(
                "site_follow =site_follow-1"
            ),
            array(
                'site_id'=>$site_id
            )
        );
        $dbr->commit();
        return $ret;
    }

    //获取站点关注数（伪数据）
    public static function getJoymeSiteFollowNum($site_id,$num = false){

        $dbr = wfGetDB(DB_MASTER);
        $follow = $dbr->selectRow(
            'joyme_sites',
            "site_follow",
            array(
                'site_id'=>$site_id
            )
        );
        if(isset($follow->site_follow) && $follow->site_follow == 0){

            if(!empty($num)){
                $number = $num*10;
            }else{
                $siteinfo = JoymeSite::getSiteInfo($site_id);
                $number = $siteinfo[1]['follow_usercount']*10;
            }
            if($number){
                $dbr->update(
                    'joyme_sites',
                    array(
                        "site_follow =site_follow+$number"
                    ),
                    array(
                        'site_id'=>$site_id
                    )
                );
                $dbr->commit();
                $follow->site_follow = $number;
            }
        }
        return $follow;
    }

    //修改站点关注伪数据
    public static function updateJoymeSiteFollow($site_id){

        self::getJoymeSiteFollowNum($site_id);
        return self::AddJoymeSiteFollow($site_id);
    }


    //修改站点关注伪数据-1
    public static function updateJoymeSiteFollowDelete($site_id){

        self::getJoymeSiteFollowNum($site_id);
        return self::DelJoymeSiteFollow($site_id);
    }

    //添加用户关注站点
    public static function addUserSiteFollow($user_id, $site_id = '')
    {
        global $wgSiteId;
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            if ($wgSiteId) {
                $site_id = $wgSiteId;
            } else {
                return false;
            }
        }
        if (!self::checkUserSiteManager($user_id, $site_id)) {
            self::updateJoymeSiteFollow($site_id);
            return self::addUserSiteRelation($user_id, $site_id, 3);
        } else {
            return false;
        }
    }





    /**
     * 判断用户是否关注站点
     */
    public static function checkUserFollowSite($user_id, $site_id)
    {
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_site_relation',
            '*',
            array(
                'user_id' => $user_id,
                'site_id' => $site_id,
            )
        );
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查看用户站点关注关系
     */
    public static function getUserFollowSite($user_id, $site_id)
    {
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRow(
            'user_site_relation',
            'status',
            array(
                'user_id' => $user_id,
                'site_id' => $site_id,
            )
        );
    }

    //取消用户管理站点
    public static function delUserSiteManage($user_id, $site_id = '')
    {
        global $wgSiteId;
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            if ($wgSiteId) {
                $site_id = $wgSiteId;
            } else {
                return false;
            }
        }
        //判断是否为管理员
        if (self::checkUserSiteManager($user_id, $site_id)) {
            $dbr = wfGetDB(DB_SLAVE);
            $res = $dbr->selectRowCount(
                'user_site_addition',
                "*",
                array(
                    'user_id' => $user_id,
                    'site_id' => $site_id
                ),
                __METHOD__
            );
            //判断是否有贡献次数
            if($res){
                //贡献
                return self::addUserSiteRelation($user_id, $site_id, 2);
            }else{
                //关注
                return self::addUserSiteRelation($user_id, $site_id, 3);
            }
        } else {
            return false;
        }
    }


    /**
     * 删除用户站点关系
     */
    public static function deleteUserSiteRelation($user_id, $site_id = '')
    {

        global $wgSiteId;
        if (empty($user_id)) {
            return false;
        }
        if (empty($site_id)) {
            if ($wgSiteId) {
                $site_id = $wgSiteId;
            } else {
                return false;
            }
        }
        if (!self::checkUserSiteManager($user_id, $site_id)) {
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->delete(
                'user_site_relation',
                array(
                    'user_id' => $user_id,
                    'site_id' => $site_id
                )
            );
            $dbw->commit();
            self::updateJoymeSiteFollowDelete($site_id);
            return $ret;
        } else {
            return false;
        }
    }


    /**
     * 获取用户站点关系
     */
    public function getUserSites($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_site_relation',
            'user_id,site_id,status',
            array(
                'user_id' => $user_id,
            ),
            __METHOD__
        );

        $wikis = array();
        if ($res) {
            $manageWikis = array();
            $contributeWikis = array();
            $followWikis = array();
            $allWikis = array();

            foreach ($res as $row) {

                if ($row->status == 1) {
                    $manageWikis[] = array(
                        'user_id' => $row->user_id,
                        'site_id' => $row->site_id,
                        'status' => $row->status
                    );
                } elseif ($row->status == 2) {
                    $contributeWikis[] = array(
                        'user_id' => $row->user_id,
                        'site_id' => $row->site_id,
                        'status' => $row->status
                    );
                } elseif ($row->status == 3) {
                    $followWikis[] = array(
                        'user_id' => $row->user_id,
                        'site_id' => $row->site_id,
                        'status' => $row->status
                    );
                }
                $allWikis[] = array(
                    'user_id' => $row->user_id,
                    'site_id' => $row->site_id,
                    'status' => $row->status
                );
            }

            $wikis = array(
                'manageWikis' => $manageWikis,
                'contributeWikis' => $contributeWikis,
                'followWikis' => $followWikis,
                'allWikis' => $allWikis,
            );
        }

        return $wikis;
    }


    /**
     * 添加用户动态
     */
    public static function addActionLog($user_id, $type, $content)
    {
        $data = array();
        if (empty($user_id)) {
            return 'user_id empty';
        } else {
            $data['user_id'] = $user_id;
        }
        if (empty($type)) {
            return 'type empty';
        }
        $joymewikiuser = new JoymeWikiUser();
        $types = array_keys($joymewikiuser->useractiontypes);
        if (!in_array($type, $types)) {
            return 'type error';
        }

        $data['type'] = (int)$type;

        if (empty($content)) {
            return false;
        } else {
            $data['content'] = $content;
        }

        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->insert(
            'user_action_log',
            array(
                'user_id' => $user_id,
                'type' => $type,
                'content' => $content,
                'add_time' => time(),
            )
        );
        $dbw->commit();
        return $ret;
    }

    /**
     * 获取用户动态
     */
    public function getUserActionLog($user_ids, $limit, $offset)
    {
        if (empty($user_ids)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $result = array();
        $options = array();
        if (is_array($user_ids)) {
            $keystr = implode(',', $user_ids);
            $where = array(
                'user_id in ( ' . $keystr . ')'
            );
        } else {
            $where = array(
                'user_id' => $user_ids
            );
        }

        if ($limit > 0) {
            $limitvalue = 0;
            if ($offset) {
                $limitvalue = $offset * $limit - ($limit);
            }
            $options['LIMIT'] = $limit;
            $options['OFFSET'] = $limitvalue;
        }
        $options['ORDER BY'] = 'add_time DESC';
        $res = $dbr->select(
            'user_action_log',
            array(
                'user_id',
                'type',
                'content',
                'add_time'
            ),
            $where,
            __METHOD__,
            $options
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['user_id'] = $row->user_id;
                $result[$k]['type'] = $row->type;
                $result[$k]['content'] = $row->content;
                $result[$k]['add_time'] = $row->add_time;
            }
        }

        return $result;
    }

    /**
     * 获取用户动态总数
     */
    public function getUserActionLogCount($user_ids)
    {
        if (empty($user_ids)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        if (is_array($user_ids)) {
            $keystr = implode(',', $user_ids);
            $where = array(
                'user_id in ( ' . $keystr . ')'
            );
        } else {
            $where = array(
                'user_id' => $user_ids
            );
        }

        return $dbr->selectRowCount(
            'user_action_log',
            '*',
            $where
        );
    }


    /**
     * 用户提醒设置
     */
    public function userRemindSet($up_user, $up_property)
    {
        global $wgUser;
        if (empty($up_user)) {
            return false;
        }
        if (empty($up_property)) {
            return false;
        }

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->selectRowCount(
            'user_properties',
            '*',
            array(
                'up_user' => $up_user,
                'up_property' => $up_property,
            ),
            __METHOD__
        );
        if ($res) {
            return true;
        } else {
            $dbw = wfGetDB(DB_MASTER);
            $ret = $dbw->insert(
                'user_properties',
                array(
                    'up_user' => $up_user,
                    'up_property' => $up_property
                ),
                __METHOD__
            );

            $wgUser->clearSharedCache();
            return $ret;
        }
    }

    /**
     * 获取用户提醒设置
     */
    public function getUserRemindSet($up_user)
    {

        if (empty($up_user)) {
            return false;
        }
        $result = array();
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'user_properties',
            array(
                'up_user',
                'up_property',
                'up_value'
            ),
            array(
                'up_user' => $up_user,
            )
        );
        if ($res) {
            foreach ($res as $k => $row) {
                $result[$k]['up_user'] = $row->up_user;
                $result[$k]['up_property'] = $row->up_property;
                $result[$k]['up_value'] = $row->up_value;
            }
        }

        return $result;
    }

    /**
     * 删除用户提醒设置
     */
    public function delUserRemindSet($up_user, $up_property)
    {

        global $wgUser;

        if (empty($up_user)) {
            return false;
        }
        if (empty($up_property)) {
            return false;
        }

        $dbw = wfGetDB(DB_MASTER);
        $ret = $dbw->delete('user_properties', array(
            'up_user' => $up_user,
            'up_property' => $up_property
        ));
        $dbw->commit();
//        $wgUser->clearSharedCache();
        return $ret;
    }


    /**
     * 设置登录验证cookie
     *
     */
    public function setJoymeUserCookie($uid, $uno, $token, $profileid,$keeptime = 'no')
    {
        global $wgEnv;


        if($keeptime == 'yes'){
            $expire_time = time() + 2592000; // 30*24*3600
        }else{
            $expire_time = null;
        }
        $domain = '.joyme.' . $wgEnv;
        $timestamp = round(microtime(true) * 1000);
        $sign = md5($uid . $uno . $timestamp . 'as__-d(*^(');

        setcookie('jmuc_lgdomain', 'mobile', $expire_time, '/', $domain);
        setcookie('jmuc_s', $sign, $expire_time, '/', $domain);
        setcookie('jmuc_t', $timestamp, $expire_time, '/', $domain);
        setcookie('jmuc_token', $token, $expire_time, '/', $domain);
        setcookie('jmuc_u', $uid, $expire_time, '/', $domain);
        setcookie('jmuc_uno', $uno, $expire_time, '/ ', $domain);
        setcookie('jmuc_appkey', 'default', $expire_time, '/', $domain);
        setcookie('jmuc_pid', $profileid, $expire_time, '/', $domain);
    }


    //根据用户名获取用户ID
    public static function getUserIdByUserName( $username ){

        if(empty($username)){
            return false;
        }
        $dbw = wfGetDB(DB_SLAVE);
        return $dbw->selectRow(
            'user',
            'user_id',
            array(
                'user_name'=>$username
            )
        );
    }

    public static function getJson($message, $rs = '0')
    {
        return json_encode(array('data' => $message, 'rs' => $rs));
    }

}

?>