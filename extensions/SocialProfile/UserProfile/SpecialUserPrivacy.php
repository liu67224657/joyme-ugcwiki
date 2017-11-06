<?php
/**
 * A special page for UserPrivacy
 *
 * @file
 * @ingroup Extensions
 * @author gradydong
 * @copyright Copyright © 2016, joyme.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialUserPrivacy extends SpecialPage {
	public $icon;
	public $brief;
	public $sex;
	public $is_attention;
	public $is_secretchat;

	/**
	 * Constructor
	 */
	public function __construct() {
		SpecialPage::__construct( 'UserPrivacy');
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
			$out->redirectHome('Special:UserPrivacy');
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
		$out->addModuleScripts(array(
			'ext.socialprofile.userprofile.useraccount.js',
		));


		if ( $request->wasPosted() ) {
			$joymewikiuser = new JoymeWikiUser();
			$a_remind = $request->getVal('a_remind');
			if($a_remind){
				$joymewikiuser->delUserRemindSet($user->getId(),'echo-subscriptions-web-article-cite-my');
			}else{
				$joymewikiuser->userRemindSet($user->getId(),'echo-subscriptions-web-article-cite-my');
			}
			$comment_remind = $request->getVal('comment_remind');
			if($comment_remind){
				$joymewikiuser->delUserRemindSet($user->getId(),'echo-subscriptions-web-article-comments');
			}else{
				$joymewikiuser->userRemindSet($user->getId(),'echo-subscriptions-web-article-comments');
			}
			$like_remind = $request->getVal('like_remind');
			if($like_remind){
				$joymewikiuser->delUserRemindSet($user->getId(),'echo-subscriptions-web-article-thumb-up');
			}else{
				$joymewikiuser->userRemindSet($user->getId(),'echo-subscriptions-web-article-thumb-up');
			}
			$attention_remind = $request->getVal('attention_remind');
			if($attention_remind){
				$joymewikiuser->delUserRemindSet($user->getId(),'echo-subscriptions-web-article-consider-me');
			}else{
				$joymewikiuser->userRemindSet($user->getId(),'echo-subscriptions-web-article-consider-me');
			}
			$sysmsg_remind = $request->getVal('sysmsg_remind');
			if($sysmsg_remind){
				$joymewikiuser->delUserRemindSet($user->getId(),'echo-subscriptions-web-echo-system-message');
			}else{
				$joymewikiuser->userRemindSet($user->getId(),'echo-subscriptions-web-echo-system-message');
			}


			$useradd = array(
				'user_id' => $user->getId()
			);
			$is_attention = $request->getInt('is_attention');
			if($is_attention){
				$useradd['is_attention'] = $is_attention;
			}else{
				$useradd['is_attention'] = 0;
			}
			$is_secretchat = $request->getInt('is_secretchat');
			if($is_secretchat){
				$useradd['is_secretchat'] = $is_secretchat;
			}else{
				$useradd['is_secretchat'] = 0;
			}

			$joymewikiuser->editUserAddition($useradd);
			$this->getOutput()->redirect( SpecialPage::getSafeTitleFor('UserPrivacy') );

		}else{
			$accountsecurity = new SpecialAccountSecurity();
			$accountsecurity->initData();

			$this->is_attention = $accountsecurity->is_attention;
			$this->is_secretchat = $accountsecurity->is_secretchat;


			$out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');

			$out->addHTML( $accountsecurity->getLeftSection("secret") );
			$out->addHTML( $this->getRightSection($user->mId) );

			$out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );
		}

	}


	public function getRightSection($user_id){
		$joymewikiuser = new JoymeWikiUser();
		$remindsets = $joymewikiuser->getUserRemindSet($user_id);

		if($remindsets){
			$up_propertys = array_column($remindsets,'up_property');
			$upp = array();
			foreach ($up_propertys as $up_property){
				if($up_property == 'echo-subscriptions-web-article-cite-my'){
					$upp['a_remind'] = 'echo-subscriptions-web-article-cite-my';
				}
				elseif ($up_property == 'echo-subscriptions-web-article-comments'){
					$upp['comment_remind'] = 'echo-subscriptions-web-article-comments';
				}
				elseif ($up_property == 'echo-subscriptions-web-article-thumb-up'){
					$upp['like_remind'] = 'echo-subscriptions-web-article-thumb-up';
				}
				elseif ($up_property == 'echo-subscriptions-web-article-consider-me'){
					$upp['attention_remind'] = 'echo-subscriptions-web-article-consider-me';
				}
				elseif ($up_property == 'echo-subscriptions-web-echo-system-message'){
					$upp['sysmsg_remind'] = 'echo-subscriptions-web-echo-system-message';
				}
			}
		}

		$output = '
			<div class="col-md-9 pag-hor-20">
				<div class="setting-r">
					<h3 class="setting-tit web-hide">隐私</h3>
					<div class="secret-con">
						<form action="" method="post" style="width: auto;margin: 0;text-align: left;"> 
						<h4>提醒设置</h4>
						<span>
							<input type="checkbox" name="a_remind" id="a_remind" value="1" ';
		if(!isset($upp['a_remind'])){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="a_remind"><i></i>接受@我的提醒</label>
						</span>
						<span>
							<input type="checkbox" name="comment_remind" id="comment_remind" value="1"';

		if(!isset($upp['comment_remind'])){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="comment_remind"><i></i>接受评论&回复的提醒</label>
						</span>
						 <span>
							<input type="checkbox" name="like_remind" id="like_remind" value="1" ';

		if(!isset($upp['like_remind'])){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="like_remind"><i></i>接受点赞的提醒</label>
						</span>
						<span>
							<input type="checkbox" name="attention_remind" id="attention_remind" value="1" ';

		if(!isset($upp['attention_remind'])){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="attention_remind"><i></i>接受关注的提醒</label>
						</span>
						 <span>
							<input type="checkbox" name="sysmsg_remind" id="sysmsg_remind" value="1" ';
		if(!isset($upp['sysmsg_remind'])){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="sysmsg_remind"><i></i>接受系统通知的提醒</label>
						</span>
						<h4>功能</h4>
						<span>
							<input type="checkbox" name="is_attention" id="is_attention" value="1"';
		if(isset($this->is_attention)
			&&$this->is_attention
		){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="is_attention"><i></i>允许他人关注</label>
						</span>
						<span>
							<input type="checkbox" name="is_secretchat" id="is_secretchat" value="1" ';

		if(isset($this->is_secretchat)
			&&$this->is_secretchat
		){
			$output .= ' checked="checked" ';
		}
		$output .= '>
							<label for="is_secretchat"><i></i>允许他人私信</label>
						</span>
						<button class="web-hide btn-sure" type="submit">保存</button>
					</form>
					</div>
				</div>
			</div>
		';
		return $output;
	}

}
