<?php
/**
 * A special page for SpecialModifyPassWord
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialModifyPassword extends SpecialPage {

    /**
     * Constructor
     */
    public function __construct() {
        SpecialPage::__construct( 'ModifyPassword');
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
            $out->redirectHome('Special:ModifyPassword');
            return false;
        }

        $out->addModuleStyles(array(
            'ext.socialprofile.userprofile.useraccount.css',
            'ext.socialprofile.userprofile.usercentercommon.css'
        ));
//        $out->addModuleStyles('ext.socialprofile.userprofile.useraccount.css');
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
            <div class="col-md-9 pag-hor-20">
                <div class="setting-r">
                    <h3 class="setting-tit web-hide">修改密码</h3>
                    <div class="change-password-con">
                        <div>
                          <span>旧密码：</span>
                          <input type="password" name="oldpwd" id="oldpwd" maxlength="16">
                        </div>
                        <div>
                          <span>新密码：</span>
                          <input type="password" name="pwd" id="pwd" maxlength="16">
                        </div>
                        <div>
                          <span>再次输入：</span>
                          <input type="password" name="repeatpwd" id="repeatpwd" maxlength="16">
                        </div>
                        <div class="btn-con">
                          <button class="btn-sure" id="modifypassword">提交</button>
                          <a class="btn-cancle" href="/home/特殊:账号安全">取消</a>
                        </div>
                    </div>
                </div>
            </div>
		';
        return $output;
    }

}
