<?php
/**
 * A special page for SpecialBindMobile
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialBindMobile extends SpecialPage {


    /**
     * Constructor
     */
    public function __construct() {
        SpecialPage::__construct( 'BindMobile');
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
            $out->redirectHome('Special:BindMobile');
            return false;
        }

//        $out->addModuleStyles('ext.socialprofile.userprofile.useraccount.css');
        $out->addModuleStyles(array(
            'ext.socialprofile.userprofile.useraccount.css',
            'ext.socialprofile.userprofile.usercentercommon.css'
        ));
        $out->addModuleScripts('ext.socialprofile.userprofile.useraccount.js');

        // If the user isn't logged in, display an error
        if ( !$user->isLoggedIn() ) {
            $this->displayRestrictionError();
            return;
        }
        $accountsecurity = new SpecialAccountSecurity();
        $accountsecurity->initData();

        $out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');

        $out->addHTML( $accountsecurity->getLeftSection() );
        $out->addHTML( $this->getRightSection() );

        $out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );
    }

    public function getRightSection(){
        $output = '
        <script src="http://captcha.luosimao.com/static/dist/api.js"></script>
            <div class="col-md-9 pag-hor-20">
                <div class="setting-r">
                    <h3 class="setting-tit web-hide">绑定手机</h3>
                    <div class="change-password-con">
                        <div>
                          <span>手机号：</span>
                          <input type="text" name="mobile" id="bmtel" maxlength="11">
                        </div>
                         <div>
                          <div class="captcha-box">
                            <div class="l-captcha" data-site-key="533a7e232fb9134c30928ceebad087ef" data-width="250" data-callback="getResponse"></div>         
                         </div>
                        </div>
                        <div>
                          <span>验证码：</span>
                          <input type="text" class="w-130 test-code" name="mobilecode" id="bmmobilecode" maxlength="6">
                          <input type="hidden" name="lsmresponse" id="lsmresponse">
                          <button class="send-code sendVerifyRegMobileCode on" data-sendtype="bindmobile" disabled="disabled" id="sendCode">发送验证码</button>
                        </div>
                        <div>
                          <span>密码：</span>
                          <input type="password" name="password" id="bmpassword" maxlength="16">
                        </div>
                        <div>
                          <span>再次输入：</span>
                          <input type="password" name="repassword" id="bmrepassword" maxlength="16">
                        </div>
                        <div class="btn-con">
                          <button class="btn-sure" type="button" id="bindmobile">绑定</button>
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

}
