<?php
/**
 * A special page for viewing all relationships by type
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=1 (viewing friends)
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=2 (viewing foes)
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use Joyme\page\Page;
class SpecialViewFollows extends SpecialPage {
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ViewFollows' );
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
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgUser,$wgWikiname,$wgUserCenterUrl,$wgOut,$wgEnv;
		
		
		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		// Set the page title, robot policies, etc.
		
		$out->redirectHome('Special:ViewFollows');
		$wgOut->redirect('http://uc.joyme.'.$wgEnv.'/usercenter/home');
		return false;
		$this->setHeaders();
		// Add CSS
		$out->addModuleStyles( array(
				'ext.socialprofile.userprofile.usercentercommon.css',
				'ext.socialprofile.useruserfollows.css' 
				)
		);
		
		// Add JS
		$out->addModules( 'ext.socialprofile.useruserfollows.js');
		$out->addModuleScripts( 'ext.RecommendUser.js');
		
		$out->addModules( 'mediawiki.action.view.postEdit' );
		$output = '';
		/**
		 * Get query string variables
		 */
		$user_name = $request->getVal( 'user' );
		$rel_type = $request->getInt( 'rel_type' ,1);
		$page = $request->getInt( 'page' ,1);
		
		if ( !$user_name ) {
			$user_name = $user->getName();
		}
		$user_id = User::idFromName( $user_name );
		
		$jwuser = new JoymeWikiUser();
		$profileid = $jwuser->getProfileid($user_id);
		$url = $wgUserCenterUrl.$profileid;
		//header("Location: ".$url);
		$wgOut->redirect($url,'301');
		return false;
		
		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewRelationships page
		 */
		if ( !$user->isLoggedIn()) {
			$out->setPageTitle( $this->msg( 'ur-error-page-title' )->plain() );
			$login = SpecialPage::getTitleFor( 'Userlogin' );
			$out->redirect( htmlspecialchars( $login->getFullURL( 'returnto=Special:ViewFollows' ) ) );
			return false;
		}
		/**
		 * Set up config for page / default values
		 */
		if ( !$page || !is_numeric( $page ) ) {
			$page = 1;
		}
		$rel_type = $rel_type==1?1:2;
		
		
		/**
		 * If no user is set in the URL, we assume its the current user
		 */
		
		/*if($user_name && $user_name != $user->getName()){
			$out->setPageTitle( $this->msg( 'ur-error-title' )->plain() );
			$output = '<div class="col-md-9">
                <div id="main">' .
					'sorry,目前您只能看自己的好友关系' .
				'</div>
			</div>';
			$out->addHTML( $output );
			return false;
		}*/
		if ( !$user_name ) {
			$user_name = $user->getName();
		}
		$user_id = User::idFromName( $user_name );
		$target_user = User::newFromId( $user_id );
		
		$url = $this->getPageTitle()->getLocalUrl('user='.$user_name.'&rel_type='.$rel_type);
		
		/**
		 * Error message for username that does not exist (from URL)
		 */
		if ( $user_id == 0 ) {
			$out->setPageTitle( $this->msg( 'ur-error-title' )->plain() );
			$output = '<div class="col-md-9">
                <div id="main">' .
					$this->msg( 'ur-error-message-no-user' )->plain() .
				'</div>
			</div>';
			$out->addHTML( $output );
			return false;
		}
		/**
		 * Get all relationships
		 */
		$stats = new UserStats( $target_user->getID(), $target_user->getName() );
		$stats_data = $stats->getUserStats();

		$limit = 10;
		
		$uuf = new UserUserFollow();
		$follows = $uuf->getFollowList( $user_id, $rel_type, $limit, $page);
		
		$fans = $stats_data['foe_count'];
		$followingCount = $stats_data['friend_count'];
		
		$total = $rel_type==1?$followingCount:$fans;
		
		$back_link = Title::makeTitle( NS_USER, $user_name );
		$target = SpecialPage::getTitleFor('ViewFollows');
		$jcontribution = SpecialPage::getTitleFor('JContribution');
		$blast = SpecialPage::getTitleFor('BoardList');
		
		$query1 = array('user' => $user_name, 'rel_type' => 1);
		$query2 = array('user' => $user_name, 'rel_type' => 2);
		
		$output = '<!-- 左侧区域 开始 -->
            <div class="col-md-9">
                <div id="main">
                    <div class="follow-list-box ">';
		
		if($user_id == $user->getId()){
			$btn_str = '我';
		}else{
			$btn_str = '他';
		}
		
		if ( $rel_type == 1 ) {
			$output.='<h1 class="page-h1 pag-hor-20 fn-clear">'.$user_name.'已经关注了<i '.($btn_str=='我'?'id="user-follower-count_top"':'').'>'.$followingCount.'</i>位好友</h1>';
			$out->setPageTitle( $this->msg( 'ur-title-friend', $user_name )->parse() );
			
			$output.='<div class="letter-tit  web-common-tit web-show ">
				'.Linker::LinkKnown($target, $btn_str.'的关注', array('class'=>'follow on'), $query1).'
				'.Linker::LinkKnown($target, $btn_str.'的粉丝', array('class'=>'fans'), $query2).'
            </div>';
		} else {
			$output.='<h1 class="page-h1 pag-hor-20 fn-clear">'.$user_name.'共有<i '.($btn_str=='我'?'id="user-fans-count_top"':'').'>'.$fans.'</i>位粉丝</h1>';
			$out->setPageTitle( $this->msg( 'ur-title-foe', $user_name )->parse() );
			
			$output.='<div class="letter-tit  web-common-tit web-show ">
				'.Linker::LinkKnown($target, $btn_str.'的关注', array('class'=>'follow'), $query1).'
				'.Linker::LinkKnown($target, $btn_str.'的粉丝', array('class'=>'fans on'), $query2).'
            </div>';
		}
		
		
		if($rel_type == 1 && $user_id == $user->getId() ){
			$model = new RecommendUsers();
			$manitos = $model->getUserInfo( $user_id );
			if (count($manitos)>=4) {
				$output .= '<!--大神推荐  开始-->
                     <div class="no-follow web-show">
						<div class="int-tj fn-clear pag-hor-20">
                        <h3>大神推荐 <cite class="change-icon fn-right" id="recommend_change"><i></i>换一换</cite></h3>
                        <ul class="int-tj-list" id="recommend_list">';
				$i = 0;
				foreach ($manitos as $manito) {
					$manuserPage = htmlspecialchars(Title::makeTitle( NS_USER, $manito['nick'] )->getFullURL());
					$follow_centent = $manito['is_follow']==1?'<span class="followed"><i class="fa fa-check"></i>已关注</span>': '<span class="user-recommend-follow" data-uid="'.$manito['uid'].'"><i class="fa fa-plus" aria-hidden="true" ></i>关注</span>';
					
					if($i<4){
						$stylestr = 'style="display:block;"';
					}else{
						$stylestr = 'style="display:none;"';
					}
					$i++;
					
					$output .= '<li '.$stylestr.'>
								<div class="int-tj-l">
	                            	<cite><a href="' . $manuserPage . '"><img src="' . $manito['icon'] . '" alt="img"></a></cite>
	                            </div>
	                            <div class="int-tj-r">
	                                <font>' . $manito['nick'] . '</font>
	                                <b>' . mb_substr($manito['brief'],0,5,"UTF-8") . '</b>
	                                '.$follow_centent.'
	                            </div>
	                        </li>';
				}
		
				$output .= '</ul>
	                    </div>
					</div>
	                <!--大神推荐  结束-->';
			}
		}
		
		
		if ( $follows ) {
			$output.='<ul class="list-item ">';
			$output .= $uuf->displayFollowList($user_id, $rel_type, $follows);
			$output .='</ul>';
		}else if($user_id != $user->getId()){
			$output .='<div class="no-data">
                                    <cite class="no-data-img"></cite>
                                    <p></p>
                                </div>';
		}else if($rel_type == 2){
			$output .='<div class="no-data">
                                    <cite class="no-data-img"></cite>
                                    <p></p>
                                </div>';
		}else{
			$output .='<div class="no-data web-hide">
                                    <cite class="no-data-img"></cite>
                                    <p></p>
                                </div>';
		}
		$output .='</div>';
		
		
		
		if ( $total>$limit) {
			$_page = new Page(array(
				'page_name'=>'page',
				'total' => $total,
				'perpage'=>$limit,
				'nowindex'=>$page,
				'pagebarnum'=>10,
				'url'=>$url,
				'classname'=>array('main_page'=>'boardpage paging','active'=>'on')
				)
			);
			$page_str = $_page->show(2);
			$output.=$page_str;
		}
		$output .='</div>
            </div>';
		
		//右侧区域
		
		$profile = new JoymeWikiUser();
		$userprofile = $profile->getProfile($user_id);
		$userprofile = $userprofile[0];
		
		if ($userprofile['sex'] == '1'){
			$genderIcon = 'man';
		} elseif ($userprofile['sex'] == '0'){
			$genderIcon = 'female';
		} else {
			$genderIcon = '';
		}
		
		$output .='<div class="col-md-3 web-hide ">
                <div id="sidebar">
                    <div class="user-mess-box"> 
                        <div class="user-int-mess">
                            <a href="'.htmlspecialchars( $back_link->getFullURL() ).'"><img src="'.$userprofile['icon'].'"></a>
                            <font class="nickname">'.$user_name.'</font>
                            <i class="user-sex '.$genderIcon.'"></i>
                        </div>
                        <div class="user-messing-situ ">
                           	'.Linker::LinkKnown($target, '<i '.($btn_str=='我'?'id="user-follower-count"':'').'>'.$followingCount.'</i></br>关注', array(), $query1).'
                           	'.Linker::LinkKnown($target, '<i '.($btn_str=='我'?'id="user-fans-count"':'').'>'.$fans.'</i></br>粉丝', array(), $query2).'
                        </div>
                    </div>
                </div>
            </div>';
		
		//$user_id
		$output .='<input type="hidden" id="user_id" name="user_id" value="' . $user_id . '"/>';
		$output .='<input type="hidden" id="rel_type" name="rel_type" value="' . $rel_type . '"/>';
		
		$out->addHTML( $output );
	}
}