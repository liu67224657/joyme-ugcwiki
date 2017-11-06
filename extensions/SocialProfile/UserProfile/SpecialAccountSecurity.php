<?php
/**
 * A special page for AccountSecurity
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialAccountSecurity extends SpecialPage {
	public $icon;
	public $brief;
	public $username;
	public $sex;
	public $mobile;
	public $interest;
	public $proviceid;
	public $birthday;
	public $bindsinaflag;
	public $bindqqflag;
	public $bindmobileflag;
	public $bindsinaweibourl;
	public $bindqqurl;
	public $is_attention;
	public $is_secretchat;

	/**
	 * Constructor
	 */
	public function __construct() {
		SpecialPage::__construct( 'AccountSecurity');
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Show the special page.
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		$this->setHeaders();

		global $wgWikiname;
		if($wgWikiname !='home'){
			$out->redirectHome('Special:AccountSecurity');
			return false;
		}


		// If the user isn't logged in, display an error
		if ( !$user->isLoggedIn() ) {
			$this->displayRestrictionError();
			return;
		}

		$out->addModuleStyles(array(
			'ext.socialprofile.userprofile.useraccount.css',
			'ext.socialprofile.userprofile.usercentercommon.css'
		));
//		$out->addModuleStyles('ext.socialprofile.userprofile.useraccount.css');
//		$out->addModuleStyles('ext.socialprofile.userprofile.usercentercommon.css');
		$out->addModuleScripts('ext.socialprofile.userprofile.useraccount.js');

		$errorcode = $request->getVal('errorcode');
		if($errorcode == "profile.has.bind"){
			$out->addModuleScripts('ext.socialprofile.userprofile.binderror.js');
		}

		$this->initData();

		$out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');

		$out->addHTML( $this->getLeftSection() );
		$out->addHTML( $this->getRightSection() );

		$out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );
	}

	public function initData(){
		global $wgJoymeUserInfo;
		$this->icon = $wgJoymeUserInfo['icon'];
		$this->brief = $wgJoymeUserInfo['brief'];
		$this->username = $wgJoymeUserInfo['nick'];
		if($wgJoymeUserInfo['sex'] == 1){
			$this->sex = 'man';
		}elseif (!is_null($wgJoymeUserInfo['sex'])
			&&$wgJoymeUserInfo['sex'] != ''
			&&$wgJoymeUserInfo['sex'] == 0
		){
			$this->sex = 'female';
		}
		$this->interest = $wgJoymeUserInfo['interest'];
		$this->mobile = $wgJoymeUserInfo['mobile'];
		$this->bindsinaflag = $wgJoymeUserInfo['bindsinaflag'];
		$this->bindqqflag = $wgJoymeUserInfo['bindqqflag'];
		$this->bindmobileflag = $wgJoymeUserInfo['bindmobileflag'];

		$this->is_attention = $wgJoymeUserInfo['is_attention'];
		$this->is_secretchat = $wgJoymeUserInfo['is_secretchat'];

		$this->proviceid = $wgJoymeUserInfo['proviceid'];
		if($wgJoymeUserInfo['birthday']){
			$this->birthday = $wgJoymeUserInfo['birthday'];
		}else{
			$this->birthday = date('Y-m-d');
		}

		$joymewikiuser = new JoymeWikiUser();
		$joymewikiuser->initData();
		$this->bindsinaweibourl = $joymewikiuser->bindsinaweibourl;
		$this->bindqqurl = $joymewikiuser->bindqqurl;
	}

	public function getLeftSection($type="account"){

		$AccountSecurity = SpecialPage::getSafeTitleFor('AccountSecurity');
		$Userinfo = SpecialPage::getSafeTitleFor('Userinfo');
		$UploadAvatar = SpecialPage::getSafeTitleFor('UploadAvatar');
		$UserPrivacy = SpecialPage::getSafeTitleFor('UserPrivacy');

		$output = '<div class="col-md-3 bg-ebeffa">
					<div class="setting-sidebar">
						<div class="setting-name web-hide">
	                         <a href="/home/用户:'.$this->username.'" class="user-login"><img src="'. $this->icon .'" alt="">
	                        </a>
	                        <div class="user-intro-con">
	                            <cite class="user-des fn-clear">
	                                <font class="nickname">'. $this->username .'</font>
	                                <i class="user-sex '.$this->sex.'"></i>
	                            </cite>
	                            <a href="javascript:;" class="user-intr">'.$this->brief.'</a>
	                        </div>
	                    </div>
	                    <ul class="setting-list">
	                    	<li><a href="'. $AccountSecurity->getFullURL() .'" class="account';
		if($type == "account"){
			$output .=' on';
		}
		$output .='"><i></i>账号安全</a></li>
	                    	<li><a href="'. $Userinfo->getFullURL() .'" class="message';
		if($type == "message"){
			$output .=' on';
		}
		$output .='"><i></i>我的信息</a></li>
	                    	<li><a href="'. $UploadAvatar->getFullURL() .'" class="portrait';
		if($type == "portrait"){
			$output .=' on';
		}
		$output .='"><i></i>修改头像</a></li>
	                    	<li><a href="'. $UserPrivacy->getFullURL() .'" class="secret';
		if($type == "secret"){
			$output .=' on';
		}
		$output .='"><i></i>隐私</a></li>
	                    </ul>
					</div>
				</div>';

		return $output;
	}

	public function getRightSection(){
		$output = '
		<div class="col-md-9 pag-hor-20">
					<div class="setting-r">
						<h3 class="setting-tit web-hide">账号安全</h3>
						<div class="account-con">
                            <ul>
                                <li>
                                    <span>绑定手机：</span>
                                    <div>';
		if($this->bindmobileflag){
			$UpdateMobile = SpecialPage::getSafeTitleFor('UpdateMobile');
			$output .= '                <span>';
				$output .= preg_replace('/(1\d{1,2})\d\d(\d{0,3})/','\1*****\3',$this->mobile);
				$output .='             </span>
                                        <span class="binded"><i class="fa fa-check-circle"></i>已绑定</span>
                                        <a href="'. $UpdateMobile->getFullURL() .'" class="fn-right">更改号码</a>';
		}else{
			$BindMobile = SpecialPage::getSafeTitleFor('BindMobile');
			$output .= '<span><a href="'. $BindMobile->getFullURL() .'">绑定手机</a></span>';
		}
		$ModifyPassWord = SpecialPage::getSafeTitleFor('ModifyPassWord');
		$output .= '			</div>
                                </li>';
		if($this->bindmobileflag){
			$output .= '
                                <li>
                                    <span>密码：</span>
                                    <div>
                                    	<span></span>
                                        <a href="'. $ModifyPassWord->getFullURL() .'">&nbsp;&nbsp;&nbsp;修改密码</a>
                                    </div>
                                </li>';
		}

			$output .= '
                                <li>
                                    <span>账号绑定：</span>
                                    <div class="sina-con con-icon">
                                        <span><i class="sina"></i>新浪微博</span>';
			if($this->bindsinaflag){
				if($this->bindmobileflag||$this->bindqqflag) {
					$output .= '<span class="binded"><i class="fa fa-check-circle"></i>已绑定</span>
                                        <span class="jc-bind"><a href="javascript:;" class="unbindthird" data-type="sinaweibo">解除绑定</a></span>';
				}else{
					$output .= '<span class="binded"><i class="fa fa-check-circle"></i>已绑定</span><span class="jc-bind"><a href="javascript:;" class="nounbindthird">解除绑定</a></span>';
				}
			}else{
				$output .= '<span class="binding"><a href="'.$this->bindsinaweibourl.'">现在绑定</a></span>';
			}
			$output .= '
                                    </div>
                                </li>';


		$output .= '
                                <li>
                                    <span></span>
                                    <div class="qq-con con-icon">
                                        <span><i class="qq"></i>QQ</span>';

			if($this->bindqqflag){
				if($this->bindsinaflag ||$this->bindmobileflag) {
					$output .= '<span class="binded"><i class="fa fa-check-circle"></i>已绑定</span>
                                        <span class="jc-bind"><a href="javascript:;" class="unbindthird" data-type="qq">解除绑定</a></span>';
				}else{
					$output .= '<span class="binded"><i class="fa fa-check-circle"></i>已绑定</span><span class="jc-bind"><a href="javascript:;" class="nounbindthird">解除绑定</a></span>';
				}
			}else{
				$output .= '<span class="binding"><a href="'.$this->bindqqurl.'">现在绑定</a></span>';
			}

			$output .= '
                                    </div>
                                </li>';


		$output .= '
                            </ul>
                        </div>
					</div>
				</div>
		';
		return $output;
	}

}
