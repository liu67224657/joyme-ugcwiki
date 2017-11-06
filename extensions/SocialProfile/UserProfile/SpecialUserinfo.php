<?php
/**
 * A special page for userinfo
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialUserinfo extends SpecialPage {
	public $icon;
	public $brief;
	public $sex;
	public $birthday;
	public $proviceid;
	public $interest;

	/**
	 * Constructor
	 */
	public function __construct() {
		SpecialPage::__construct( 'Userinfo');
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
			$out->redirectHome('Special:Userinfo');
			return false;
		}

		$out->addModuleStyles(array(
			'ext.socialprofile.userprofile.useraccount.css',
			'ext.socialprofile.userprofile.edit-select.css',
			'ext.socialprofile.userprofile.usercentercommon.css'
		));

		$out->addModuleScripts(array(
			'ext.socialprofile.userprofile.useraccount.js',
		));


		// If the user isn't logged in, display an error
		if ( !$user->isLoggedIn() ) {
			$this->displayRestrictionError();
			return;
		}

		$accountsecurity = new SpecialAccountSecurity();
		$accountsecurity->initData();

		$this->sex = $accountsecurity->sex;
		$this->brief = $accountsecurity->brief;
		$this->interest = $accountsecurity->interest;
		$this->proviceid = $accountsecurity->proviceid;
		$this->birthday = $accountsecurity->birthday;

		$out->addHTML( '<!-- 内容区域 开始 -->
			<div class="container">
				<div class="row">
					<div class="setting-con">
				');

		$out->addHTML( $accountsecurity->getLeftSection("message") );
		$out->addHTML( $this->getRightSection($user->mName) );

		$out->addHTML( '			
					</div>
				</div>
			</div>
			<!-- 内容区域 结束 -->' );
	}


	public function getRightSection($username){

		$output = '<div class="col-md-9 pag-hor-20">
					<div class="setting-r">
						<h3 class="setting-tit web-hide">我的信息</h3>
						<div class="message-con">
                            <div>
                                <span>昵称：</span>
                                <div>';

		if(!empty($username)){
			$output .= $username;
		}else{
			$output .= '<input type="text" name="nick" ><i  class="warning">*昵称只能修改一次</i>';
		}

		$output .= '
                                </div>
                            </div>
                            <div>
                                <span class="sign">我的签名：</span>
                                <div>
                                    <textarea name="brief" maxlength="16" id="brief" >'. $this->brief .'</textarea>
                                </div>
                            </div>
                            <div>
                                <span>性别：</span>
                                <div>
                                    <div class="select-area">
                                        <div class="select-ele">
                                            <span class="select-value">请选择</span>
                                            <i class="fa fa-angle-down"></i>
                                        </div>
                                        <select name="sex" id="sex">';
								if(is_null($this->sex)){
									$output .= '<option>请选择</option>';
								}
							$output .= '
                                            <option value="1" ';
								if($this->sex == "man" ){
									$output .= 'selected="selected"';
								}
								$output .= '>男</option>
                                            <option value="0" ';
								if($this->sex == "female" ){
									$output .= 'selected="selected"';
								}
								$output .= '>女</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span>出生年月：</span>
                                <div>
                                    <div class="form-group">
                                        <div class="input-group input-append date" id="datetimepicker10">
                                            <input type="text" class="form-control" id="birthday" value="'. $this->birthday .'"/>
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar">
                                                  </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span>所在地：</span>
                                <div>
                                    <div class="select-area">
                                        <div class="select-ele">
                                            <span class="select-value">请选择</span>
                                            <i class="fa fa-angle-down"></i>
                                        </div>
                                        <select name="proviceid" id="proviceid">
                                        	<option>请选择</option>';
										foreach (JoymeSite::$provices as $k => $v){
											$output .= '<option value="'.$k.'" ';
											if($this->proviceid == $k){
												$output .= 'selected="selected"';
											}
											$output .= '>'.$v.'</option>';
										}
								$output .= '
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span>兴趣：</span>
                                <div>
                                   <input type="text" name="interest" id="interest" value="'. $this->interest .'">
                                </div>
                            </div>
                        <button class="web-hide btn-sure userinfosave">保存</button>
                        <button class="web-show btn-sure userinfosave">保存</button>
                        </div>
					</div>
				</div>';


		return $output;
	}

}
