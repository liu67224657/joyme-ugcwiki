<?php

/**
 * User profile Wiki Page
 *
 * @file
 * @ingroup   Extensions
 * @author    David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use Joyme\page\Page;
class UserProfilePage extends Article
{

    /**
     * @var Title
     */
    public $title = null;

    /**
     * @var String: user name of the user whose profile we're viewing
     */
    public $user_name;

    /**
     * @var Integer: user ID of the user whose profile we're viewing
     */
    public $user_id;

    /**
     * @var User: User object representing the user whose profile we're viewing
     */
    public $user;

    /**
     * @var Boolean: is the current user the owner of the profile page?
     */
    public $is_owner;

    /**
     * @var Array: user profile data (interests, etc.) for the user whose
     * profile we're viewing
     */
    public $profile_data;

    /**
     * @var Array: array of profile fields visible to the user viewing the profile
     */
    public $profile_visible_fields;

    /**
     * @var Array: array of user profile
     */
    public $stats_data;

    public $url;

    public $Request;

    public $activitytab1 = '';
    public $activitytab2 = '';
    public $activitytab3 = '';

    /**
     * Constructor
     */
    function __construct($title)
    {
        global $wgUser;
        parent::__construct($title);
        $this->user_name = ucfirst($title->getText());
        $this->user_id = User::idFromName($this->user_name);
        $this->user = User::newFromId($this->user_id);
        $this->user->loadFromDatabase();

        $this->url = $title->getLocalURL('userprofile=1');

        $this->is_owner = ($this->user_name == $wgUser->getName());

        $stats = new UserStats($this->user_id, $this->user_name);
        $this->stats_data = $stats->getUserStats();

        $this->Request = RequestContext::getMain()->getRequest();

        $useractivity = $this->Request->getval('useractivity');
        $friendactivity = $this->Request->getval('friendactivity');
        if(isset($useractivity)
            &&$useractivity == 'on'
        ){
            $this->activitytab2 = 'on';
        }elseif (isset($friendactivity)
            &&$friendactivity == 'on'
        ){
            $this->activitytab3 = 'on';
        }else{
            $this->activitytab1 = 'on';
        }

//        $profile = new UserProfile($this->user_name);
//        $this->profile_data = $profile->getProfile();
//        $this->profile_visible_fields = SPUserSecurity::getVisibleFields($this->user_id, $wgUser->getId());
    }

    /**
     * Is the current user the owner of the profile page?
     * In other words, is the current user's username the same as that of the
     * profile's owner's?
     *
     * @return Boolean
     */
    function isOwner()
    {
        return $this->is_owner;
    }

    function view()
    {
        global $wgOut,$wgUser,$wgEnv,$wgUserCenterUrl;

        $wgOut->setPageTitle($this->mTitle->getPrefixedText());

        global $wgWikiname;
        if($wgWikiname !='home'){
            $wgOut->redirectHome('用户:'.$this->user_name);
            return false;
        }

        // No need to display noarticletext, we use our own message
        if (!$this->user_id) {
            $output = '<div class="unreal-name">
			<div class="unreal-name-img"></div>
			<p class="unreal-name-text">抱歉！用户账户“<cite>'.$this->user_name.'</cite>”没有进行注册或者激活</p>
		</div>';
            $wgOut->addHTML($output);
            return '';
        }else{
            $jwuser = new JoymeWikiUser();
            $profileid = $jwuser->getProfileid($this->user_id);
            $url = $wgUserCenterUrl.$profileid;
            //header("Location: ".$url);
            $wgOut->redirect($url,'301');
            return false;
        }

        exit();

        $wgOut->addModuleStyles(array(
            'ext.socialprofile.userprofile.usercenter.css',
            'ext.socialprofile.userprofile.usercentercommon.css'
        ));

        $wgOut->addModuleScripts( array(
            'ext.socialprofile.userprofile.userprofilepage.js',
            'ext.RecommendUser.js'
        ));

        $wgOut->addModules( 'mediawiki.action.view.postEdit' );

        ######用户中心top内容开始######

        $wgOut->addHTML($this->getUserProfileSection());

        ######用户中心top内容结束######

        ######用户中心content内容开始######
        $wgOut->addHTML('<div class="">');

        ######左边开始######
        $wgOut->addHTML('<div  class="col-md-9">
                <div id="main" class="pag-hor-20 tab-box">');

        ######切换开始######
        $wgOut->addHTML('<div class="trends-nav fn-clear tab-tit">');

        if ($this->isOwner()) {
            $wgOut->addHTML('<a href="javascript:;" class="'.$this->activitytab1.'" id="mywiki">我的WIKI<i></i></a>');
            $wgOut->addHTML('<a href="javascript:;" class="'.$this->activitytab2.'" id="useractivity">我的动态<i></i></a>');
            $wgOut->addHTML('<a href="javascript:;" class="'.$this->activitytab3.'" id="friendactivity">好友动态<i></i></a>');
        } else {
            $wgOut->addHTML('<a href="javascript:;" class="'.$this->activitytab1.'" id="otherwiki">他的WIKI<i></i></a>');
            $wgOut->addHTML('<a href="javascript:;" class="'.$this->activitytab2.'" id="otheractivity">他的动态<i></i></a>');
        }

        $wgOut->addHTML('</div>');

        $wgOut->addHTML('<div class="tab-con">');
        if($this->isOwner()){
            //我的wiki
            $wgOut->addHTML($this->getUserWikis());
            //我的动态
            $wgOut->addHTML($this->getUserActivity());
            //好友动态
            $wgOut->addHTML($this->getFriendActivity());
        }else{
            //他的wiki
            $wgOut->addHTML($this->getUserWikis());
            //他的动态
            $wgOut->addHTML($this->getUserActivity());
        }


        $wgOut->addHTML('</div>');
        ######切换结束######

        $wgOut->addHTML('</div>
            </div>');
        $wgOut->addHTML('</div>');

        ######左边结束######

        ######右边开始######

        $wgOut->addHTML('<div class="col-md-3 web-hide ">
                <div id="sidebar">');

        if($this->isOwner()){

            //有关注，有粉丝
            if ($this->stats_data['friend_count']
                && $this->stats_data['foe_count']
            ) {
                if($this->stats_data['friend_count']<16){
                    //大神推荐
                    $wgOut->addHTML($this->getManitoRecommend());
                }
                //关注
                $wgOut->addHTML($this->getUserFollow());
                //粉丝
                $wgOut->addHTML($this->getUserFans());
            } //无关注，无粉丝
            elseif (!$this->stats_data['friend_count']
                && !$this->stats_data['foe_count']
            ) {
                //大神推荐
                $wgOut->addHTML($this->getManitoRecommend());
            } //无关注，有粉丝
            elseif (!$this->stats_data['friend_count']
                && $this->stats_data['foe_count']
            ) {
                //大神推荐
                $wgOut->addHTML($this->getManitoRecommend());
                //粉丝
                $wgOut->addHTML($this->getUserFans());
            } //有关注，无粉丝
            elseif ($this->stats_data['friend_count']
                && !$this->stats_data['foe_count']
            ) {
                if($this->stats_data['friend_count']<16){
                    //大神推荐
                    $wgOut->addHTML($this->getManitoRecommend());
                }
                //关注
                $wgOut->addHTML($this->getUserFollow());
            }
        }else{
            //关注
            $wgOut->addHTML($this->getUserFollow());
            //粉丝
            $wgOut->addHTML($this->getUserFans());
        }



        $wgOut->addHTML('</div>
            </div>');

        ######右边结束######

        $wgOut->addHTML('</div>');

        ######用户中心content内容结束######
    }


    /**
     * 用户中心个人相关统计
     * Get the header for the users center page, which includes the user's
     * profile and statistics
     * more.
     */
    function getUserProfileSection()
    {
        global $wgUser;

        $follow_count = isset($this->stats_data['friend_count'])&&$this->stats_data['friend_count']?$this->stats_data['friend_count']:0;
        $follow_count = UserProfile::showFormatNumber($follow_count);

        $fans_count = isset($this->stats_data['foe_count'])&&$this->stats_data['foe_count']?$this->stats_data['foe_count']:0;
        $fans_count = UserProfile::showFormatNumber($fans_count);

        $like_count = isset($this->stats_data['total_like_count'])&&$this->stats_data['total_like_count']?$this->stats_data['total_like_count']:0;
        $like_count = UserProfile::showFormatNumber($like_count);

        $comment_count = isset($this->stats_data['total_comment_count'])&&$this->stats_data['total_comment_count']?$this->stats_data['total_comment_count']:0;
        $comment_count = UserProfile::showFormatNumber($comment_count);

        if(isset($this->stats_data['brief'])
            &&$this->stats_data['brief']
        ){
            $brief = $this->stats_data['brief'];
        }else{
            if($this->isOwner()){
                $brief = '一句话介绍一下自己吧，让别人更了解你';
            }else{
                $brief = '这个人很懒，什么都没留下……';
            }
        }

        $total_edit_count = isset($this->stats_data['total_edit_count'])&&$this->stats_data['total_edit_count']?$this->stats_data['total_edit_count']:0;
        $total_edit_count = UserProfile::showFormatNumber($total_edit_count);

        $joymewikiuser = new JoymeWikiUser();
        $joymewikiuser->getProfile($this->user_id);
        if($joymewikiuser->icon){
            $icon = $joymewikiuser->icon;
        }else{
            $icon = $joymewikiuser->defaulticonurl;
        }
        if($joymewikiuser->sex == 1){
            $sex = 'man';
        }elseif (!is_null($joymewikiuser->sex)
            &&$joymewikiuser->sex != ''
            &&$joymewikiuser->sex == 0
        ){
            $sex = 'female';
        }else{
            $sex = null;
        }

        //今日编辑
        $today_edit_count = $joymewikiuser->getUserTodayEditCount($this->user_id);
        $today_edit_count = UserProfile::showFormatNumber($today_edit_count);

        $output = '<div class=" personal-info fn-clear col-md-12">
            <div class="fn-clear"> 
            <div class="col-md-10 info-r fn-clear">
                <div class="info-user  fn-clear">';

        // This feature is only available for logged-in users.
        if ( $wgUser->isLoggedIn() ) {
            if($this->isOwner()){
                $output .= '
                    <div>
                         <a href="/home/%E7%89%B9%E6%AE%8A:%E6%9B%B4%E6%96%B0%E5%A4%B4%E5%83%8F" class="user-login"><img src="' . $icon . '" alt="">
                        </a>
                        <div class="user-intro-con">
                            <cite class="user-des fn-clear">
                                 <font class="nickname">' . $this->user_name . '</font>';
                        if(is_null($sex)){
                            $output .= '<a href="/home/特殊:我的信息" class="link-set">设置</a>';
                        }else{
                            $output .= '<i class="user-sex '. $sex .'"></i>';
                        }
                $output .='
                            </cite>
                            <a href="/home/%E7%89%B9%E6%AE%8A:%E6%88%91%E7%9A%84%E4%BF%A1%E6%81%AF" class="user-intr">' . $brief . '</a>';
                $output .='
                        </div>
                    </div>';
            }else{
                $output .= '
                    <div>
                         <a href="javascript:;" class="user-login un-link"><img src="' . $icon . '" alt="">
                        </a>
                        <div class="user-intro-con">
                            <cite class="user-des fn-clear">
                                 <font class="nickname">' . $this->user_name . '</font>';
                            if(!is_null($sex)){
                                $output .= '<i class="user-sex '. $sex .'"></i>';
                            }
                $output .= '
                            </cite>
                            <a href="javascript:;" class="user-intr un-link">' . $brief . '</a>';
                    $output .= '<div class="fn-clear gz-sx-con">';
                    $uuf = new UserUserFollow();
                    $uufret = $uuf->checkUserUserFollow($wgUser,$this->user);
                    if($uufret){
                        $output .= '<a href="javascript:;" class="gz-icon un-link">已关注</a>';
                    }else{
                        $output .= '<a href="javascript:;" class="gz-icon user-other-follow" data-uid="'.$this->user_id.'" data-action="follow" data-nohtml="1">关注</a>';
                    }
                    $output .= '<a href="/home/index.php?title=特殊:私信&fid='.$this->user_id.'" class="sx-icon" target="_blank">私信</a>';
                    $output .= '</div>';

                $output .='
                        </div>
                    </div>';
            }

            $output .= '
                </div>
                <div class="info-edit-num ">
                    <cite class="count-num">
                    <a href="/home/index.php?title=特殊:着迷贡献&userid='.$this->user_id.'" target="_blank">
                        <font><i>' . $total_edit_count . '</i>次</font>
                        总共编辑
                        </a>
                    </cite>
                    <cite class="day-num">
                        <font><i>' . $today_edit_count . '</i>次</font>
                        今日编辑
                    </cite>
                </div>
            
            </div>
            <div class="col-md-2 look-con fn-clear">';

            $output .= Linker::LinkKnown(
                SpecialPage::getTitleFor('ViewFollows'),
                '<i>'. $follow_count . '</i>关注',
                array("class" => "follow-num"),
                array('user' => $this->user_name, 'rel_type' => 1)
            );
            $output .= Linker::LinkKnown(
                SpecialPage::getTitleFor('ViewFollows'),
                '<i>'. $fans_count . '</i>粉丝',
                array("class" => "fans-num"),
                array('user' => $this->user_name, 'rel_type' => 2)
            );

            $output .= '
                <a href="javascript:;" class="zan-num un-link"><i>' . $like_count . '</i>被赞</a>
                <a href="javascript:;" class="dis-num un-link"><i>' . $comment_count . '</i>被评</a>
            </div>
            </div>
        </div>';

        }else{
            $output .= '
                    <div>
                         <a href="javascript:;" class="user-login un-link"><img src="' . $icon . '" alt="">
                        </a>
                        <div class="user-intro-con">
                            <cite class="user-des fn-clear">
                                 <font class="nickname">' . $this->user_name . '</font>';
                            if(!is_null($sex)){
                                $output .= '<i class="user-sex '. $sex .'"></i>';
                            }
            $output .= '
                            </cite>
                            <a href="javascript:;" class="user-intr un-link">' . $brief . '</a>
                        </div>
                    </div>';


            $output .= '
                </div>
                <div class="info-edit-num ">
                    <cite class="count-num">
                    <a href="javascript:;" class="un-link">
                        <font><i>' . $total_edit_count . '</i>次</font>
                        总共编辑
                        </a>
                    </cite>
                    <cite class="day-num">
                        <font><i>' . $today_edit_count . '</i>次</font>
                        今日编辑
                    </cite>
                </div>
            
            </div>
            <div class="col-md-2 look-con fn-clear">';
            $output .= '
                <a href="javascript:;" class="follow-num un-link"><i>'. $follow_count . '</i>关注</a>
                <a href="javascript:;" class="fans-num un-link"><i>'. $fans_count . '</i>粉丝</a>
                <a href="javascript:;" class="zan-num un-link"><i>' . $like_count . '</i>被赞</a>
                <a href="javascript:;" class="dis-num un-link"><i>' . $comment_count . '</i>被评</a>
            </div>
            </div>
        </div>';
        }

        return $output;

    }

    /**
     * 我的wiki
     */
    function getUserWikis()
    {
        global $joyme_u_adminid;
        $joymewikiuser = new JoymeWikiUser();
        
        $userprofile = new UserProfile($this->user_name);
        $userwikicount = $userprofile->getUserWikisCount($this->user_id);

        if($this->isOwner()){
            $owner = '您';
        }else{
            $owner = '他';
        }

        $output = '<!--我的WIKI 开始-->
				<div class="trends-con '.$this->activitytab1.' ">';

        if (empty($userwikicount)) {
            $output .= '<div class="no-date">'.$owner.'还没有关注，贡献或管理任何WIKI</div>';
        } else {
            $userallwikis = $userprofile->getUserWikis($this->user_id);

            $site_ids = array_column($userallwikis,'site_id');
            if($site_ids){
                $joymesite = new JoymeSite();
                $siteinfos = $joymesite->getSiteInfo($site_ids);
                if($siteinfos){
                    $site_names = array_column($siteinfos,'site_name','site_id');
                    $site_keys = array_column($siteinfos,'site_key','site_id');
                    $site_icons = array_column($siteinfos,'site_icon','site_id');
                    $page_counts = array_column($siteinfos,'page_count','site_id');
                    $edit_counts = array_column($siteinfos,'edit_count','site_id');
                    $edituser_counts = array_column($siteinfos,'edituser_count','site_id');
                    $yes_editcounts = array_column($siteinfos,'yes_editcount','site_id');
                    $follow_usercounts = array_column($siteinfos,'follow_usercount','site_id');
                }

                $offercounts = $joymewikiuser->getUserSiteOfferCount($this->user_id,$site_ids);
                if($offercounts){
                    $offer_counts = array_column($offercounts,'offer_count','site_id');
                }
            }
            //管理wiki
            $manageWikis = $userprofile->getUserWikis($this->user_id,1,3);
            if (!empty($manageWikis)) {
                $userManagewikicount = $userprofile->getUserWikisCount($this->user_id,1);
                $output .= '	<!--管理WIKI  开始-->
					<div class="manage-list">
						<h3 class="list-tit glwiki-tit"><i></i>管理的Wiki</h3>
						<ul class="manage-item  fn-clear">';

                foreach ($manageWikis as $manageWiki) {

                    if(isset($site_keys[$manageWiki['site_id']])
                        && $site_keys[$manageWiki['site_id']]
                    ){
                        $site_key = $site_keys[$manageWiki['site_id']];
                    }else{
                        $site_key = '';
                    }

                    $output .= '
                    <li class="col-md-4">
                        <div class="manage-wiki">
                            <a href="/'.$site_key.'/%E9%A6%96%E9%A1%B5" class="mg-wiki-main fn-clear col-sm-12" target="_blank">
                                 <b class="manager web-hide">管理员</b>
                                <div class="col-md-12">
                                     <cite>';
                    if(isset($site_icons[$manageWiki['site_id']])
                    && $site_icons[$manageWiki['site_id']]
                    ){
                        $output .= '<img src="'.$site_icons[$manageWiki['site_id']].'" alt="">';
                    }else{
                        $output .= '<img src="" alt="">';
                    }

                    $output .= '
							<i>管理员</i>
						</cite>
					</div>
					<div class="manager-text col-md-12">';
                    //wiki名称
                    if(isset($site_names[$manageWiki['site_id']])
                        && $site_names[$manageWiki['site_id']]
                    ){
                        $output .= '<font>'.$site_names[$manageWiki['site_id']].'</font>';
                    }else{
                        $output .= '<font></font>';
                    }
                    //页面总数量
                    if(isset($page_counts[$manageWiki['site_id']])
                        && $page_counts[$manageWiki['site_id']]
                    ){
                        $output .= '<span>页面总数量：'.$page_counts[$manageWiki['site_id']].' </span>';
                    }else{
                        $output .= '<span>页面总数量：0 </span>';
                    }
                    //编辑总次数
                    if(isset($edit_counts[$manageWiki['site_id']])
                        && $edit_counts[$manageWiki['site_id']]
                    ){
                        $output .= '<span>编辑总次数：'.$edit_counts[$manageWiki['site_id']].'  </span>';
                    }else{
                        $output .= '<span>编辑总次数：0  </span>';
                    }

                    $output .= '
					</div>
				</a>
				<a href="javascript:;" class="web-hide add-edit">';

                    if(isset($follow_usercounts[$manageWiki['site_id']])
                        && $follow_usercounts[$manageWiki['site_id']]
                    ){
                        $output .= '<span>关注人数：'.$follow_usercounts[$manageWiki['site_id']].'  </span>';
                    }else{
                        $output .= '<span>关注人数：0 </span>';
                    }
                    if(isset($edituser_counts[$manageWiki['site_id']])
                        && $edituser_counts[$manageWiki['site_id']]
                    ){
                        $output .= '<span>编辑人数：'.$edituser_counts[$manageWiki['site_id']].'  </span>';
                    }else{
                        $output .= '<span>编辑人数：0 </span>';
                    }

                    if(isset($yes_editcounts[$manageWiki['site_id']])
                        && $yes_editcounts[$manageWiki['site_id']]
                    ){
                        $output .= '<span>昨日编辑：'.$yes_editcounts[$manageWiki['site_id']].'  </span>';
                    }else{
                        $output .= '<span>昨日编辑：0  </span>';
                    }


                    $output .= '
                                </a>
                                <i class="caret  count-icon web-hide"></i>
                            </div>
                        </li>';
                }

                $output .= '</ul>';
                if($userManagewikicount>3){
                    $output .= '<div class="more-con">
							<button id="managewikimore" data-page="2" data-uid="'.$this->user_id.'">点击加载更多...</button>
						</div>';
                }
                $output .= '
					</div>
					<!--管理WIKI 结束-->';
            }
            //如果是超级管理员，不显示贡献wiki和关注wiki，都是管理wiki
            if(!in_array($this->user_id,$joyme_u_adminid)) {

                //贡献wiki
                $contributeWikis = $userprofile->getUserWikis($this->user_id, 2, 6);
                if (!empty($contributeWikis)) {
                    $userContributewikicount = $userprofile->getUserWikisCount($this->user_id, 2);
                    $output .= '<!--贡献的wiki  开始 -->
					<div class="tj-list " >
						<h3 class="list-tit gxwiki-tit"><i></i>贡献的wiki</h3>
						<ul class="list-item fn-clear contribute-item">';

                    foreach ($contributeWikis as $contributeWiki) {
                        if (isset($site_keys[$contributeWiki['site_id']])
                            && $site_keys[$contributeWiki['site_id']]
                        ) {
                            $site_key = $site_keys[$contributeWiki['site_id']];
                        } else {
                            $site_key = '';
                        }

                        $output .= '<li class="col-md-4">
								<a href="/' . $site_key . '/%E9%A6%96%E9%A1%B5" target="_blank">
									<cite>';
                        if (isset($site_icons[$contributeWiki['site_id']])
                            && $site_icons[$contributeWiki['site_id']]
                        ) {
                            $output .= '<img src="' . $site_icons[$contributeWiki['site_id']] . '" alt="" >';
                        } else {
                            $output .= '<img src="" alt="">';
                        }

                        $output .= '
									</cite>
									<span>';
                        //wiki名称
                        if (isset($site_names[$contributeWiki['site_id']])
                            && $site_names[$contributeWiki['site_id']]
                        ) {
                            $output .= '<font>' . $site_names[$contributeWiki['site_id']] . '</font>';
                        } else {
                            $output .= '<font></font>';
                        }

                        if (isset($offer_counts[$contributeWiki['site_id']])
                            && $offer_counts[$contributeWiki['site_id']]
                        ) {
                            $output .= '<b>贡献总数：' . $offer_counts[$contributeWiki['site_id']] . '  </b>';
                        } else {
                            $output .= '<b>贡献总数：0  </b>';
                        }

                        if (isset($yes_editcounts[$contributeWiki['site_id']])
                            && $yes_editcounts[$contributeWiki['site_id']]
                        ) {
                            $output .= '<b>昨日编辑：' . $yes_editcounts[$contributeWiki['site_id']] . '  </b>';
                        } else {
                            $output .= '<b>昨日编辑：0  </b>';
                        }

                        $output .= '
									</span>
								</a>
							</li>';
                    }

                    $output .= '</ul>';
                    if ($userContributewikicount > 6) {
                        $output .= '
                        <div class="more-con">
							<button id="contributewikimore" data-page="3" data-uid="' . $this->user_id . '">点击加载更多...</button>
						</div>';
                    }
                    $output .= '
					</div>
					<!--贡献的wiki  结束 -->';
                }

                //关注wiki
                $followWikis = $userprofile->getUserWikis($this->user_id, 3, 6);
                if (!empty($followWikis)) {
                    $userFollowwikicount = $userprofile->getUserWikisCount($this->user_id, 3);
                    $output .= '<!--关注的wiki  开始 -->
					<div class="tj-list ">
						<h3 class="list-tit gzwiki-tit"><i></i>关注的wiki</h3>
						<ul class="list-item fn-clear row follow-item">';

                    foreach ($followWikis as $followWiki) {
                        if (isset($site_keys[$followWiki['site_id']])
                            && $site_keys[$followWiki['site_id']]
                        ) {
                            $site_key = $site_keys[$followWiki['site_id']];
                        } else {
                            $site_key = '';
                        }

                        $output .= '<li class="col-md-4">
								<a href="/' . $site_key . '/%E9%A6%96%E9%A1%B5" target="_blank">
									<cite>';
                        if (isset($site_icons[$followWiki['site_id']])
                            && $site_icons[$followWiki['site_id']]
                        ) {
                            $output .= '<img src="' . $site_icons[$followWiki['site_id']] . '" alt="" >';
                        } else {
                            $output .= '<img src="" alt="">';
                        }
                        $output .= '	</cite>
									<span>';
                        //wiki名称
                        if (isset($site_names[$followWiki['site_id']])
                            && $site_names[$followWiki['site_id']]
                        ) {
                            $output .= '<font>' . $site_names[$followWiki['site_id']] . '</font>';
                        } else {
                            $output .= '<font></font>';
                        }
                        //页面总数量
                        if (isset($page_counts[$followWiki['site_id']])
                            && $page_counts[$followWiki['site_id']]
                        ) {
                            $output .= '<b>页面总数量：' . $page_counts[$followWiki['site_id']] . ' </b>';
                        } else {
                            $output .= '<b>页面总数量：0 </b>';
                        }

                        if (isset($yes_editcounts[$followWiki['site_id']])
                            && $yes_editcounts[$followWiki['site_id']]
                        ) {
                            $output .= '<b>昨日编辑：' . $yes_editcounts[$followWiki['site_id']] . '  </b>';
                        } else {
                            $output .= '<b>昨日编辑：0  </b>';
                        }

                        $output .= '
									</span>
								</a>
							</li>';
                    }

                    $output .= '</ul>';
                    if ($userFollowwikicount > 6) {
                        $output .= '
                        <div class="more-con">
							<button id="followwikimore" data-page="3" data-uid="' . $this->user_id . '">点击加载更多...</button>
						</div>';
                    }
                    $output .= '
					</div>
					<!--关注的wiki  结束 -->';
                }
            }

        }

       $followcount = $joymewikiuser->getUserSiteFollowCount($this->user_id);
        //推荐WIKI中的热门站点给首次来到个人中心的用户。该板块不是时时刻刻都会出现在个人中心中，只要用户关注了3个WIKI后该板块就会消失
        if ($followcount < 3) {
            ######热门WIKI推荐 开始######
            $output .= $this->getHotWikiRecommend();
            ######热门WIKI推荐 结束######
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * 我的关注
     */
    function getUserFollow()
    {
        global $wgUser;
        $uuf = new UserUserFollow();
        $followlists = $uuf->getFollowList($this->user_id, 1, 8);


        if ($this->isOwner()) {
            $userowner = '我';
        } else {
            $userowner = '他';
        }

        if ( $wgUser->isLoggedIn() ) {
            $followlink = Linker::LinkKnown(
                SpecialPage::getTitleFor('ViewFollows'),
                '更多',
                array("class" => "fn-right"),
                array('user' => $this->user_name, 'rel_type' => 1)
            );
        }else{
            $followlink = '<a href="javascript:;" title="特殊:ViewFollows" class="fn-right user-nologin">更多</a>';
        }


        $output = '';
        $output .= '<!--我的关注  开始-->
                    <div class="fans-box fn-clear pag-hor-20">
                        <h3>' . $userowner . '的关注' . $followlink . '</h3>';
        if($followlists){
            $output .= '<div class="row fans-con">';
            $fuserids = array_column($followlists, 'user_id');
            $joymewikiuser = new JoymeWikiUser();
            $userprofiles = $joymewikiuser->getProfile($fuserids);
            if($userprofiles){
                $nicks = array_column($userprofiles,'nick','uid');
                $icons = array_column($userprofiles,'icon','uid');
            }
            foreach ($followlists as $followlist) {
                $output .= '  <a href="/home/用户:';
                if(isset($nicks[$followlist['user_id']])
                    &&$nicks[$followlist['user_id']]
                ){
                    $output .= $nicks[$followlist['user_id']];
                }

                $output .=  '" class="col-sm-3">
                            <cite>
                                <img src="';
                if(isset($icons[$followlist['user_id']])
                    &&$icons[$followlist['user_id']]
                ){
                    $output .= $icons[$followlist['user_id']];
                }
                $output .= '" alt="img">
                            </cite>
                            <font>';
                if(isset($nicks[$followlist['user_id']])
                    &&$nicks[$followlist['user_id']]
                ){
                    $output .= $nicks[$followlist['user_id']];
                }
                $output .= '</font>
                        </a>';
            }
            $output .= '    </div>';
        }else{
            $output .= '<!--他没有粉丝、关注时  开始-->
                        <div class="no-fans">
                            <p><i></i>他还没有关注任何人</p>
                        </div>';
        }

        $output .= '
                    </div>
                    <!--我的关注  结束-->';

        return $output;
    }

    /**
     * 我的粉丝
     */
    function getUserFans()
    {
        global $wgUser;
        $uuf = new UserUserFollow();
        $fanlists = $uuf->getFollowList($this->user_id, 2, 8);

        if ($this->isOwner()) {
            $userowner = '我';
        } else {
            $userowner = '他';
        }

        if ( $wgUser->isLoggedIn() ) {
            $fanslink = Linker::LinkKnown(
                SpecialPage::getTitleFor('ViewFollows'),
                '更多',
                array("class" => "fn-right"),
                array('user' => $this->user_name, 'rel_type' => 2)
            );
        }else{
            $fanslink = '<a href="javascript:;" title="特殊:ViewFollows" class="fn-right user-nologin">更多</a>';
        }

        $output = '';
        $output .= '<!--我的粉丝  开始-->
                    <div class="fans-box fn-clear pag-hor-20">
                        <h3>' . $userowner . '的粉丝' . $fanslink . '</h3>';
        if($fanlists){
            $output .= '<div class="row fans-con">';
            $fansuserids = array_column($fanlists, 'user_id');
            $joymewikiuser = new JoymeWikiUser();
            $userprofiles = $joymewikiuser->getProfile($fansuserids);
            if($userprofiles){
                $nicks = array_column($userprofiles,'nick','uid');
                $icons = array_column($userprofiles,'icon','uid');
            }
            foreach ($fanlists as $fanlist) {
                $output .= '  <a href="/home/用户:';
                if(isset($nicks[$fanlist['user_id']])
                    &&$nicks[$fanlist['user_id']]
                ){
                    $output .= $nicks[$fanlist['user_id']];
                }

                $output .=  '" class="col-sm-3">
                            <cite>
                                <img src="';
                if(isset($icons[$fanlist['user_id']])
                    &&$icons[$fanlist['user_id']]
                ){
                    $output .= $icons[$fanlist['user_id']];
                }
                $output .= '" alt="img">
                            </cite>
                            <font>';
                if(isset($nicks[$fanlist['user_id']])
                    &&$nicks[$fanlist['user_id']]
                ){
                    $output .= $nicks[$fanlist['user_id']];
                }
                $output .= '</font>
                        </a>';


            }
            $output .= '    </div>';
        }else{
            $output .= '<!--他没有粉丝、关注时  开始-->
                        <div class="no-fans">
                            <p><i></i>他还没有粉丝</p>
                        </div>
                        <!--他没有粉丝、关注时  结束-->';
        }

        $output .= '
                    </div>
                    <!--我的粉丝  结束-->';

        return $output;
    }


    /**
     * 大神推荐
     *
     */
    function getManitoRecommend()
    {
        $ruser = new RecommendUsers();
        $manitos = (array)$ruser->getUserInfo($this->user_id);
        $output = '';
        if (count($manitos)>=4) {
            $output .= '<!--大神推荐  开始-->
                     <div class="no-follow">
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

        return $output;
    }

    /**
     * 热门wiki推荐
     */
    function getHotWikiRecommend()
    {

        $output = '';

        $hotwikis = RecommendWiki::getWikiInfo();
        if ($hotwikis) {
            $wiki_keys = array_column($hotwikis,'site_key');
            $joymewikiuser = new JoymeWikiUser();
            $siteinfos = $joymewikiuser->getSiteInfo($wiki_keys);
            if($siteinfos){
                $site_names = array_column($siteinfos,'site_name','site_key');
                $page_counts = array_column($siteinfos,'page_count','site_key');
                $yes_editcounts = array_column($siteinfos,'yes_editcount','site_key');
            }

            $output .= '<!--热门WIKI推荐 开始-->
					<div class="tj-list " >
						<h3 class="list-tit hotwiki-tit"><i></i>热门WIKI推荐</h3>
						<ul class="list-item fn-clear row">';
            foreach ($hotwikis as $hwiki) {

                $output .= '<li class="col-md-4">
								<a href="/'.$hwiki['site_key'].'/%E9%A6%96%E9%A1%B5" target="_blank">
									<cite>
									<img src="' . $hwiki['icon'] . '" alt="img">
									</cite>
									<span>';

                //wiki名称
                if(isset($site_names[$hwiki['site_key']])
                    && $site_names[$hwiki['site_key']]
                ){
                    $output .= '<font>'.$site_names[$hwiki['site_key']].'</font>';
                }else{
                    $output .= '<font></font>';
                }
                //页面总数量
                if(isset($page_counts[$hwiki['site_key']])
                    && $page_counts[$hwiki['site_key']]
                ){
                    $output .= '<b>页面总数：'.$page_counts[$hwiki['site_key']].' </b>';
                }else{
                    $output .= '<b>页面总数：0 </b>';
                }

                if(isset($yes_editcounts[$hwiki['site_key']])
                    && $yes_editcounts[$hwiki['site_key']]
                ){
                    $output .= '<b>昨日编辑：'.$yes_editcounts[$hwiki['site_key']].'  </b>';
                }else{
                    $output .= '<b>昨日编辑：0  </b>';
                }
                $output .= '
									</span>
								</a>
							</li>';
            }

            $output .= '</ul>
						<div class="more-con">
							<a href="http://wiki.joyme.com/">更多WIKI</a>
						</div>
					</div>
					<!--热门WIKI推荐 结束-->';

        }

        return $output;
    }

    /**
     * 用户动态
     */
    function getUserActivity()
    {
        global $wgUserProfileDisplay;

        // If not enabled in site settings, don't display
        if ($wgUserProfileDisplay['activity'] == false) {
            return '';
        }

        if($this->isOwner()){
            $owner_show = '您在WIKI中什么都没有做还要什么动态！~？';
        }else{
            $owner_show = '他在WIKI中什么都没有做';
        }

        $page = $this->Request->getInt('userpage', 1);

        $limit = 10;

        $output = '';
        $joymewikiuser = new JoymeWikiUser();
        $activity = $joymewikiuser->getUserActionLog($this->user_id,$limit,$page);
        $total = $joymewikiuser->getUserActionLogCount($this->user_id);
        $output .= '<!--我的动态 开始-->
				<div class="my-trends-con '.$this->activitytab2.' " >';

        if(empty($activity)){
            $output .= '<p style="line-height:40px;color:#555;">
								'.$owner_show.'
							</p>';
        }else{
            $output .= '<ul class="my-trends-list">';
            foreach ($activity as $item){
                $output .= '<li>
							<p>
								'.$item['content'].'
								<b class="time-stamp">' . date('Y年m月d日 H:i', $item['add_time']) . '</b>
							</p>
						</li>';
            }

        }
        $output .= '
					</ul>';

        if($total>10){
            $_page = new Page(array(
                    'page_name'=>'userpage',
                    'total' => $total,
                    'perpage'=>$limit,
                    'nowindex'=>$page,
                    'pagebarnum'=>10,
                    'url'=>'',
                    'classname'=>array('main_page'=>'paging','active'=>'on')
                )
            );
            $page_str = $_page->show(2,array('useractivity'=>'on'));
            $output.=$page_str;
            $output .= '<div class="activity-more-con more-con" >
							<button id="useractivitymore" data-page="2"  data-uid="'.$this->user_id.'">点击加载更多...</button>
						</div>';

        }
        $output .= '</div>';


        return $output;
    }

    /**
     * 好友动态
     */
    function getFriendActivity()
    {
        $output = '';
        if ($this->isOwner()) {
            $page = $this->Request->getInt('friendpage', 1);

            $limit = 10;
            $total = 0;
            $uuf = new UserUserFollow();
            $follows = $uuf->getFollowList($this->user_id, 1);
            if($follows){
                $friendids = array_column($follows,'user_id');
                $joymewikiuser = new JoymeWikiUser();
                $userprofiles = $joymewikiuser->getProfile($friendids);
                if($userprofiles){
                    $usericons = array_column($userprofiles,'icon','uid');
                    $usernicks = array_column($userprofiles,'nick','uid');
                }
                $friendsactivitys = $joymewikiuser->getUserActionLog($friendids,$limit,$page);
                $total = $joymewikiuser->getUserActionLogCount($friendids);
            }

            $output .= '<!-- 好友动态 开始-->
				<div class="friend-trends-con '.$this->activitytab3.'" >';
            if(empty($friendsactivitys)){
                if(empty($follows)){
                    $output .= '<p style="line-height:40px;color:#555;">您还未关注任何好友</p>';
                }else{
                    $output .= '<p  style="line-height:40px;color:#555;">您关注的好友还没有任何动态</p>';
                }

            }else{
                $output .= '<ul class="friend-trends-list">';

                foreach ($friendsactivitys as $friendsactivity){
                    $output .= '<li class="fn-clear">
							<div class="user-img">
								 <cite>';
                    if(isset($usericons[$friendsactivity['user_id']])
                        &&$usericons[$friendsactivity['user_id']]
                        &&isset($usernicks[$friendsactivity['user_id']])
                        &&$usernicks[$friendsactivity['user_id']]
                    ){
                        $output .= '<a href="/home/用户:'.$usernicks[$friendsactivity['user_id']].'" target="_blank"><img src="'.$usericons[$friendsactivity['user_id']].'" alt="img"></a><span>'.$usernicks[$friendsactivity['user_id']].'</span>';
                    }else{
                        $output .= '<img src="" alt="img">';
                    }

                    $output .= '
								</cite>
							</div>
							<div class="col-sm-12 user-text">
								<p ><i class="user-name">';
                    if(isset($usernicks[$friendsactivity['user_id']])
                        &&$usernicks[$friendsactivity['user_id']]
                    ){
                        $output .= $usernicks[$friendsactivity['user_id']];
                    }
                    $output .= '</i>'.$friendsactivity['content'].'
								<b class="time-stamp">'.date('Y年m月d日 H:i',$friendsactivity['add_time']).'</b></p>
							</div>
						</li>';

                }
            }
            $output .= '
					</ul>';
            if($total>10){
                $_page = new Page(array(
                        'page_name'=>'friendpage',
                        'total' => $total,
                        'perpage'=>$limit,
                        'nowindex'=>$page,
                        'pagebarnum'=>10,
                        'url'=>$this->url,
                        'classname'=>array('main_page'=>'paging','active'=>'on')
                    )
                );
                $page_str = $_page->show(2,array(
                    'friendactivity'=>'on'
                ));
                $output.=$page_str;
                $output .= '<div class="activity-more-con more-con">
							<button id="friendactivitymore" data-page="2" data-uid="'.$this->user_id.'">点击加载更多...</button>
						</div>';
            }

            $output .= '</div>';
        }

        return $output;
    }


    function getUserStatsRow($label, $value)
    {
        $output = ''; // Prevent E_NOTICE

        if ($value != 0) {
            global $wgLang;
            $formattedValue = $wgLang->formatNum($value);
            $output = "<div>
					<b>{$label}</b>
					{$formattedValue}
			</div>";
        }

        return $output;
    }

    /**
     * 我的动态
     */
    function getUserStats($user_id, $user_name)
    {
        global $wgUserProfileDisplay;

        if ($wgUserProfileDisplay['stats'] == false) {
            return '';
        }

        $output = ''; // Prevent E_NOTICE

        $stats = new UserStats($user_id, $user_name);
        $stats_data = $stats->getUserStats();

        $total_value = $stats_data['edits'] . $stats_data['votes'] .
            $stats_data['comments'] . $stats_data['recruits'] .
            $stats_data['poll_votes'] .
            $stats_data['picture_game_votes'] .
            $stats_data['quiz_points'];

        if ($total_value != 0) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-stats-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="action-left">
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="profile-info-container bold-fix">' .
                $this->getUserStatsRow(
                    wfMessage('user-stats-edits', $stats_data['edits'])->escaped(),
                    $stats_data['edits']
                ) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-votes', $stats_data['votes'])->escaped(),
                    $stats_data['votes']
                ) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-comments', $stats_data['comments'])->escaped(),
                    $stats_data['comments']) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-recruits', $stats_data['recruits'])->escaped(),
                    $stats_data['recruits']
                ) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-poll-votes', $stats_data['poll_votes'])->escaped(),
                    $stats_data['poll_votes']
                ) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-picture-game-votes', $stats_data['picture_game_votes'])->escaped(),
                    $stats_data['picture_game_votes']
                ) .
                $this->getUserStatsRow(
                    wfMessage('user-stats-quiz-points', $stats_data['quiz_points'])->escaped(),
                    $stats_data['quiz_points']
                );
            if ($stats_data['currency'] != '10000') {
                $output .= $this->getUserStatsRow(
                    wfMessage('user-stats-pick-points', $stats_data['currency'])->escaped(),
                    $stats_data['currency']
                );
            }
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get three of the polls the user has created and cache the data in
     * memcached.
     *
     * @return Array
     */
    function getUserPolls()
    {
        global $wgMemc;

        $polls = array();

        // Try cache
        $key = wfMemcKey('user', 'profile', 'polls', $this->user_id);
        $data = $wgMemc->get($key);

        if ($data) {
            wfDebug("Got profile polls for user {$this->user_id} from cache\n");
            $polls = $data;
        } else {
            wfDebug("Got profile polls for user {$this->user_id} from DB\n");
            $dbr = wfGetDB(DB_SLAVE);
            $res = $dbr->select(
                array('poll_question', 'page'),
                array(
                    'page_title', 'UNIX_TIMESTAMP(poll_date) AS poll_date'
                ),
                /* WHERE */
                array('poll_user_id' => $this->user_id),
                __METHOD__,
                array('ORDER BY' => 'poll_id DESC', 'LIMIT' => 3),
                array('page' => array('INNER JOIN', 'page_id = poll_page_id'))
            );
            foreach ($res as $row) {
                $polls[] = array(
                    'title' => $row->page_title,
                    'timestamp' => $row->poll_date
                );
            }
            $wgMemc->set($key, $polls);
        }
        return $polls;
    }

    /**
     * Get three of the quiz games the user has created and cache the data in
     * memcached.
     *
     * @return Array
     */
    function getUserQuiz()
    {
        global $wgMemc;

        $quiz = array();

        // Try cache
        $key = wfMemcKey('user', 'profile', 'quiz', $this->user_id);
        $data = $wgMemc->get($key);

        if ($data) {
            wfDebug("Got profile quizzes for user {$this->user_id} from cache\n");
            $quiz = $data;
        } else {
            wfDebug("Got profile quizzes for user {$this->user_id} from DB\n");
            $dbr = wfGetDB(DB_SLAVE);
            $res = $dbr->select(
                'quizgame_questions',
                array(
                    'q_id', 'q_text', 'UNIX_TIMESTAMP(q_date) AS quiz_date'
                ),
                array(
                    'q_user_id' => $this->user_id,
                    'q_flag' => 0 // the same as QUIZGAME_FLAG_NONE
                ),
                __METHOD__,
                array(
                    'ORDER BY' => 'q_id DESC',
                    'LIMIT' => 3
                )
            );
            foreach ($res as $row) {
                $quiz[] = array(
                    'id' => $row->q_id,
                    'text' => $row->q_text,
                    'timestamp' => $row->quiz_date
                );
            }
            $wgMemc->set($key, $quiz);
        }

        return $quiz;
    }

    /**
     * Get three of the picture games the user has created and cache the data
     * in memcached.
     *
     * @return Array
     */
    function getUserPicGames()
    {
        global $wgMemc;

        $pics = array();

        // Try cache
        $key = wfMemcKey('user', 'profile', 'picgame', $this->user_id);
        $data = $wgMemc->get($key);
        if ($data) {
            wfDebug("Got profile picgames for user {$this->user_id} from cache\n");
            $pics = $data;
        } else {
            wfDebug("Got profile picgames for user {$this->user_id} from DB\n");
            $dbr = wfGetDB(DB_SLAVE);
            $res = $dbr->select(
                'picturegame_images',
                array(
                    'id', 'title', 'img1', 'img2',
                    'UNIX_TIMESTAMP(pg_date) AS pic_game_date'
                ),
                array(
                    'userid' => $this->user_id,
                    'flag' => 0 // PICTUREGAME_FLAG_NONE
                ),
                __METHOD__,
                array(
                    'ORDER BY' => 'id DESC',
                    'LIMIT' => 3
                )
            );
            foreach ($res as $row) {
                $pics[] = array(
                    'id' => $row->id,
                    'title' => $row->title,
                    'img1' => $row->img1,
                    'img2' => $row->img2,
                    'timestamp' => $row->pic_game_date
                );
            }
            $wgMemc->set($key, $pics);
        }

        return $pics;
    }

    /**
     * Get the casual games (polls, quizzes and picture games) that the user
     * has created if $wgUserProfileDisplay['games'] is set to true and the
     * PictureGame, PollNY and QuizGame extensions have been installed.
     *
     * @param $user_id   Integer: user ID number
     * @param $user_name String: user name
     * @return String: HTML or nothing if this feature isn't enabled
     */
    function getCasualGames($user_id, $user_name)
    {
        global $wgUser, $wgOut, $wgUserProfileDisplay;

        if ($wgUserProfileDisplay['games'] == false) {
            return '';
        }

        $output = '';

        // Safe titles
        $quiz_title = SpecialPage::getTitleFor('QuizGameHome');
        $pic_game_title = SpecialPage::getTitleFor('PictureGameHome');

        // Combine the queries
        $combined_array = array();

        $quizzes = $this->getUserQuiz();
        foreach ($quizzes as $quiz) {
            $combined_array[] = array(
                'type' => 'Quiz',
                'id' => $quiz['id'],
                'text' => $quiz['text'],
                'timestamp' => $quiz['timestamp']
            );
        }

        $polls = $this->getUserPolls();
        foreach ($polls as $poll) {
            $combined_array[] = array(
                'type' => 'Poll',
                'title' => $poll['title'],
                'timestamp' => $poll['timestamp']
            );
        }

        $pics = $this->getUserPicGames();
        foreach ($pics as $pic) {
            $combined_array[] = array(
                'type' => 'Picture Game',
                'id' => $pic['id'],
                'title' => $pic['title'],
                'img1' => $pic['img1'],
                'img2' => $pic['img2'],
                'timestamp' => $pic['timestamp']
            );
        }

        usort($combined_array, array('UserProfilePage', 'sortItems'));

        if (count($combined_array) > 0) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('casual-games-title')->escaped() . '
				</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="action-left">
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="casual-game-container">';

            $x = 1;

            foreach ($combined_array as $item) {
                $output .= (($x == 1) ? '<p class="item-top">' : '<p>');

                if ($item['type'] == 'Poll') {
                    $ns = (defined('NS_POLL') ? NS_POLL : 300);
                    $poll_title = Title::makeTitle($ns, $item['title']);
                    $casual_game_title = wfMessage('casual-game-poll')->escaped();
                    $output .= '<a href="' . htmlspecialchars($poll_title->getFullURL()) .
                        "\" rel=\"nofollow\">
							{$poll_title->getText()}
						</a>
						<span class=\"item-small\">{$casual_game_title}</span>";
                }

                if ($item['type'] == 'Quiz') {
                    $casual_game_title = wfMessage('casual-game-quiz')->escaped();
                    $output .= '<a href="' .
                        htmlspecialchars($quiz_title->getFullURL('questionGameAction=renderPermalink&permalinkID=' . $item['id'])) .
                        "\" rel=\"nofollow\">
							{$item['text']}
						</a>
						<span class=\"item-small\">{$casual_game_title}</span>";
                }

                if ($item['type'] == 'Picture Game') {
                    if ($item['img1'] != '' && $item['img2'] != '') {
                        $image_1 = $image_2 = '';
                        $render_1 = wfFindFile($item['img1']);
                        if (is_object($render_1)) {
                            $thumb_1 = $render_1->transform(array('width' => 25));
                            $image_1 = $thumb_1->toHtml();
                        }

                        $render_2 = wfFindFile($item['img2']);
                        if (is_object($render_2)) {
                            $thumb_2 = $render_2->transform(array('width' => 25));
                            $image_2 = $thumb_2->toHtml();
                        }

                        $casual_game_title = wfMessage('casual-game-picture-game')->escaped();

                        $output .= '<a href="' .
                            htmlspecialchars($pic_game_title->getFullURL('picGameAction=renderPermalink&id=' . $item['id'])) .
                            "\" rel=\"nofollow\">
								{$image_1}
								{$image_2}
								{$item['title']}
							</a>
							<span class=\"item-small\">{$casual_game_title}</span>";
                    }
                }

                $output .= '</p>';

                $x++;
            }

            $output .= '</div>';
        }

        return $output;
    }

    function sortItems($x, $y)
    {
        if ($x['timestamp'] == $y['timestamp']) {
            return 0;
        } elseif ($x['timestamp'] > $y['timestamp']) {
            return -1;
        } else {
            return 1;
        }
    }

    function getProfileSection($label, $value, $required = true)
    {
        global $wgUser, $wgOut;

        $output = '';
        if ($value || $required) {
            if (!$value) {
                if ($wgUser->getName() == $this->getTitle()->getText()) {
                    $value = wfMessage('profile-updated-personal')->escaped();
                } else {
                    $value = wfMessage('profile-not-provided')->escaped();
                }
            }

            $value = $wgOut->parse(trim($value), false);

            $output = "<div><b>{$label}</b>{$value}</div>";
        }
        return $output;
    }

    function getPersonalInfo($user_id, $user_name)
    {
        global $wgUser, $wgUserProfileDisplay;

        if ($wgUserProfileDisplay['personal'] == false) {
            return '';
        }

        $stats = new UserStats($user_id, $user_name);
        $stats_data = $stats->getUserStats();
        $user_level = new UserLevel($stats_data['points']);
        $level_link = Title::makeTitle(NS_HELP, wfMessage('user-profile-userlevels-link')->inContentLanguage()->text());

        $this->initializeProfileData($user_name);
        $profile_data = $this->profile_data;

        $defaultCountry = wfMessage('user-profile-default-country')->inContentLanguage()->text();

        // Current location
        $location = $profile_data['location_city'] . ', ' . $profile_data['location_state'];
        if ($profile_data['location_country'] != $defaultCountry) {
            if ($profile_data['location_city'] && $profile_data['location_state']) { // city AND state
                $location = $profile_data['location_city'] . ', ' .
                    $profile_data['location_state'] . ', ' .
                    $profile_data['location_country'];
                // Privacy
                $location = '';
                if (in_array('up_location_city', $this->profile_visible_fields)) {
                    $location .= $profile_data['location_city'] . ', ';
                }
                $location .= $profile_data['location_state'];
                if (in_array('up_location_country', $this->profile_visible_fields)) {
                    $location .= ', ' . $profile_data['location_country'] . ', ';
                }
            } elseif ($profile_data['location_city'] && !$profile_data['location_state']) { // city, but no state
                $location = '';
                if (in_array('up_location_city', $this->profile_visible_fields)) {
                    $location .= $profile_data['location_city'] . ', ';
                }
                if (in_array('up_location_country', $this->profile_visible_fields)) {
                    $location .= $profile_data['location_country'];
                }
            } elseif ($profile_data['location_state'] && !$profile_data['location_city']) { // state, but no city
                $location = $profile_data['location_state'];
                if (in_array('up_location_country', $this->profile_visible_fields)) {
                    $location .= ', ' . $profile_data['location_country'];
                }
            } else {
                $location = '';
                if (in_array('up_location_country', $this->profile_visible_fields)) {
                    $location .= $profile_data['location_country'];
                }
            }
        }

        if ($location == ', ') {
            $location = '';
        }

        // Hometown
        $hometown = $profile_data['hometown_city'] . ', ' . $profile_data['hometown_state'];
        if ($profile_data['hometown_country'] != $defaultCountry) {
            if ($profile_data['hometown_city'] && $profile_data['hometown_state']) { // city AND state
                $hometown = $profile_data['hometown_city'] . ', ' .
                    $profile_data['hometown_state'] . ', ' .
                    $profile_data['hometown_country'];
                $hometown = '';
                if (in_array('up_hometown_city', $this->profile_visible_fields)) {
                    $hometown .= $profile_data['hometown_city'] . ', ' . $profile_data['hometown_state'];
                }
                if (in_array('up_hometown_country', $this->profile_visible_fields)) {
                    $hometown .= ', ' . $profile_data['hometown_country'];
                }
            } elseif ($profile_data['hometown_city'] && !$profile_data['hometown_state']) { // city, but no state
                $hometown = '';
                if (in_array('up_hometown_city', $this->profile_visible_fields)) {
                    $hometown .= $profile_data['hometown_city'] . ', ';
                }
                if (in_array('up_hometown_country', $this->profile_visible_fields)) {
                    $hometown .= $profile_data['hometown_country'];
                }
            } elseif ($profile_data['hometown_state'] && !$profile_data['hometown_city']) { // state, but no city
                $hometown = $profile_data['hometown_state'];
                if (in_array('up_hometown_country', $this->profile_visible_fields)) {
                    $hometown .= ', ' . $profile_data['hometown_country'];
                }
            } else {
                $hometown = '';
                if (in_array('up_hometown_country', $this->profile_visible_fields)) {
                    $hometown .= $profile_data['hometown_country'];
                }
            }
        }

        if ($hometown == ', ') {
            $hometown = '';
        }

        $joined_data = $profile_data['real_name'] . $location . $hometown .
            $profile_data['birthday'] . $profile_data['occupation'] .
            $profile_data['websites'] . $profile_data['places_lived'] .
            $profile_data['schools'] . $profile_data['about'];
        $edit_info_link = SpecialPage::getTitleFor('UpdateProfile');

        // Privacy fields holy shit!
        $personal_output = '';
        if (in_array('up_real_name', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-real-name')->escaped(), $profile_data['real_name'], false);
        }

        $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-location')->escaped(), $location, false);
        $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-hometown')->escaped(), $hometown, false);

        if (in_array('up_birthday', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-birthday')->escaped(), $profile_data['birthday'], false);
        }

        if (in_array('up_occupation', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-occupation')->escaped(), $profile_data['occupation'], false);
        }

        if (in_array('up_websites', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-websites')->escaped(), $profile_data['websites'], false);
        }

        if (in_array('up_places_lived', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-places-lived')->escaped(), $profile_data['places_lived'], false);
        }

        if (in_array('up_schools', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-schools')->escaped(), $profile_data['schools'], false);
        }

        if (in_array('up_about', $this->profile_visible_fields)) {
            $personal_output .= $this->getProfileSection(wfMessage('user-personal-info-about-me')->escaped(), $profile_data['about'], false);
        }

        $output = '';
        if ($joined_data) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-personal-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if ($wgUser->getName() == $user_name) {
                $output .= '<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '">' .
                    wfMessage('user-edit-this')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="profile-info-container">' .
                $personal_output .
                '</div>';
        } elseif ($wgUser->getName() == $user_name) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-personal-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '">' .
                wfMessage('user-edit-this')->escaped() .
                '</a>
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="no-info-container">' .
                wfMessage('user-no-personal-info')->escaped() .
                '</div>';
        }

        return $output;
    }

    /**
     * Get the custom info (site-specific stuff) for a given user.
     *
     * @param $user_name String: user name whose custom info we should fetch
     * @return String: HTML
     */
    function getCustomInfo($user_name)
    {
        global $wgUser, $wgUserProfileDisplay;

        if ($wgUserProfileDisplay['custom'] == false) {
            return '';
        }

        $this->initializeProfileData($user_name);

        $profile_data = $this->profile_data;

        $joined_data = $profile_data['custom_1'] . $profile_data['custom_2'] .
            $profile_data['custom_3'] . $profile_data['custom_4'];
        $edit_info_link = SpecialPage::getTitleFor('UpdateProfile');

        $custom_output = '';
        if (in_array('up_custom_1', $this->profile_visible_fields)) {
            $custom_output .= $this->getProfileSection(wfMessage('custom-info-field1')->escaped(), $profile_data['custom_1'], false);
        }
        if (in_array('up_custom_2', $this->profile_visible_fields)) {
            $custom_output .= $this->getProfileSection(wfMessage('custom-info-field2')->escaped(), $profile_data['custom_2'], false);
        }
        if (in_array('up_custom_3', $this->profile_visible_fields)) {
            $custom_output .= $this->getProfileSection(wfMessage('custom-info-field3')->escaped(), $profile_data['custom_3'], false);
        }
        if (in_array('up_custom_4', $this->profile_visible_fields)) {
            $custom_output .= $this->getProfileSection(wfMessage('custom-info-field4')->escaped(), $profile_data['custom_4'], false);
        }

        $output = '';
        if ($joined_data) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('custom-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if ($wgUser->getName() == $user_name) {
                $output .= '<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '/custom">' .
                    wfMessage('user-edit-this')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="profile-info-container">' .
                $custom_output .
                '</div>';
        } elseif ($wgUser->getName() == $user_name) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('custom-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '/custom">' .
                wfMessage('user-edit-this')->escaped() .
                '</a>
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="no-info-container">' .
                wfMessage('custom-no-info')->escaped() .
                '</div>';
        }

        return $output;
    }

    /**
     * Get the interests (favorite movies, TV shows, music, etc.) for a given
     * user.
     *
     * @param $user_name String: user name whose interests we should fetch
     * @return String: HTML
     */
    function getInterests($user_name)
    {
        global $wgUser, $wgUserProfileDisplay;

        if ($wgUserProfileDisplay['interests'] == false) {
            return '';
        }

        $this->initializeProfileData($user_name);

        $profile_data = $this->profile_data;
        $joined_data = $profile_data['movies'] . $profile_data['tv'] .
            $profile_data['music'] . $profile_data['books'] .
            $profile_data['video_games'] .
            $profile_data['magazines'] . $profile_data['drinks'] .
            $profile_data['snacks'];
        $edit_info_link = SpecialPage::getTitleFor('UpdateProfile');

        $interests_output = '';
        if (in_array('up_movies', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-movies')->escaped(), $profile_data['movies'], false);
        }
        if (in_array('up_tv', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-tv')->escaped(), $profile_data['tv'], false);
        }
        if (in_array('up_music', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-music')->escaped(), $profile_data['music'], false);
        }
        if (in_array('up_books', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-books')->escaped(), $profile_data['books'], false);
        }
        if (in_array('up_video_games', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-video-games')->escaped(), $profile_data['video_games'], false);
        }
        if (in_array('up_magazines', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-magazines')->escaped(), $profile_data['magazines'], false);
        }
        if (in_array('up_snacks', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-snacks')->escaped(), $profile_data['snacks'], false);
        }
        if (in_array('up_drinks', $this->profile_visible_fields)) {
            $interests_output .= $this->getProfileSection(wfMessage('other-info-drinks')->escaped(), $profile_data['drinks'], false);
        }

        $output = '';
        if ($joined_data) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('other-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if ($wgUser->getName() == $user_name) {
                $output .= '<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '/personal">' .
                    wfMessage('user-edit-this')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="profile-info-container">' .
                $interests_output .
                '</div>';
        } elseif ($this->isOwner()) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('other-info-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars($edit_info_link->getFullURL()) . '/personal">' .
                wfMessage('user-edit-this')->escaped() .
                '</a>
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="no-info-container">' .
                wfMessage('other-no-info')->escaped() .
                '</div>';
        }
        return $output;
    }

    /**
     * Get the header for the social profile page, which includes the user's
     * points and user level (if enabled in the site configuration) and lots
     * more.
     *
     * @param $user_id   Integer: user ID
     * @param $user_name String: user name
     */
    function getProfileTop($user_id, $user_name)
    {
        global $wgUser, $wgLang;
        global $wgUserLevels;

        $stats = new UserStats($user_id, $user_name);
        $stats_data = $stats->getUserStats();
        $user_level = new UserLevel($stats_data['points']);
        $level_link = Title::makeTitle(NS_HELP, wfMessage('user-profile-userlevels-link')->inContentLanguage()->text());

        $this->initializeProfileData($user_name);
        $profile_data = $this->profile_data;

        // Variables and other crap
        $page_title = $this->getTitle()->getText();
        $title_parts = explode('/', $page_title);
        $user = $title_parts[0];
        $id = User::idFromName($user);
        $user_safe = urlencode($user);

        // Safe urls
        $add_relationship = SpecialPage::getTitleFor('AddRelationship');
        $remove_relationship = SpecialPage::getTitleFor('RemoveRelationship');
        $give_gift = SpecialPage::getTitleFor('GiveGift');
        $send_board_blast = SpecialPage::getTitleFor('BoardList');
        $update_profile = SpecialPage::getTitleFor('UpdateProfile');
        $watchlist = SpecialPage::getTitleFor('Watchlist');
        $contributions = SpecialPage::getTitleFor('Contributions', $user);
        $send_message = SpecialPage::getTitleFor('UserBoard');
        $upload_avatar = SpecialPage::getTitleFor('UploadAvatar');
        $user_page = Title::makeTitle(NS_USER, $user);
        $user_social_profile = Title::makeTitle(NS_USER_PROFILE, $user);
        $user_wiki = Title::makeTitle(NS_USER_WIKI, $user);

        if ($id != 0) {
            $relationship = UserRelationship::getUserRelationshipByID($id, $wgUser->getID());
        }
        $avatar = new wAvatar($this->user_id, 'l');

        wfDebug('profile type: ' . $profile_data['user_page_type'] . "\n");
        $output = '';

        if ($this->isOwner()) {
            $toggle_title = SpecialPage::getTitleFor('ToggleUserPage');
            // Cast it to an int because PHP is stupid.
            if (
                (int)$profile_data['user_page_type'] == 1 ||
                $profile_data['user_page_type'] === ''
            ) {
                $toggleMessage = wfMessage('user-type-toggle-old')->escaped();
            } else {
                $toggleMessage = wfMessage('user-type-toggle-new')->escaped();
            }
            $output .= '<div id="profile-toggle-button">
				<a href="' . htmlspecialchars($toggle_title->getFullURL()) . '" rel="nofollow">' .
                $toggleMessage . '</a>
			</div>';
        }

        $output .= '<div id="profile-image">' . $avatar->getAvatarURL() .
            '</div>';

        $output .= '<div id="profile-right">';

        $output .= '<div id="profile-title-container">
				<div id="profile-title">' .
            $user_name .
            '</div>';
        // Show the user's level and the amount of points they have if
        // UserLevels has been configured
        if ($wgUserLevels) {
            $output .= '<div id="points-level">
					<a href="' . htmlspecialchars($level_link->getFullURL()) . '">' .
                wfMessage(
                    'user-profile-points',
                    $wgLang->formatNum($stats_data['points'])
                )->escaped() .
                '</a>
					</div>
					<div id="honorific-level">
						<a href="' . htmlspecialchars($level_link->getFullURL()) . '" rel="nofollow">(' . $user_level->getLevelName() . ')</a>
					</div>';
        }
        $output .= '<div class="visualClear"></div>
			</div>
			<div class="profile-actions">';

        if ($this->isOwner()) {
            $output .= $wgLang->pipeList(array(
                '<a href="' . htmlspecialchars($update_profile->getFullURL()) . '">' . wfMessage('user-edit-profile')->escaped() . '</a>',
                '<a href="' . htmlspecialchars($upload_avatar->getFullURL()) . '">' . wfMessage('user-upload-avatar')->escaped() . '</a>',
                '<a href="' . htmlspecialchars($watchlist->getFullURL()) . '">' . wfMessage('user-watchlist')->escaped() . '</a>',
                ''
            ));
        } elseif ($wgUser->isLoggedIn()) {
            if ($relationship == false) {
                $output .= $wgLang->pipeList(array(
                    '<a href="' . htmlspecialchars($add_relationship->getFullURL('user=' . $user_safe . '&rel_type=1')) . '" rel="nofollow">' . wfMessage('user-add-friend')->escaped() . '</a>',
                    '<a href="' . htmlspecialchars($add_relationship->getFullURL('user=' . $user_safe . '&rel_type=2')) . '" rel="nofollow">' . wfMessage('user-add-foe')->escaped() . '</a>',
                    ''
                ));
            } else {
                if ($relationship == 1) {
                    $output .= $wgLang->pipeList(array(
                        '<a href="' . htmlspecialchars($remove_relationship->getFullURL('user=' . $user_safe)) . '">' . wfMessage('user-remove-friend')->escaped() . '</a>',
                        ''
                    ));
                }
                if ($relationship == 2) {
                    $output .= $wgLang->pipeList(array(
                        '<a href="' . htmlspecialchars($remove_relationship->getFullURL('user=' . $user_safe)) . '">' . wfMessage('user-remove-foe')->escaped() . '</a>',
                        ''
                    ));
                }
            }

            global $wgUserBoard;
            if ($wgUserBoard) {
                $output .= '<a href="' . htmlspecialchars($send_message->getFullURL('user=' . $wgUser->getName() . '&conv=' . $user_safe)) . '" rel="nofollow">' .
                    wfMessage('user-send-message')->escaped() . '</a>';
                $output .= wfMessage('pipe-separator')->escaped();
            }
            $output .= '<a href="' . htmlspecialchars($give_gift->getFullURL('user=' . $user_safe)) . '" rel="nofollow">' .
                wfMessage('user-send-gift')->escaped() . '</a>';
            $output .= wfMessage('pipe-separator')->escaped();
        }

        $output .= '<a href="' . htmlspecialchars($contributions->getFullURL()) . '" rel="nofollow">' . wfMessage('user-contributions')->escaped() . '</a> ';

        // Links to User:user_name from User_profile:
        if ($this->getTitle()->getNamespace() == NS_USER_PROFILE && $this->profile_data['user_id'] && $this->profile_data['user_page_type'] == 0) {
            $output .= '| <a href="' . htmlspecialchars($user_page->getFullURL()) . '" rel="nofollow">' .
                wfMessage('user-page-link')->escaped() . '</a> ';
        }

        // Links to User:user_name from User_profile:
        if ($this->getTitle()->getNamespace() == NS_USER && $this->profile_data['user_id'] && $this->profile_data['user_page_type'] == 0) {
            $output .= '| <a href="' . htmlspecialchars($user_social_profile->getFullURL()) . '" rel="nofollow">' .
                wfMessage('user-social-profile-link')->escaped() . '</a> ';
        }

        if ($this->getTitle()->getNamespace() == NS_USER && (!$this->profile_data['user_id'] || $this->profile_data['user_page_type'] == 1)) {
            $output .= '| <a href="' . htmlspecialchars($user_wiki->getFullURL()) . '" rel="nofollow">' .
                wfMessage('user-wiki-link')->escaped() . '</a>';
        }

        $output .= '</div>

		</div>';

        return $output;
    }

    /**
     * This is currently unused, seems to be a leftover from the ArmchairGM
     * days.
     *
     * @param $user_name String: user name
     * @return String: HTML
     */
    function getProfileImage($user_name)
    {
        global $wgUser;

        $avatar = new wAvatar($this->user_id, 'l');
        $avatarTitle = SpecialPage::getTitleFor('UploadAvatar');

        $output = '<div class="profile-image">';
        if ($wgUser->getName() == $this->user_name) {
            if (strpos($avatar->getAvatarImage(), 'default_') != false) {
                $caption = 'upload image';
            } else {
                $caption = 'new image';
            }
            $output .= '<a href="' . htmlspecialchars($avatarTitle->getFullURL()) . '" rel="nofollow">' .
                $avatar->getAvatarURL() . '<br />
					(' . $caption . ')
				</a>';
        } else {
            $output .= $avatar->getAvatarURL();
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Get the relationships for a given user.
     *
     * @param $user_name         String: name of the user whose relationships we want
     *                           to fetch
     * @param $rel_type          Integer: 1 for friends, 2 (or anything else than 1) for
     *                           foes
     */
    function getRelationships($user_name, $rel_type)
    {
        global $wgMemc, $wgUser, $wgUserProfileDisplay, $wgLang;

        // If not enabled in site settings, don't display
        if ($rel_type == 1) {
            if ($wgUserProfileDisplay['friends'] == false) {
                return '';
            }
        } else {
            if ($wgUserProfileDisplay['foes'] == false) {
                return '';
            }
        }

        $output = ''; // Prevent E_NOTICE

        $count = 4;
        $rel = new UserRelationship($user_name);
        $key = wfMemcKey('relationship', 'profile', "{$rel->user_id}-{$rel_type}");
        $data = $wgMemc->get($key);

        // Try cache
        if (!$data) {
            $friends = $rel->getRelationshipList($rel_type, $count);
            $wgMemc->set($key, $friends);
        } else {
            wfDebug("Got profile relationship type {$rel_type} for user {$user_name} from cache\n");
            $friends = $data;
        }

        $stats = new UserStats($rel->user_id, $user_name);
        $stats_data = $stats->getUserStats();
        $view_all_title = SpecialPage::getTitleFor('ViewRelationships');

        if ($rel_type == 1) {
            $relationship_count = $stats_data['friend_count'];
            $relationship_title = wfMessage('user-friends-title')->escaped();
        } else {
            $relationship_count = $stats_data['foe_count'];
            $relationship_title = wfMessage('user-foes-title')->escaped();
        }

        if (count($friends) > 0) {
            $x = 1;
            $per_row = 4;

            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' . $relationship_title . '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if (intval($relationship_count) > 4) {
                $output .= '<a href="' . htmlspecialchars($view_all_title->getFullURL('user=' . $user_name . '&rel_type=' . $rel_type)) .
                    '" rel="nofollow">' . wfMessage('user-view-all')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="action-left">';
            if (intval($relationship_count) > 4) {
                $output .= wfMessage('user-count-separator', $per_row, $relationship_count)->escaped();
            } else {
                $output .= wfMessage('user-count-separator', $relationship_count, $relationship_count)->escaped();
            }
            $output .= '</div>
				</div>
				<div class="visualClear"></div>
			</div>
			<div class="visualClear"></div>
			<div class="user-relationship-container">';

            foreach ($friends as $friend) {
                $user = Title::makeTitle(NS_USER, $friend['user_name']);
                $avatar = new wAvatar($friend['user_id'], 'ml');

                // Chop down username that gets displayed
                $user_name = $wgLang->truncate($friend['user_name'], 9, '..');

                $output .= "<a href=\"" . htmlspecialchars($user->getFullURL()) . "\" title=\"{$friend['user_name']}\" rel=\"nofollow\">
					{$avatar->getAvatarURL()}<br />
					{$user_name}
				</a>";

                if ($x == count($friends) || $x != 1 && $x % $per_row == 0) {
                    $output .= '<div class="visualClear"></div>';
                }

                $x++;
            }

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Gets the recent social activity for a given user.
     *
     * @param $user_name String: name of the user whose activity we want to fetch
     */
    function getActivity($user_name)
    {
        global $wgUser, $wgUserProfileDisplay, $wgExtensionAssetsPath, $wgUploadPath;

        // If not enabled in site settings, don't display
        if ($wgUserProfileDisplay['activity'] == false) {
            return '';
        }

        $output = '';

        $limit = 8;
        $rel = new UserActivity($user_name, 'user', $limit);
        $rel->setActivityToggle('show_votes', 0);
        $rel->setActivityToggle('show_gifts_sent', 1);

        /**
         * Get all relationship activity
         */
        $activity = $rel->getActivityList();

        if ($activity) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-recent-activity-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>';

            $x = 1;

            if (count($activity) < $limit) {
                $style_limit = count($activity);
            } else {
                $style_limit = $limit;
            }

            foreach ($activity as $item) {
                $item_html = '';
                $title = Title::makeTitle($item['namespace'], $item['pagetitle']);
                $user_title = Title::makeTitle(NS_USER, $item['username']);
                $user_title_2 = Title::makeTitle(NS_USER, $item['comment']);

                if ($user_title_2) {
                    $user_link_2 = '<a href="' . htmlspecialchars($user_title_2->getFullURL()) .
                        '" rel="nofollow">' . $item['comment'] . '</a>';
                }

                $comment_url = '';
                if ($item['type'] == 'comment') {
                    $comment_url = "#comment-{$item['id']}";
                }

                $page_link = '<b><a href="' . htmlspecialchars($title->getFullURL()) .
                    "{$comment_url}\">" . $title->getPrefixedText() . '</a></b> ';
                $b = new UserBoard(); // Easier than porting the time-related functions here
                $item_time = '<span class="item-small">' .
                    wfMessage('user-time-ago', $b->getTimeAgo($item['timestamp']))->escaped() .
                    '</span>';

                if ($x < $style_limit) {
                    $item_html .= '<div class="activity-item">
						<img src="' . $wgExtensionAssetsPath . '/SocialProfile/images/' .
                        UserActivity::getTypeIcon($item['type']) . '" alt="" border="0" />';
                } else {
                    $item_html .= '<div class="activity-item-bottom">
						<img src="' . $wgExtensionAssetsPath . '/SocialProfile/images/' .
                        UserActivity::getTypeIcon($item['type']) . '" alt="" border="0" />';
                }

                $viewGift = SpecialPage::getTitleFor('ViewGift');

                switch ($item['type']) {
                    case 'edit':
                        $item_html .= wfMessage('user-recent-activity-edit')->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">";
                        if ($item['comment']) {
                            $item_html .= "\"{$item['comment']}\"";
                        }
                        $item_html .= '</div>';
                        break;
                    case 'vote':
                        $item_html .= wfMessage('user-recent-activity-vote')->escaped() . " {$page_link} {$item_time}";
                        break;
                    case 'comment':
                        $item_html .= wfMessage('user-recent-activity-comment')->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">
								\"{$item['comment']}\"
							</div>";
                        break;
                    case 'gift-sent':
                        $gift_image = "<img src=\"{$wgUploadPath}/awards/" .
                            Gifts::getGiftImage($item['namespace'], 'm') .
                            '" border="0" alt="" />';
                        $item_html .= wfMessage('user-recent-activity-gift-sent')->escaped() . " {$user_link_2} {$item_time}
						<div class=\"item\">
							<a href=\"" . htmlspecialchars($viewGift->getFullURL("gift_id={$item['id']}")) . "\" rel=\"nofollow\">
								{$gift_image}
								{$item['pagetitle']}
							</a>
						</div>";
                        break;
                    case 'gift-rec':
                        $gift_image = "<img src=\"{$wgUploadPath}/awards/" .
                            Gifts::getGiftImage($item['namespace'], 'm') .
                            '" border="0" alt="" />';
                        $item_html .= wfMessage('user-recent-activity-gift-rec')->escaped() . " {$user_link_2} {$item_time}</span>
								<div class=\"item\">
									<a href=\"" . htmlspecialchars($viewGift->getFullURL("gift_id={$item['id']}")) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
                        break;
                    case 'system_gift':
                        $gift_image = "<img src=\"{$wgUploadPath}/awards/" .
                            SystemGifts::getGiftImage($item['namespace'], 'm') .
                            '" border="0" alt="" />';
                        $viewSystemGift = SpecialPage::getTitleFor('ViewSystemGift');
                        $item_html .= wfMessage('user-recent-system-gift')->escaped() . " {$item_time}
								<div class=\"user-home-item-gift\">
									<a href=\"" . htmlspecialchars($viewSystemGift->getFullURL("gift_id={$item['id']}")) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
                        break;
                    case 'friend':
                        $item_html .= wfMessage('user-recent-activity-friend')->escaped() .
                            " <b>{$user_link_2}</b> {$item_time}";
                        break;
                    case 'foe':
                        $item_html .= wfMessage('user-recent-activity-foe')->escaped() .
                            " <b>{$user_link_2}</b> {$item_time}";
                        break;
                    case 'system_message':
                        $item_html .= "{$item['comment']} {$item_time}";
                        break;
                    case 'user_message':
                        $item_html .= wfMessage('user-recent-activity-user-message')->escaped() .
                            " <b><a href=\"" . UserBoard::getUserBoardURL($user_title_2->getText()) .
                            "\" rel=\"nofollow\">{$item['comment']}</a></b>  {$item_time}
								<div class=\"item\">
								\"{$item['namespace']}\"
								</div>";
                        break;
                    case 'network_update':
                        $network_image = SportsTeams::getLogo($item['sport_id'], $item['team_id'], 's');
                        $item_html .= wfMessage('user-recent-activity-network-update')->escaped() .
                            '<div class="item">
									<a href="' . SportsTeams::getNetworkURL($item['sport_id'], $item['team_id']) .
                            "\" rel=\"nofollow\">{$network_image} \"{$item['comment']}\"</a>
								</div>";
                        break;
                }

                $item_html .= '</div>';

                if ($x <= $limit) {
                    $items_html_type['all'][] = $item_html;
                }
                $items_html_type[$item['type']][] = $item_html;

                $x++;
            }

            $by_type = '';
            foreach ($items_html_type['all'] as $item) {
                $by_type .= $item;
            }
            $output .= "<div id=\"recent-all\">$by_type</div>";
        }

        return $output;
    }

    function getGifts($user_name)
    {
        global $wgUser, $wgMemc, $wgUserProfileDisplay, $wgUploadPath;

        // If not enabled in site settings, don't display
        if ($wgUserProfileDisplay['gifts'] == false) {
            return '';
        }

        $output = '';

        // User to user gifts
        $g = new UserGifts($user_name);
        $user_safe = urlencode($user_name);

        // Try cache
        $key = wfMemcKey('user', 'profile', 'gifts', "{$g->user_id}");
        $data = $wgMemc->get($key);

        if (!$data) {
            wfDebug("Got profile gifts for user {$user_name} from DB\n");
            $gifts = $g->getUserGiftList(0, 4);
            $wgMemc->set($key, $gifts, 60 * 60 * 4);
        } else {
            wfDebug("Got profile gifts for user {$user_name} from cache\n");
            $gifts = $data;
        }

        $gift_count = $g->getGiftCountByUsername($user_name);
        $gift_link = SpecialPage::getTitleFor('ViewGifts');
        $per_row = 4;

        if ($gifts) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-gifts-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if ($gift_count > 4) {
                $output .= '<a href="' . htmlspecialchars($gift_link->getFullURL('user=' . $user_safe)) . '" rel="nofollow">' .
                    wfMessage('user-view-all')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="action-left">';
            if ($gift_count > 4) {
                $output .= wfMessage('user-count-separator', '4', $gift_count)->escaped();
            } else {
                $output .= wfMessage('user-count-separator', $gift_count, $gift_count)->escaped();
            }
            $output .= '</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="user-gift-container">';

            $x = 1;

            foreach ($gifts as $gift) {
                if ($gift['status'] == 1 && $user_name == $wgUser->getName()) {
                    $g->clearUserGiftStatus($gift['id']);
                    $wgMemc->delete($key);
                    $g->decNewGiftCount($wgUser->getID());
                }

                $user = Title::makeTitle(NS_USER, $gift['user_name_from']);
                $gift_image = '<img src="' . $wgUploadPath . '/awards/' .
                    Gifts::getGiftImage($gift['gift_id'], 'ml') .
                    '" border="0" alt="" />';
                $gift_link = $user = SpecialPage::getTitleFor('ViewGift');
                $class = '';
                if ($gift['status'] == 1) {
                    $class = 'class="user-page-new"';
                }
                $output .= '<a href="' . htmlspecialchars($gift_link->getFullURL('gift_id=' . $gift['id'])) . '" ' .
                    $class . " rel=\"nofollow\">{$gift_image}</a>";
                if ($x == count($gifts) || $x != 1 && $x % $per_row == 0) {
                    $output .= '<div class="visualClear"></div>';
                }

                $x++;
            }

            $output .= '</div>';
        }

        return $output;
    }

    function getAwards($user_name)
    {
        global $wgUser, $wgMemc, $wgUserProfileDisplay, $wgUploadPath;

        // If not enabled in site settings, don't display
        if ($wgUserProfileDisplay['awards'] == false) {
            return '';
        }

        $output = '';

        // System gifts
        $sg = new UserSystemGifts($user_name);

        // Try cache
        $sg_key = wfMemcKey('user', 'profile', 'system_gifts', "{$sg->user_id}");
        $data = $wgMemc->get($sg_key);
        if (!$data) {
            wfDebug("Got profile awards for user {$user_name} from DB\n");
            $system_gifts = $sg->getUserGiftList(0, 4);
            $wgMemc->set($sg_key, $system_gifts, 60 * 60 * 4);
        } else {
            wfDebug("Got profile awards for user {$user_name} from cache\n");
            $system_gifts = $data;
        }

        $system_gift_count = $sg->getGiftCountByUsername($user_name);
        $system_gift_link = SpecialPage::getTitleFor('ViewSystemGifts');
        $per_row = 4;

        if ($system_gifts) {
            $x = 1;

            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-awards-title')->escaped() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            if ($system_gift_count > 4) {
                $output .= '<a href="' . htmlspecialchars($system_gift_link->getFullURL('user=' . $user_name)) . '" rel="nofollow">' .
                    wfMessage('user-view-all')->escaped() . '</a>';
            }
            $output .= '</div>
					<div class="action-left">';
            if ($system_gift_count > 4) {
                $output .= wfMessage('user-count-separator', '4', $system_gift_count)->escaped();
            } else {
                $output .= wfMessage('user-count-separator', $system_gift_count, $system_gift_count)->escaped();
            }
            $output .= '</div>
					<div class="visualClear"></div>
				</div>
			</div>
			<div class="visualClear"></div>
			<div class="user-gift-container">';

            foreach ($system_gifts as $gift) {
                if ($gift['status'] == 1 && $user_name == $wgUser->getName()) {
                    $sg->clearUserGiftStatus($gift['id']);
                    $wgMemc->delete($sg_key);
                    $sg->decNewSystemGiftCount($wgUser->getID());
                }

                $gift_image = '<img src="' . $wgUploadPath . '/awards/' .
                    SystemGifts::getGiftImage($gift['gift_id'], 'ml') .
                    '" border="0" alt="" />';
                $gift_link = $user = SpecialPage::getTitleFor('ViewSystemGift');

                $class = '';
                if ($gift['status'] == 1) {
                    $class = 'class="user-page-new"';
                }
                $output .= '<a href="' . htmlspecialchars($gift_link->getFullURL('gift_id=' . $gift['id'])) .
                    '" ' . $class . " rel=\"nofollow\">
					{$gift_image}
				</a>";

                if ($x == count($system_gifts) || $x != 1 && $x % $per_row == 0) {
                    $output .= '<div class="visualClear"></div>';
                }
                $x++;
            }

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the user board for a given user.
     *
     * @param $user_id   Integer: user's ID number
     * @param $user_name String: user name
     */
    function getUserBoard($user_id, $user_name)
    {
        global $wgUser, $wgOut, $wgUserProfileDisplay;

        // Anonymous users cannot have user boards
        if ($user_id == 0) {
            return '';
        }

        // Don't display anything if user board on social profiles isn't
        // enabled in site configuration
        if ($wgUserProfileDisplay['board'] == false) {
            return '';
        }

        $output = ''; // Prevent E_NOTICE

        // Add JS
        $wgOut->addModules('ext.socialprofile.userprofile.js');

        $rel = new UserRelationship($user_name);
        $friends = $rel->getRelationshipList(1, 4);

        $stats = new UserStats($user_id, $user_name);
        $stats_data = $stats->getUserStats();
        $total = $stats_data['user_board'];

        // If the user is viewing their own profile or is allowed to delete
        // board messages, add the amount of private messages to the total
        // sum of board messages.
        if ($wgUser->getName() == $user_name || $wgUser->isAllowed('userboard-delete')) {
            $total = $total + $stats_data['user_board_priv'];
        }

        $output .= '<div class="user-section-heading">
			<div class="user-section-title">' .
            wfMessage('user-board-title')->escaped() .
            '</div>
			<div class="user-section-actions">
				<div class="action-right">';
        if ($wgUser->getName() == $user_name) {
            if ($friends) {
                $output .= '<a href="' . UserBoard::getBoardBlastURL() . '">' .
                    wfMessage('user-send-board-blast')->escaped() . '</a>';
            }
            if ($total > 10) {
                $output .= wfMessage('pipe-separator')->escaped();
            }
        }
        if ($total > 10) {
            $output .= '<a href="' . UserBoard::getUserBoardURL($user_name) . '">' .
                wfMessage('user-view-all')->escaped() . '</a>';
        }
        $output .= '</div>
				<div class="action-left">';
        if ($total > 10) {
            $output .= wfMessage('user-count-separator', '10', $total)->escaped();
        } elseif ($total > 0) {
            $output .= wfMessage('user-count-separator', $total, $total)->escaped();
        }
        $output .= '</div>
				<div class="visualClear"></div>
			</div>
		</div>
		<div class="visualClear"></div>';

        if ($wgUser->getName() !== $user_name) {
            if ($wgUser->isLoggedIn() && !$wgUser->isBlocked()) {
                $output .= '<div class="user-page-message-form">
						<input type="hidden" id="user_name_to" name="user_name_to" value="' . addslashes($user_name) . '" />
						<span class="profile-board-message-type">' .
                    wfMessage('userboard_messagetype')->escaped() .
                    '</span>
						<select id="message_type">
							<option value="0">' .
                    wfMessage('userboard_public')->escaped() .
                    '</option>
							<option value="1">' .
                    wfMessage('userboard_private')->escaped() .
                    '</option>
						</select><p>
						<textarea name="message" id="message" cols="43" rows="4"></textarea>
						<div class="user-page-message-box-button">
							<input type="button" value="' . wfMessage('userboard_sendbutton')->escaped() . '" class="site-button" />
						</div>
					</div>';
            } else {
                $login_link = SpecialPage::getTitleFor('Userlogin');
                $output .= '<div class="user-page-message-form">' .
                    wfMessage('user-board-login-message', $login_link->getFullURL())->text() .
                    '</div>';
            }
        }

        $output .= '<div id="user-page-board">';
        $b = new UserBoard();
        $output .= $b->displayMessages($user_id, 0, 10);
        $output .= '</div>';

        return $output;
    }

    /**
     * Gets the user's fanboxes if $wgEnableUserBoxes = true; and
     * $wgUserProfileDisplay['userboxes'] = true; and the FanBoxes extension is
     * installed.
     *
     * @param $user_name String: user name
     * @return String: HTML
     */
    function getFanBoxes($user_name)
    {
        global $wgOut, $wgUser, $wgMemc, $wgUserProfileDisplay, $wgEnableUserBoxes;

        if (!$wgEnableUserBoxes || $wgUserProfileDisplay['userboxes'] == false) {
            return '';
        }

        // Add CSS & JS
        $wgOut->addModules('ext.fanBoxes');

        $output = '';
        $f = new UserFanBoxes($user_name);

        // Try cache
        /*
        $key = wfMemcKey( 'user', 'profile', 'fanboxes', "{$f->user_id}" );
        $data = $wgMemc->get( $key );

        if ( !$data ) {
            wfDebug( "Got profile fanboxes for user {$user_name} from DB\n" );
            $fanboxes = $f->getUserFanboxes( 0, 10 );
            $wgMemc->set( $key, $fanboxes );
        } else {
            wfDebug( "Got profile fanboxes for user {$user_name} from cache\n" );
            $fanboxes = $data;
        }
        */

        $fanboxes = $f->getUserFanboxes(0, 10);

        $fanbox_count = $f->getFanBoxCountByUsername($user_name);
        $fanbox_link = SpecialPage::getTitleFor('ViewUserBoxes');
        $per_row = 1;

        if ($fanboxes) {
            $output .= '<div class="user-section-heading">
				<div class="user-section-title">' .
                wfMessage('user-fanbox-title')->plain() .
                '</div>
				<div class="user-section-actions">
					<div class="action-right">';
            // If there are more than ten fanboxes, display a "View all" link
            // instead of listing them all on the profile page
            if ($fanbox_count > 10) {
                $output .= Linker::link(
                    $fanbox_link,
                    wfMessage('user-view-all')->plain(),
                    array(),
                    array('user' => $user_name)
                );
            }
            $output .= '</div>
					<div class="action-left">';
            if ($fanbox_count > 10) {
                $output .= wfMessage('user-count-separator')->numParams(10, $fanbox_count)->parse();
            } else {
                $output .= wfMessage('user-count-separator')->numParams($fanbox_count, $fanbox_count)->parse();
            }
            $output .= '</div>
					<div class="visualClear"></div>

				</div>
			</div>
			<div class="visualClear"></div>

			<div class="user-fanbox-container clearfix">';

            $x = 1;
            $tagParser = new Parser();
            foreach ($fanboxes as $fanbox) {
                $check_user_fanbox = $f->checkIfUserHasFanbox($fanbox['fantag_id']);

                if ($fanbox['fantag_image_name']) {
                    $fantag_image_width = 45;
                    $fantag_image_height = 53;
                    $fantag_image = wfFindFile($fanbox['fantag_image_name']);
                    $fantag_image_url = '';
                    if (is_object($fantag_image)) {
                        $fantag_image_url = $fantag_image->createThumb(
                            $fantag_image_width,
                            $fantag_image_height
                        );
                    }
                    $fantag_image_tag = '<img alt="" src="' . $fantag_image_url . '" />';
                }

                if ($fanbox['fantag_left_text'] == '') {
                    $fantag_leftside = $fantag_image_tag;
                } else {
                    $fantag_leftside = $fanbox['fantag_left_text'];
                    $fantag_leftside = $tagParser->parse(
                        $fantag_leftside, $this->getTitle(),
                        $wgOut->parserOptions(), false
                    );
                    $fantag_leftside = $fantag_leftside->getText();
                }

                $leftfontsize = '10px';
                $rightfontsize = '11px';
                if ($fanbox['fantag_left_textsize'] == 'mediumfont') {
                    $leftfontsize = '11px';
                }

                if ($fanbox['fantag_left_textsize'] == 'bigfont') {
                    $leftfontsize = '15px';
                }

                if ($fanbox['fantag_right_textsize'] == 'smallfont') {
                    $rightfontsize = '10px';
                }

                if ($fanbox['fantag_right_textsize'] == 'mediumfont') {
                    $rightfontsize = '11px';
                }

                // Get permalink
                $fantag_title = Title::makeTitle(NS_FANTAG, $fanbox['fantag_title']);
                $right_text = $fanbox['fantag_right_text'];
                $right_text = $tagParser->parse(
                    $right_text, $this->getTitle(), $wgOut->parserOptions(), false
                );
                $right_text = $right_text->getText();

                // Output fanboxes
                $output .= "<div class=\"fanbox-item\">
					<div class=\"individual-fanbox\" id=\"individualFanbox" . $fanbox['fantag_id'] . "\">
						<div class=\"show-message-container-profile\" id=\"show-message-container" . $fanbox['fantag_id'] . "\">
							<a class=\"perma\" style=\"font-size:8px; color:" . $fanbox['fantag_right_textcolor'] . "\" href=\"" . htmlspecialchars($fantag_title->getFullURL()) . "\" title=\"{$fanbox['fantag_title']}\">" . wfMessage('fanbox-perma')->plain() . "</a>
							<table class=\"fanBoxTableProfile\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
								<tr>
									<td id=\"fanBoxLeftSideOutputProfile\" style=\"color:" . $fanbox['fantag_left_textcolor'] . "; font-size:$leftfontsize\" bgcolor=\"" . $fanbox['fantag_left_bgcolor'] . "\">" . $fantag_leftside . "</td>
									<td id=\"fanBoxRightSideOutputProfile\" style=\"color:" . $fanbox['fantag_right_textcolor'] . "; font-size:$rightfontsize\" bgcolor=\"" . $fanbox['fantag_right_bgcolor'] . "\">" . $right_text . "</td>
								</tr>
							</table>
						</div>
					</div>";

                if ($wgUser->isLoggedIn()) {
                    if ($check_user_fanbox == 0) {
                        $output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
							<table cellpadding="0" cellspacing="0" align="center">
								<tr>
									<td style="font-size:10px">' .
                            wfMessage('fanbox-add-fanbox')->plain() .
                            '</td>
								</tr>
								<tr>
									<td align="center">
										<input type="button" class="fanbox-add-button-half" value="' . wfMessage('fanbox-add')->plain() . '" size="10" />
										<input type="button" class="fanbox-cancel-button" value="' . wfMessage('cancel')->plain() . '" size="10" />
									</td>
								</tr>
							</table>
						</div>';
                    } else {
                        $output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
							<table cellpadding="0" cellspacing="0" align="center">
								<tr>
									<td style="font-size:10px">' .
                            wfMessage('fanbox-remove-fanbox')->plain() .
                            '</td>
								</tr>
								<tr>
									<td align="center">
										<input type="button" class="fanbox-remove-button-half" value="' . wfMessage('fanbox-remove')->plain() . '" size="10" />
										<input type="button" class="fanbox-cancel-button" value="' . wfMessage('cancel')->plain() . '" size="10" />
									</td>
								</tr>
							</table>
						</div>';
                    }
                }

                // Show a message to anonymous users, prompting them to log in
                if ($wgUser->getID() == 0) {
                    $output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
						<table cellpadding="0" cellspacing="0" align="center">
							<tr>
								<td style="font-size:10px">' .
                        wfMessage('fanbox-add-fanbox-login')->parse() .
                        '</td>
							</tr>
							<tr>
								<td align="center">
									<input type="button" class="fanbox-cancel-button" value="' . wfMessage('cancel')->plain() . '" size="10" />
								</td>
							</tr>
						</table>
					</div>';
                }

                $output .= '</div>';

                $x++;
            }

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Initialize UserProfile data for the given user if that hasn't been done
     * already.
     *
     * @param $username String: name of the user whose profile data to initialize
     */
    private function initializeProfileData($username)
    {
        if (!$this->profile_data) {
            $profile = new UserProfile($username);
            $this->profile_data = $profile->getProfile();
        }
    }
}
