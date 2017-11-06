<?php
/**
 * A special page to allow users to send a mass board message by selecting from
 * a list of their friends and foes
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use Joyme\page\Page;
class SpecialBoardList extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'BoardList' );
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgWikiname,$wgEnv;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// This feature is available only to logged-in users.
		if ( !$user->isLoggedIn() ) {
			$out->setPageTitle( $this->msg( 'boardblastlogintitle' )->plain() );
			$out->addWikiMsg( 'boardblastlogintext' );
			$out->redirect('http://wiki.joyme.'.$wgEnv);
			return false;
		}
		
		$out->redirectHome('Special:BoardList');

		// Is the database locked?
		if ( wfReadOnly() ) {
			$out->readOnlyPage();
			return false;
		}

		// Blocked through Special:Block? No access for you!
		if ( $user->isBlocked() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		// Add CSS & JS
		$out->addModuleStyles( array(
			'ext.socialprofile.userprofile.usercentercommon.css',
			'ext.socialprofile.clearfix',
			'ext.socialprofile.userboard.boardlist.css',
			'ext.socialprofile.userboard.headskin.css'
		) );
		$out->addModules( 'ext.socialprofile.userboard.boardlist.js' );

		$output = '';

		$out->setPageTitle( $this->msg( 'boardlisttitle' )->plain() );
		$output .= $this->displayList();

		$out->addHTML( $output );
	}

	/**
	 * Displays the board list
	 */
	function displayList(){
		global $wgUserBoardWebSocketUrl,$wgUserCenterUrl;
		$request = $this->getRequest();

		$page = $request->getVal( 'page' ,1);
		$type = $request->getVal( 'type' ,1);
		$type = $type==1?1:2;
		
		$limit = 10;
		
		$url = $this->getPageTitle()->getLocalUrl('type='.$type);
		
		$total = $this->getBoardCount($type);
		
		if($page<1){
			$page = 1;
		}

		//左侧数据
		$output = '<div class="col-md-9">
                <div id="main">
                    <div class="sixin-list-box ">
                        <h1 class="page-h1 pag-hor-20 fn-clear web-hide">'.($type==1?'私信':'未关注人私信').' <span class="del-all fn-right" id="clearBoardAll"><i class="fa fa-trash-o"></i>清空所有</span></h1>
                        <div class="letter-tit  web-common-tit web-show ">
							 <a href="'.$this->getPageTitle()->getLocalUrl('type=1').'" class="lettering '.($type==1?'on':'').'">私信</a>
                            <a href="'.$this->getPageTitle()->getLocalUrl('type=2').'" class="unletter '.($type==1?'':'on').'">未关注人私信</a>
                        </div>
						<ul class="sixin-list list-item " id="board_list">';

		//获取私信列表
		$list = $this->getBoardList($type,$limit,$page);
		
		if($list){
			foreach ($list as $v){
				if($v->ub_msg_count > 0){
					$msg_count_str = '<i class="news-count '.($v->ub_msg_count>99?'on':'').'">'.($v->ub_msg_count>99?99:$v->ub_msg_count).'</i>';
				}else{
					$msg_count_str = '';
				}
				$msg ='<li id="board_uid_'.$v->ub_friend_id.'">
								<a target="_blank" href="'.UserBoard::getUserBoardToBoardURL( $v->ub_friend_id ).'">
	                                <div class="list-item-l userinfo" data-username="'.$v->profileid.'">
	                                    <cite class="board-headicon"><img src="'.$v->url.'">'.$msg_count_str.($v->vtype>0?'<span class="user-vip" title="'.$v->vdesc.'"></span>':'').'<span class="dianzan-def focus-dec-0'.$v->headskin.'"></span></cite>
	                                </div>
	                                <div class="list-item-r">
										
	                                    <div class="item-r-name fn-clear">
	                                        <span class="fn-left userinfo" data-username="'.$v->profileid.'">'.$v->ub_friend_name.'</span>
	                                        <b class="time-stamp fn-right">'.$v->ub_date.'</b>
	                                    </div>
	                                    <div class="item-r-text">
	                                       	'.$v->ub_message.'
	                                    </div>
	                                </div>
	                             </a>
	                             <i class="del-icon" data-uid="'.$v->ub_friend_id.'"></i>
	                          </li>';
				$output.=$msg;
				//$output.='<li id="board_uid_'.$v->ub_friend_id.'"><a href="'.UserBoard::getUserBoardToBoardURL( $v->ub_friend_id ).'"><span class="board_count">'.$v->ub_msg_count.'</span><br/>'.$v->ub_friend_id.' - '.$v->ub_date.'<br/><span class="board_msg">'.$v->ub_message.'</span></a><a class="delmsg" href="javascript:;" data-uid="'.$v->ub_friend_id.'">删除</a></li>';
			}
		}else{
			$output.='<div class="no-data">
                                    <cite class="no-data-img"></cite>
                                    <p></p>
                                </div>';
		}
		$output.='</ul>
                    </div>';
		if($total > $limit){
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
		$output.='</div>
            </div>';
		
		//右侧数据
		$user = $this->getUser();
		$stats = new UserStats( $user->getID(), $user->getName() );
		$stats_data = $stats->getUserStats();
		
		$profile = new JoymeWikiUser();
		$userprofile = $profile->getProfile($user->getId());
		$userprofile = $userprofile[0];
		
		if ($userprofile['sex'] == '1'){
			$genderIcon = 'man';
		} elseif ($userprofile['sex'] == '0'){
			$genderIcon = 'female';
		} else {
			$genderIcon = '';
		}

		$boardCount = $stats_data['user_board'];
		$privboardCount = $stats_data['user_board_priv'];
		
		$userPageURL = $wgUserCenterUrl.$userprofile['profileid'];
		
		$output.='<div class="col-md-3 web-hide ">
                <div id="sidebar">
                    <div class="user-mess-box">
                        <div class="user-int-mess">
                            <a target="_blank" href="'.$userPageURL.'" class="userinfo" data-username="'.$userprofile['profileid'].'">
                            	<img src="'.$userprofile['icon'].'" />
                            	'.($userprofile['vtype']>0?'<span class="user-vip" title="'.$userprofile['vdesc'].'"></span>':'').'
                            	<span class="luojiaoye-def luojiaoye-dec-0'.$userprofile['headskin'].'"></span>
                            </a>
                            <font class="nickname">'.$user->getName().'</font>
                            <i class="user-sex '.$genderIcon.'"></i>
                        </div>
                        <div class="user-messing">
                            <input type="hidden" name="boardtype" id="boardtype" value="'.$type.'" />
                            <a id="boardcount_a" href="'.$this->getPageTitle()->getLocalUrl('type=1').'" class="lettering '.($type==1?'on':'').'">私信'.($boardCount>0?'<i id="boardcount" '.($boardCount>99?'class="on"':'').'>'.$boardCount.'</i>':'').'</a>
                            <a id="preboardcount_a" href="'.$this->getPageTitle()->getLocalUrl('type=2').'" class="unletter '.($type==1?'':'on').'">未关注人私信'.($privboardCount>0?'<i id="preboardcount" '.($privboardCount>99?'class="on"':'').'>'.$privboardCount.'</i>':'').'</a>
                        </div>
                    </div>
                </div>
            </div>';
		
		$output .='<input type="hidden" id="boardpageurl" name="boardpageurl" value="' . UserBoard::getUserBoardToBoardURL( '' ) . '"/>';
		$output .='<input type="hidden" id="UserBoardWebSocketUrl" name="UserBoardWebSocketUrl" value="' . $wgUserBoardWebSocketUrl . '"/>';
		
		return $output;
	}
	
	public function getBoardList( $type = 1, $limit = 0, $page = 1 ) {
		$user = $this->getUser();
		$dbr = wfGetDB( DB_SLAVE );

		$where = array();
		$options = array();
		//$where['ub_user_id'] = $user->mId;
		$where = 'ub_user_id='.$user->mId.' AND ub_id>0 AND ub_isfollow='.$type;
		
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = ($page-1)*$limit;
			}
			$options['LIMIT'] = $limit;
			$options['OFFSET'] = $limitvalue;
			$options['ORDER BY'] = 'ub_id DESC';
		}
		//消息列表
		$res = $dbr->select(
				'user_board_list',
				array(
						'ubl_id', 'ub_user_id', 'ub_friend_id', 'ub_id', 'ub_msg_count'
				),
				$where,
				__METHOD__,
				$options
		);
		//var_dump($res);exit;
		$ub_id_arr = array(0);
		$friend_id_arr = array(0);
		foreach ( $res as $row ) {
			$ub_id_arr[] = $row->ub_id;
			$friend_id_arr[] = $row->ub_friend_id;
		}
		
		//最后一条消息记录
		$where2 = array('ub_id'=>$ub_id_arr);
		$res2 = $dbr->select(
				'user_board',
				array(
						'ub_id', 'ub_message', 'ub_date'
				),
				$where2,
				__METHOD__
		);
		
		//查询用户
		$where3 = array('user_id'=>$friend_id_arr);
		$res3 = $dbr->select(
				'user',
				array(
						'user_id','user_name'
				),
				$where3,
				__METHOD__
		);
		
		$joymewikiuser = new JoymeWikiUser();
		$user_profiles = $joymewikiuser->getProfile($friend_id_arr);
		
		$list = array();
		foreach ( $res as $k=>$row ) {
			$list[$k] = $row;
			$list[$k]->ub_message = '';
			$list[$k]->ub_date = '';
			$list[$k]->ub_friend_name = '';
			$list[$k]->url = '';
			$list[$k]->profileid = '';
			if($user_profiles){
				foreach ($user_profiles as $pro){
					//var_dump($pro);exit;
					if($row->ub_friend_id == $pro['uid']){
						$list[$k]->url = $pro['icon'];
						$list[$k]->profileid = $pro['profileid'];
						$list[$k]->headskin = $pro['headskin'];
						$list[$k]->vdesc = $pro['vdesc'];
						$list[$k]->vtype = $pro['vtype'];
						break;
					}
				}
			}
			//$list[$k]->url = 'http://tva1.sinaimg.cn/crop.244.21.873.873.180/7f18799bjw1ehi4f25vqnj21400p0wjt.jpg';
			foreach ( $res2 as $row2 ) {
				if($row2->ub_id == $row->ub_id){
					$message_text = str_replace(chr(32),'&nbsp;',htmlspecialchars($row2->ub_message));
					$list[$k]->ub_message = $message_text;
					$list[$k]->ub_date = $row2->ub_date;
					break;
				}
			}
			foreach ( $res3 as $row3 ) {
				if($row3->user_id == $row->ub_friend_id){
					$list[$k]->ub_friend_name = $row3->user_name;
					break;
				}
			}
		}
	
		return $list;
	}
	
	public function getBoardCount( $type = 1) {
		$user = $this->getUser();
		$dbr = wfGetDB( DB_SLAVE );
	
		$where = array();
		$options = array();
		
		$where = 'ub_user_id='.$user->mId.' AND ub_id>0';
	
		$type = $type==1?1:2;
		$where.= ' AND ub_isfollow='.$type;
	

		//消息列表
		$row = $dbr->selectRowCount(
				'user_board_list',
				'1',
				$where,
				__METHOD__,
				$options
		);
	
		return $row;
	}

}
