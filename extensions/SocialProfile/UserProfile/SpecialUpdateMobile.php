<?php
/**
 * A special page for UpdateMobile
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialUpdateMobile extends SpecialPage {

	public $username;
	public $user_id;
	public $icon;
	public $brief;
	public $mobile;
	public $sex;


	/**
	 * Constructor
	 */
	public function __construct() {
		SpecialPage::__construct( 'UpdateMobile');
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
			$out->redirectHome('Special:UpdateMobile');
			return false;
		}

		$out->addModuleStyles(array(
			'ext.socialprofile.userprofile.useraccount.css',
			'ext.socialprofile.userprofile.usercentercommon.css'
		));
//		$out->addModuleStyles('ext.socialprofile.userprofile.useraccount.css');
		$out->addModuleScripts('ext.socialprofile.userprofile.useraccount.js');

		// If the user isn't logged in, display an error
		if ( !$user->isLoggedIn() ) {
			$this->displayRestrictionError();
			return;
		}

		$accountsecurity = new SpecialAccountSecurity();
		$accountsecurity->initData();
		$this->mobile = $accountsecurity->mobile;
		$this->username = $user->mName;
		$this->user_id = $user->mId;

		$out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');

		$out->addHTML( $accountsecurity->getLeftSection() );
		$step = $request->getInt( 'step' );
		$token = $request->getVal( 'token' );
		if($step){
			if($token == md5($this->mobile.$this->user_id)){
				if($step == 2){
					$out->addHTML( $this->step2() );
				}
				elseif($step == 3){
					$out->addHTML( $this->step3() );
				}else{
					$out->addHTML( $this->step1() );
				}
			}else{
				$out->addHTML( $this->step1() );
			}
		}else{
			$out->addHTML( $this->step1() );
		}
		$out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );
	}


	/**
	 * 验证账号
	 */
	public function step1(){
		$output = '
			<script src="http://captcha.luosimao.com/static/dist/api.js"></script>
				<div class="col-md-9 pag-hor-20">
					<div class="setting-r">
						<h3 class="setting-tit web-hide">修改绑定的手机号</h3>
						<div class="change-num-con">
                            <div class="chanhe-tit fn-clear">
                                <a href="javascript:;" class="on" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>1</cite>
                                    验证账号
                                </a>
                                <a href="javascript:;" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>2</cite>
                                    修改手机号码
                                </a>
                                <a href="javascript:;" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>3</cite>
                                    更换成功
                                </a>
                            </div>
                            <div class="chanhe-con">
                                <div class="step-1 on ">
                                    <p>我们将向下方的手机号发送验证码，请在下方输入您收到的验证码</p>
                                    <div>
										<div class="captcha-box">
											<div class="l-captcha" data-site-key="533a7e232fb9134c30928ceebad087ef" data-width="250" data-callback="getResponse"></div>         
										</div>
									</div>
                                    <div>
                                        <span>验证方式：</span>
                                        <cite>使用<b>';
			if($this->mobile){
				$output .= preg_replace('/(1\d{1,2})\d\d(\d{0,3})/','\1*****\3',$this->mobile);
			}

		$output .= '</b>验证</cite>
                                        <button class="send-code sendVerifyMobileCode on" data-mobileid="" disabled="disabled" id="sendCode">发送验证码</button><input type="hidden" name="lsmresponse" id="lsmresponse">
                                    </div>
                                    <div>
                                        <span>验证码：</span>
                                        <input type="text" class="w-130 test-code" name="mobilecode" id="oldmobilecode" maxlength="6">
                                    </div>
                                    <input type="hidden" name="step" value="2">
                                    <button class="next-icon" id="umstep1" data-step="1">下一步</button>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
				<script>
            function getResponse(response){
                $("#lsmresponse").val(response);
                $("#sendCode").removeClass("on");
                $("#sendCode").attr("disabled",false);
            }
            </script>
				';
		return $output;
	}

	/**
	 * 输入更换手机号码
	 */
	public function step2(){
		$output = '
			<script src="http://captcha.luosimao.com/static/dist/api.js"></script>
				<div class="col-md-9 pag-hor-20">
					<div class="setting-r">
						<h3 class="setting-tit web-hide">修改绑定的手机号</h3>
						<div class="change-num-con">
                            <div class="chanhe-tit fn-clear">
                                <a href="javascript:;" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>1</cite>
                                    验证账号
                                </a>
                                <a href="javascript:;"  class="on">
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>2</cite>
                                    修改手机号码
                                </a>
                                <a href="javascript:;" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>3</cite>
                                    更换成功
                                </a>
                            </div>
                            <div class="chanhe-con">
                                <div class="step-2 on ">
                                    <p>请填写新的手机号</p>
                                    <div>
										<div class="captcha-box">
											<div class="l-captcha" data-site-key="533a7e232fb9134c30928ceebad087ef" data-width="250" data-callback="getResponse"></div>         
										</div>
									</div>
                                    <div>
                                        <span>手机号：</span>
                                        <input type="text" name="newmobile" id="newuptel" class="w-130">
                                        <button class="send-code sendVerifyRegMobileCode on" data-sendtype="upnewmobile" disabled="disabled" id="sendCode">发送验证码</button><input type="hidden" name="lsmresponse" id="lsmresponse">
                                    </div>
                                    <div>
                                        <span>验证码：</span>
                                        <input type="text" class="w-130 test-code" name="mobilecode" id="newupmobilecode" maxlength="6">
                                    </div>
                                    <button class="next-icon" id="umstep2" data-step="2">下一步</button>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
				<script>
            function getResponse(response){
                $("#lsmresponse").val(response);
                $("#sendCode").removeClass("on");
                $("#sendCode").attr("disabled",false);
            }
            </script>
				';
		return $output;
	}

	/**
	 * 输出成功信息
	 */
	public function step3(){

		$output = '<div class="col-md-9 pag-hor-20">
					<div class="setting-r">
						<h3 class="setting-tit web-hide">修改绑定的手机号</h3>
						<div class="change-num-con">
                            <div class="chanhe-tit fn-clear">
                                <a href="javascript:;">
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>1</cite>
                                    验证账号
                                </a>
                                <a href="javascript:;" >
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>2</cite>
                                    修改手机号码
                                </a>
                                <a href="javascript:;"  class="on">
                                    <cite><i class="web-hide"></i><i class="fa fa-check web-show"></i>3</cite>
                                    更换成功
                                </a>
                            </div>
                            <div class="chanhe-con">
                                <div class="setp-3  on ">
                                    <div>
                                        <p>恭喜您，已经成功更换绑定手机！</p>
                                        <font>您的新手机号：<b>';
		if($this->mobile){
			$output .= preg_replace('/(1\d{1,2})\d\d(\d{0,3})/','\1*****\3',$this->mobile);
		}
						$output .=   '</b></font>
                                        <a class="finish-icon" href="/home/特殊:账号安全">完成</a>
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>';
		return $output;
	}

}
