<?php
/**
 * MediaWikiBootstrap is a simple Mediawiki Skin build on Bootstrap 3.
 *
 * @file
 * @ingroup Skins
 * @author Nasir Khan Saikat http://nasirkhn.com
 */
if (!defined('MEDIAWIKI')) {
    die(-1);
}

/**
 * SkinTemplate class for MediaWikiBootstrap skin
 * @ingroup Skins
 */
class SkinMediaWikiBootstrap1 extends SkinTemplate {

    public $skinname        = 'mediawikibootstrap1';
    public $stylename       = 'mediawikibootstrap1';
    public $template        = 'MediaWikiBootstrapTemplate1';
    public $useHeadElement  = true;
    
    public $sidebarIsOn = false;

    /**
     * Initializes output page and sets up skin-specific parameters
     * @param $out OutputPage object to initialize
     */
    public function initPage(OutputPage $out) {
        global $wgLocalStylePath;

        parent::initPage($out);

        // Append CSS which includes IE only behavior fixes for hover support -
        // this is better than including this in a CSS fille since it doesn't
        // wait for the CSS file to load before fetching the HTC file.
        $min = $this->getRequest()->getFuzzyBool('debug') ? '' : '.min';
        $out->addHeadItem('csshover', '<!--[if lt IE 7]><style type="text/css">body{behavior:url("' .
                htmlspecialchars($wgLocalStylePath) .
                "/{$this->stylename}/csshover{$min}.htc\")}</style><![endif]-->"
        );

        //$out->addHeadItem('responsive', '<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        $out->addModuleScripts('ext.socialprofile.userinfo.js');
        $out->addModuleScripts('ext.page.contribute.js');
        $out->addModuleScripts('skins.mediawikibootstrap1');
    }

    /**
     * Loads skin and user CSS files.
     * @param OutputPage $out
     */
   function setupSkinUserCss( OutputPage $out ) {
        parent::setupSkinUserCss( $out );
        
        $styles = array( 'mediawiki.skinning.interface', 'skins.mediawikibootstrap1','ext.socialprofile.userinfo.css' );
        wfRunHooks( 'SkinMediawikibootstrapStyleModules', array( $this, &$styles ) );
        $out->addModuleStyles( $styles );
    }

}

/**
 * Template class of the MediaWikiBootstrap Skin
 * @ingroup Skins
 */
class MediaWikiBootstrapTemplate1 extends BaseTemplate {

    /**
     * Outputs the entire contents of the page
     */
    public function execute() {
        global $wgGroupPermissions;
        global $wgVectorUseIconWatch;
        global $wgSearchPlacement;
        global $wgMediaWikiBootstrapSkinLoginLocation;        
        global $wgMediaWikiBootstrapSkinAnonNavbar;
        global $wgMediaWikiBootstrapSkinUseStandardLayout;
        global $wgSiteId;
		global $wgTitle, $wgRequest;
        global $wgEnv;

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();
        
        // search box locations 
        if (!$wgSearchPlacement) {
            $wgSearchPlacement['top-nav'] = true;
            $wgSearchPlacement['nav'] = true;
        }
        
        // Build additional attributes for navigation urls
        $nav = $this->data['content_navigation'];

        if ( $wgVectorUseIconWatch ) {
            $mode = $this->getSkin()->getUser()->isWatched( $this->getSkin()->getRelevantTitle() )
                    ? 'unwatch'
                    : 'watch';
            if ( isset( $nav['actions'][$mode] ) ){
                $nav['views'][$mode] = $nav['actions'][$mode];
                $nav['views'][$mode]['class'] = rtrim( 'icon ' . $nav['views'][$mode]['class'], ' ' );
                $nav['views'][$mode]['primary'] = true;
                unset( $nav['actions'][$mode] );
            }
        }

        $xmlID = '';
        foreach ($nav as $section => $links) {
            foreach ($links as $key => $link) {
                if ($section == 'views' && !( isset($link['primary']) && $link['primary'] )) {
                    $link['class'] = rtrim('collapsible ' . $link['class'], ' ');
                }

                $xmlID = isset($link['id']) ? $link['id'] : 'ca-' . $xmlID;
                $nav[$section][$key]['attributes'] = ' id="' . Sanitizer::escapeId($xmlID) . '"';
                if ($link['class']) {
                    $nav[$section][$key]['attributes'] .=
                            ' class="' . htmlspecialchars($link['class']) . '"';
                    unset($nav[$section][$key]['class']);
                }
                if (isset($link['tooltiponly']) && $link['tooltiponly']) {
                    $nav[$section][$key]['key'] = Linker::tooltip($xmlID);
                } else {
                    $nav[$section][$key]['key'] = Xml::expandAttributes(Linker::tooltipAndAccesskeyAttribs($xmlID));
                }
            }
        }
        $this->data['namespace_urls'] = $nav['namespaces'];
        $this->data['view_urls'] = array_merge($nav['views'],$nav['actions']);

        // Output HTML Page
        $this->html('headelement');
        global $wgSiteGameTitle,$wgWikiname,$wgIsLogin,$wgUser,$search_str,$wgJoymeUserInfo;
        
        $wgSiteGameTitle = empty($wgSiteGameTitle)?'本wiki':$wgSiteGameTitle;
        $search_str = '在本WIKI中搜索';

        if($wgWikiname == "home"){
            $logouturl = "http://passport.joyme.".$wgEnv."/auth/logout?reurl=http://wiki.joyme.".$wgEnv;
        }else{
            $logouturl = "http://passport.joyme.".$wgEnv."/auth/logout?reurl=  ";
        }

        
        ?>
        <!-- 将这段话添加到heade结束标签之前  开始-->
        <script type="text/javascript">
            document.domain=window.location.hostname;
            window.addEventListener("DOMContentLoaded", function (){
                document.addEventListener("touchstart", function (){return false}, true)
            }, true);
        </script>
        <!-- 将这段话添加到heade结束标签之前  结束-->
        <?php if ($wgGroupPermissions['*']['edit'] || $wgMediaWikiBootstrapSkinAnonNavbar || $this->data['loggedin']) : ?>
        <?php endif; ?>
        <!-- header-box 全站导航 开始-->
        <div class="header-box" >
            <div class="navbar navbar-default" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <?php
                        if ($this->data['loggedin']) {
                            ?>
                            <?php } ?>
                            <ul class="nav navbar-nav web-hide joyme-nav-l">
                                <li><a href="http://www.joyme.com/">返回着迷首页>></a></li>
                                <li><a href="http://www.joyme.com/news/official/">手游资讯</a></li>
                                <li><a href="http://www.joyme.com/news/reviews/">着迷评测</a></li>
                                <li><a href="http://wiki.joyme.com/">着迷WIKI</a></li>
                                <li><a href="http://www.joyme.com/gift">礼包中心</a></li>
                                <li><a href="http://www.joyme.com/news/blue/">精品推荐</a></li>
                                <!--<li><a href="http://html.joyme.com/mobile/gameguides.html">应用下载</a></li>-->
                                <!--<li><a target="_blank" href="http://wanba.joyme.com/">精彩问答</a></li>-->
                            </ul>

                            <div class="nav navbar-nav  joyme-nav-r navbar-right">
                                <?php
                                //if (!$wgIsLogin) {
                                    ?>
                                    <!-- 未登录 开始 -->
                                    <!--<div class="unloading fn-clear">
                                        <a href="javascript:;" class="login login-mask">登录</a>
                                        <a href="javascript:;" class="register register-mask">注册</a>
                                    </div>-->
                                    <span class="fn-clear">
                                        <script>
                                            document.write(unescape("%3Cscript src='http://passport.joyme.<?php echo $wgEnv ?>/auth/header/userinfo?t=wiki%26v=" + Math.random() + "' type='text/javascript'%3E%3C/script%3E"));
                                        </script>
                                    </span>
                                    <!-- 未登录 结束 -->
                                <?php
                                /*} else {
                                        if($wgJoymeUserInfo['icon']&&$wgJoymeUserInfo['nick']){
                                            $iconurl = $wgJoymeUserInfo['icon'];
                                            $nick = $wgJoymeUserInfo['nick'];
                                        }else{
                                            $joymewikiuser = new JoymeWikiUser();
                                            $joymewikiuser->getProfile($wgUser->getId());
                                            $iconurl = $joymewikiuser->icon;
                                            $nick = $joymewikiuser->nick;
                                        }
                                    ?>
                                    <!-- 登陆之后 开始-->
                                    <div class="lading" >
                                        <a href="/home/用户:<?php echo $nick; ?>" class="user-icon"><img src="<?php echo $iconurl; ?>"></a>
                                        <div class="dropdown setting-box dropdown-nor web-hide pull-down">
                                            <a data-toggle="dropdown" class="dropdown-toggle" role="button">
			                                     <b class="caret setting-caret web-hide"></b>
			                                </a>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="/home/特殊:收藏列表"
                                                        <?php if($wgWikiname != "home"):?>
                                                        target="_blank"
                                                    <?php endif;?>>我的收藏</a></li>
                                                <li><a href="/home/特殊:账号安全">设置</a></li>
                                                <li><a href="<?php echo $logouturl;?>">退出</a></li>
                                            </ul>
                                        </div>
                                        <div class="dropdown notice-box pull-down dropdown-nor">
                                            <?php if($wgIsLogin){GetSystemMessageClass::getIndex();} $model = new SpecialAboutMe(); $groupData = $model->getGroupData();?>
                                            <a data-toggle="dropdown" class="dropdown-toggle" role="button">
                                                <i class="fa fa-bell-o col-xs-show" aria-hidden="true"></i>
													  提醒
                                                <?php if($groupData['is_new_remind']):?>
                                                    <b class="caret"></b>
                                                <?php endif;?>
                                            </a>
                                            <?php

                                                $morenum = '99';
                                                if($groupData['article-cite-my']>0 && $groupData['article-cite-my']<100){
                                                    $article_cite_my = '<b>'.$groupData['article-cite-my'].'</b>';
                                                }elseif($groupData['article-cite-my']>99){
                                                    $article_cite_my = '<b class="on">'.$morenum.'</b>';
                                                }else{
                                                    $article_cite_my = '';
                                                }
                                                if($groupData['article-comments']>0 && $groupData['article-comments']<100){
                                                    $article_comments = '<b>'.$groupData['article-comments'].'</b>';
                                                }elseif($groupData['article-comments']>99){
                                                    $article_comments = '<b class="on">'.$morenum.'</b>';
                                                }else{
                                                    $article_comments = '';
                                                }
                                                if($groupData['article-thumb-up']>0 && $groupData['article-thumb-up']<100){
                                                    $article_thumb_up = '<b>'.$groupData['article-thumb-up'].'</b>';
                                                }elseif($groupData['article-thumb-up']>99){
                                                    $article_thumb_up = '<b class="on">'.$morenum.'</b>';
                                                }else{
                                                    $article_thumb_up = '';
                                                }
                                                if($groupData['article-consider-me']>0 && $groupData['article-consider-me']<100){
                                                    $article_consider_me = '<b>'.$groupData['article-consider-me'].'</b>';
                                                }elseif($groupData['article-consider-me']>99){
                                                    $article_consider_me = '<b class="on">'.$morenum.'</b>';
                                                }else{
                                                    $article_consider_me = '';
                                                }
                                                if($groupData['echo-system-message']>0 && $groupData['echo-system-message']<100){
                                                    $echo_system_message = '<b>'.$groupData['echo-system-message'].'</b>';
                                                }elseif($groupData['echo-system-message']>99){
                                                    $echo_system_message = '<b class="on">'.$morenum.'</b>';
                                                }else{
                                                    $echo_system_message = '';
                                                }
                                            ?>

                                            <ul class="dropdown-menu notice-list" aria-labelledby="提醒" role="menu">
                                                <li><a href="<?=htmlspecialchars('http://wiki.joyme.'.$wgEnv.'/home/index.php?title=%E7%89%B9%E6%AE%8A:%E5%85%B3%E4%BA%8E%E6%88%91%E7%9A%84&about_type=article-cite-my')?>">@我的 <?=$article_cite_my?></a></li>
                                                <li><a href="<?=htmlspecialchars('http://wiki.joyme.'.$wgEnv.'/home/index.php?title=%E7%89%B9%E6%AE%8A:%E5%85%B3%E4%BA%8E%E6%88%91%E7%9A%84&about_type=article-comments')?>">评论 <?=$article_comments?></a></li>
                                                <li><a href="<?=htmlspecialchars('http://wiki.joyme.'.$wgEnv.'/home/index.php?title=%E7%89%B9%E6%AE%8A:%E5%85%B3%E4%BA%8E%E6%88%91%E7%9A%84&about_type=article-thumb-up')?>">点赞 <?=$article_thumb_up?></a></li>
                                                <li><a href="<?=htmlspecialchars('http://wiki.joyme.'.$wgEnv.'/home/index.php?title=%E7%89%B9%E6%AE%8A:%E5%85%B3%E4%BA%8E%E6%88%91%E7%9A%84&about_type=article-consider-me')?>">关注 <?=$article_consider_me?></a></li>
                                                <li><a href="<?=htmlspecialchars('http://wiki.joyme.'.$wgEnv.'/home/index.php?title=%E7%89%B9%E6%AE%8A:%E5%85%B3%E4%BA%8E%E6%88%91%E7%9A%84&about_type=echo-system-message')?>">系统 <?=$echo_system_message?></a></li>
                                            </ul>
                                        </div>
                                        <?php 
                                        $stats = new UserStats( $wgUser->getId() );
                                        $stats_data = $stats->getUserStats();
                                        $boardtotal = intval($stats_data['user_board'] + $stats_data['user_board_priv']);
                                        $boardtotal = $boardtotal>99?99:$boardtotal;
                                        ?>
                                        <a id="boardtotal_a" href="<?=htmlspecialchars('/home/特殊:私信列表')?>" class="letter "><?php if($boardtotal >0 ){echo '<b id="boardtotal" '.($boardtotal>99?'class="on"':'').'>'. $boardtotal.'</b>';} ?><i class="fa fa-envelope-o" aria-hidden="true"></i>私信</a>
                                        
                                        <div class="dropdown setting-box pull-down dropdown-nor mstyle">
			                                <!--<a data-toggle="dropdown" class="dropdown-toggle" role="button">
			                                     <cite class="navbar-toggle">
			                                         <span class="icon-bar"></span>
			                                         <span class="icon-bar"></span>
			                                         <span class="icon-bar"></span>
			                                     </cite>
			                                </a>-->
                                            <a  data-toggle="dropdown" class="dropdown-toggle" role="button" ><i class="fa fa-cog fa-fw" aria-hidden="true"></i></a>
			                                <ul class="dropdown-menu" role="menu"> 
			                                    <li><a href="/home/特殊:收藏列表"
                                                    <?php if($wgWikiname != "home"):?>
                                                        target="_blank"
                                                    <?php endif;?> >我的收藏</a></li>
                                                <li><a href="/home/特殊:账号安全">设置</a></li>
                                                <li><a href="<?php echo $logouturl;?>">退出</a></li>
			                                </ul>
			                            </div>
                            
                                    </div>
                                    <!-- 登陆之后 结束-->
                                <?php
                            }*/
                            ?>
                        </div>
                        <!--   <a class="navbar-brand visible-xs" href="#"><?php $this->html('sitename'); ?></a> -->
                          <a class="navbar-brand visible-xs joyme-logo" href="http://m.joyme.com"><span class="lt-style">&lt; </span> 返回着迷网首页</a>
                    </div>
                    <!--/.nav-collapse -->
                </div><!--/.container-fluid -->
            </div> <!-- /navbar -->
        </div>
        <!-- header-box 全站导航 结束-->
        <!-- WIKI区 开始-->
        <div id="wrapper">
            <!-- 导航区 开始-->
            <section id="mw-navigation">
                <!-- 新导航-->
                <!-- 移动端WIKI头部 开始-->
                <div class="navbar-header2">
                    <div id="sidebar-menu">
		   	 <span class="icon-bar-gray"></span>
		    </div>
		    <button type="button" class="header-search"></button>
                    <div id="sidebar-menu-bg" class="mengceng-share" class="display:none"></div>
                    
                            <div class="ysw-wiki" >
                                <!--<?=$wgSiteGameTitle;?>-->
                                <!--<a href=""><img src="" alt=""/></a>-->
                                <a href="<?php echo '/'.$wgWikiname.'/'?>" class="wiki-logo"></a>
                                <a href="http://app.joyme.com/" class="support-ask"></a>
                            </div>
                    
                    
                    <div class="fn-clear"></div>
                    
                </div>
                <div class="header-search-text">
                    <form action="<?php $this->text('wgScript') ?>">
                        <input type="search" accesskey="f" title="<?php $this->text('searchtitle'); ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php echo $search_str; ?>'" placeholder="<?php echo $search_str; ?>" name="search" value="<?php echo htmlspecialchars($this->data['search']); ?>">
                    </form>
                </div>
                <!-- 移动端WIKI头部 结束-->
                <!-- 导航（移动+左侧） 开始-->
                <div id="joymewiki-navigation" class="section-left2">
                    <div class="wiki-person">
                    	<?php 
	                        $site_info = JoymeSite::getSiteInfo($wgSiteId);
	                        if($site_info){
	                        	$site_page_count = $site_info[1]['page_count']>=1000?round($site_info[1]['page_count']/1000,1).'K':$site_info[1]['page_count'];
	                        	$site_edit_count = $site_info[1]['edit_count']>=1000?round($site_info[1]['edit_count']/1000,1).'K':$site_info[1]['edit_count'];
	                        	$site_follow_usercount = $site_info[1]['follow_usercount']>=1000?round($site_info[1]['follow_usercount']/1000,1).'K':$site_info[1]['follow_usercount'];
	                        }else{
	                        	$site_page_count = 0;
	                        	$site_edit_count = 0;
	                        	$site_follow_usercount = 0;
	                        }
                        ?>
                        <div id="joyme_site_follow_status" class="name-focus">
                        	<b><a href="/<?=$wgWikiname?>/" class="user-title"><?=$wgSiteGameTitle;?></a></b>
                        	<?php
                        		if($wgIsLogin){
                        			$ret = JoymeWikiUser::getUserFollowSite($wgUser->getId(),$wgSiteId);
                        			if($ret && $ret->status==1){
                        				echo '<a id="joymesiteuserfollow" name="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow_ygz()"></a>';
                        				$followstr = ' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow_ygz()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1">'.$site_follow_usercount.'</em> 关注</a>';
                        			}elseif($ret && ($ret->status==2 || $ret->status==3)){
                        				echo '<a id="joymesiteuserfollow" name="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow()"></a>';
                        				$followstr = ' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1">'.$site_follow_usercount.'</em> 关注</a>';
                        			}else{
                        				echo '<a id="joymesiteuserfollow" name="joymesiteuserfollow" href="javascript:;" class="sf-gz usersitefollow" onclick="mw.joymesiteuserfollow()"></a>';
                        				$followstr = ' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz" onclick="mw.joymesiteuserfollow()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1">'.$site_follow_usercount.'</em> 关注</a>';
                        			}
                        		}else{
                        			echo '<a id="joymesiteuserfollow" name="joymesiteuserfollow" href="javascript:;" class="sf-gz usersitefollow" onclick="mw.joymesiteuserfollow()"></a>';
                        			$followstr = ' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz" onclick="mw.joymesiteuserfollow()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1">'.$site_follow_usercount.'</em> 关注</a>';
                        		}
                        		
                        	?>
                        </div>
                        <p class="focus-num"><em class="joyme_wiki_word_info" id="joyme_wiki_word_info2"><?=$site_page_count?></em>词条已创建 ,<em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info2"><?=$site_follow_usercount?></em>用户已关注</p>
                    </div>
                    <div class="nav-left-tj fn-clear">
                        <?php echo SpecialRecommendArea::getAreaContent( '推荐区1' );?>
                        <span class="tj-bg"></span>
                    </div>
                    <div class="wiki-search visible-md visible-lg">
                        <?php  $this->renderNavigation(array('TOP-NAV-SEARCH')); ?>
                        <div class="fn-clear"></div>
                    </div>
                    <?php  $this->renderNavigation(array('SIDEBARNAV')); ?>
                </div>
                <!-- WIKI左侧导航 结束-->
                <!-- WIKI顶部导航 开始-->
                <div class="tl-ej-nav">
                    <div class="ej-nav-wrap">
					<div class="nav-tool-con">
					<div class="nav-tool"></div>
                            <div class="zd-tool">
                                <div class="zd-tool-con">
                                    <h2 class="tool-title">站点工具</h2>
                                    <ul class="tool-list fn-clear">
                                        <?php if($wgIsLogin):?>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '上传文件' )->getFullURL())?>">上传文件</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '最近更改' )->getFullURL())?>">最近更改</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '文件列表' )->getFullURL())?>">文件列表</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '特殊页面' )->getFullURL())?>">特殊页面</a></li>
                                        <?php else: ?>
<!--                                            <li><a href="javascript:mw.loginbox.login();">上传文件</a></li>-->
                                            <li><a href="javascript:loginDiv();">上传文件</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '最近更改' )->getFullURL())?>">最近更改</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '文件列表' )->getFullURL())?>">文件列表</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( '特殊页面' )->getFullURL())?>">特殊页面</a></li>
                                        <?php endif;?>
                                        <?php if(in_array('sysop', $wgUser->getGroups()) || in_array('bureaucrat', $wgUser->getGroups())):?>
                                            <li><a href="<?=htmlspecialchars( Title::makeTitle( NS_MEDIAWIKI, 'Sidebar' )->getFullURL())?>">编辑导航</a></li>
                                            <li><a href="<?=htmlspecialchars( Title::makeTitle( NS_MEDIAWIKI, 'Common.css' )->getFullURL())?>">编辑CSS</a></li>
                                            <li><a href="<?=htmlspecialchars( Title::makeTitle( NS_MEDIAWIKI, 'Common.js' )->getFullURL())?>">编辑JS</a></li>
                                            <li><a href="<?=htmlspecialchars( SpecialPage::getTitleFor( 'RecommendArea' )->getFullURL())?>">编辑推荐区</a></li>
				        					<li><a href="<?=htmlspecialchars( Title::makeTitle( NS_MAIN, '首页' )->getFullURL('action=edit'))?>">编辑首页</a></li>
                                        <?php endif;?>
                                    </ul>
                                    <div class="create-wiki">
                                        <form method="get" action="<?php $this->text('wgScript') ?>" class="createbox" name="createbox">
                                            <input type="hidden" value="edit" name="action">
                                            <input type="text" placeholder="请输入页面名称" onfocus="this.placeholder=''" onblur="this.placeholder='请输入页面名称'" class="place" name="title"/>
                                            <input type="button" class="search" value="创建"/>
                                        </form>
                                        <span class="input-warn">*页面名称不能为空</span>
                                    </div>
                                </div>
                            </div>
                    </div>
					<div class="ej-nav-con container">
                            <div class="nav-con">
                                <div class="nav-left">
                                    <div class="logo-focus">
                                        <a href="<?php echo '/'.$wgWikiname.'/'?>" class="wiki-logo"></a>
                                        <div class="focus-box"><?=$followstr?></div>
                                    </div>
                                    <div class="nav-content">
                                        <?php  $this->renderNavigation(array('TOPSIDEBARNAV')); ?>
                                    </div>
                                </div>
                                <div class="nav-right">
                                    <div class="wiki-search1">
                                        <!-- 0 -->
                                        <form class="navbar-form" action="<?php $this->text('wgScript'); ?>" id="nav-searchform">
                                            <input id="nav-searchInput" class="form-control search-query search-text" type="search" accesskey="f" title="<?php $this->text('searchtitle'); ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php echo $search_str; ?>'" placeholder="<?php echo $search_str; ?>" name="search" value="<?php echo htmlspecialchars($this->data['search']); ?>">
                                            <input type="submit" name="fulltext" value="搜索" title="搜索含这些文字的页面" id="mw-searchButton" class="searchButton btn">
                                        </form>
                                        <div class="fn-clear"></div>
                                    </div>
                                    <div class="gz-citiao">
                                        <p class="gzct">
                                            <a class="gz"><em class="joyme_wiki_edit_info" id="joyme_wiki_edit_info1"><?=$site_edit_count?></em>编辑</a><span></span>
                                            <a class="citiao"><em class="joyme_wiki_word_info" id="joyme_wiki_word_info1"><?=$site_page_count?></em>词条</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
                </div>
                <!-- WIKI顶部导航 结束-->
            </section>
            <!-- 导航区 结束-->
            <!-- 内容区 开始-->
            <?php
            if ($this->data['loggedin']) {
                $userStateClass = "user-loggedin";
            } else {
                $userStateClass = "user-loggedout";
            }
            ?>

            <?php
            if ($wgGroupPermissions['*']['edit'] || $this->data['loggedin']) {
                $userStateClass += " editable";
            } else {
                $userStateClass += " not-editable";
            }
            ?>
			<?php $area  = SpecialRecommendArea::getAreaContent( '推荐区2' );?>
			<div class="joymewiki-content-block container">
				<div class="section-bodycontent <?php if($area && $this->data['isarticle'] && $this->data['articleid']!=1){echo 'col-md-9';}?>">
					<section id="content" class="mw-body joymewiki-block <?php echo $userStateClass; ?> " role="main">
						<div id="top"></div>
						<!-- h1 标题 开始-->
						<div id="firstHeading2" class="firstHeading2 page-header nry-h1 fn-clear">
							<div class="h1-top">
								<?php if($this->data['isarticle'] & $wgTitle->mNamespace == 0):?>
								<?php
								if($this->data['articleid']&&$this->data['title']!="首页" && $this->data['title']!="手机版首页"){
									$nav = $this->data['content_navigation'];
									if($nav['actions']['favorite']){
										$favorite = $nav['actions']['favorite'];
										echo '<a class="shouc" id="'.$favorite['id'].'" href="javascript:;"></a>';
									}
									if($nav['actions']['unfavorite']){
										$unfavorite = $nav['actions']['unfavorite'];
										echo '<a class="shouc shouc-done" id="'.$unfavorite['id'].'" href="javascript:;">取消收藏</a>';
									}
								}
								?>
								<?php endif;?>
								<div class="h1-left">
								<h1 id="firstHeading" class="h1" dir="auto"><?php $this->html('title') ?></h1>
								<?php if($this->data['isarticle'] && $wgTitle->mNamespace == 0){
                                    echo '<p class="page-hot"><a href="#let-dianzan">页面热度(<font class="page-hot-num">0</font>)</a></p>';
                                }?>
								<div class="fn-clear"></div>
								</div>
							</div>
							<?php if($this->data['articleid'] && $this->data['isarticle'] && $wgTitle->mNamespace == 0): 
								$pageaddons = JoymePageAddons::getPageAddons($this->data['articleid']);
								$page_contribute_user = User::newFromId($pageaddons->contribute_uid);
								if(!$page_contribute_user->isBlocked()):
									$pageaddons = JoymePageAddons::getPageAddons($this->data['articleid']);
									$page_contribute_user = User::newFromId($pageaddons->contribute_uid);
									$page_contribute_username = $page_contribute_user->getName();
									if(!$page_contribute_user->isBlocked()):
										if($page_contribute_user->isValidUserName($page_contribute_username)):
											$jwu = new JoymeWikiUser();
											$profile = $jwu->getProfileid($page_contribute_user->getId());
											?>
											<input type="hidden" id="pageContributeId" value="<?=$pageaddons->contribute_id?>"/>
                                            <ul class="info0 clearfix">
				                                <li class="fl main-prostrate">主要贡献者：<a id="pageContributeUser" data-uid="<?=$page_contribute_user->getId()?>" target="_blank" href="/home/用户:<?=$page_contribute_username?>" class="userinfo contributer" data-username="<?=$profile?>"><?=$page_contribute_username?></a></li>
				                                <li class="fl info0-btns">
				                                    <span class="info0-btn thank-btn">感谢</span>
				                                    <span class="info0-btn prostrate-btn">膜拜</span>
				                                </li>
				                                <li class="fl line-c">|</li>
				                                <li class="fl prostrate-num">共膜拜0次</li>
				                                <li class="fl list-prostraters">
				                                    <div class="prostraters-direction prostraters-left "></div>
				                                    <div class="prostraters-direction prostraters-right"></div>
				                                    <div class="prostraters-box">
				                                        <ul class="clearfix prostraters-ul">加载中...</ul>
				                                    </div> 
				                                </li>
				                            </ul>
			                            <?php else:?>
											<input type="hidden" id="pageContributeId" value="0"/>
                                            <ul class="info0 clearfix">
				                                <li class="fl main-prostrate">主要贡献者：匿名</li>
				                                <li class="fl info0-btns">
				                                    <span class="info0-btn thank-btn" style="background-color:#9d9d9d;cursor:default;">感谢</span>
				                                    <span class="info0-btn prostrate-btn" style="background-color:#9d9d9d;cursor:default;">膜拜</span>
				                                </li>
				                            </ul>
			                            <?php endif;?>
		                            <?php endif;?>
		                        <?php endif;?>
                            <?php endif;?>
							<?php if($this->data['isarticle']):?>
							<?php $edit = JoymePageAddons::getPageLastEditUser($this->data['articleid']);?>
							<div class="h1-bottom">
								<?php if ($edit != ''):
								$jwu = new JoymeWikiUser();
								$profile = $jwu->getProfileid($edit['user_id']);
								?>
								<p class="info1"><span>最后编辑：</span><span  id="wgArticleUserID" data-uid="<?php echo $edit['user_id']?>"><a target="_blank" href="/home/用户:<?=$edit['last_edit_user']?>" class="userinfo" data-username="<?=$profile?>"><?php echo isset($edit['last_edit_user'])?$edit['last_edit_user']:''?></a></span> <span>更新日期：<?php echo isset($edit['time'])?$edit['time']:''?></span><span>&nbsp;&nbsp;&nbsp;来源：<a target="_blank" href="http://www.joyme.com">着迷网</a></span></p>
								<?php endif;?>
								
								<div class="select-choose visible-md visible-lg">
									<?php  $this->renderNavigation(array('ACTIONS')); ?>
								</div>
                                <?php
                                if ($wgIsLogin) {
                                ?>
                                    <button style="font-size:12px;"  class="btn btn-default ca-edit visible-xs visible-sm">源代码</button>
                                    
                                <?php } ?>
							</div>
							<?php endif;?>
						</div>
						<!-- h1 标题 结束-->
						<!-- bodyContent内容区 开始-->
						<div id="bodyContent" class="mw-body-content">
							<!-- 内容区-->
							<div id="innerbodycontent" class="setting-con">
								<!-- 副标题 开始-->
								<?php
								if ( $this->data['isarticle'] ) {
									?>
									<div id="siteSub"><?php $this->msg( 'tagline' ) ?></div>
									<?php
								}
								?>
								<div id="contentSub"<?php $this->html( 'userlangattributes' ) ?>><?php
									$this->html( 'subtitle' )
									?></div>
								<?php
								if ( $this->data['undelete'] ) {
									?>
									<div id="contentSub2"><?php $this->html( 'undelete' ) ?></div>
									<?php
								}
								?>
								<!-- 副标题 结束-->
								<!-- WIKI内容 -->
								<?php $this->html('bodycontent'); ?>
							</div>
							
						</div>
						<!-- bodyContent内容区 结束-->
					</section>
					<!-- 内容区 结束-->
					<!-- 分类 -->
					<section id="content-after-block" class="joymewiki-block">
					<?php if ($this->data['catlinks']): ?>
						<!-- catlinks -->
							<?php $this->html('catlinks'); ?>
						<!-- /catlinks -->
					<?php endif; ?>
					<!-- 脚注 -->
					<?php if ($this->data['printfooter']): ?>
						<!-- printfooter -->
						<div class="printfooter">
							<?php $this->html('printfooter'); ?>
						</div>
						<!-- /printfooter -->
					<?php endif; ?>
					</section>
					<!-- 点赞短评 -->
					<?php
					$action = $wgRequest->getVal('action');
					$ns = $wgTitle->mNamespace;
					$aid = $this->data['articleid'];
					?>
					<?php if($aid > 1 && $ns==0 && $action==null && $wgWikiname!= 'home'):?>
					<section id="dianzan-block" class="joymewiki-block">
						<div class="fn-clear"></div>
						<div class="dianzan" id="let-dianzan">
                            <div class="dz-detail" id="duanping"></div>
							<p id="ClickLike" class="dz-icon"><span class="dz-num">0</span><b class="dz"></b></p>
							<div class="fn-clear"></div>
						</div>
					</section>
					<?php endif;?>
					<!-- M端推荐区 在短评和评论之间 开始-->
						<?php if($area && $this->data['isarticle'] && $this->data['articleid']!=1):?>
						<div class="section-recommend-m">
							<div class="section-tjq-m"><?php echo $area;?></div>
						</div>
						<?php endif;?>
						<!-- M端推荐区 在短评和评论之间 结束-->
					<div class="visualClear"></div>
					<!-- debughtml -->
					<?php $this->html('debughtml'); ?>
					<!-- /debughtml -->
					<?php if ($this->data['dataAfterContent']): ?>
						<!-- dataAfterContent -->
						<section id="mw-data-after-content-block" class="joymewiki-block">
							<?php $this->html('dataAfterContent'); ?>
						</section>
						<!-- /dataAfterContent -->
					<?php endif; ?>
				</div>
				<!-- 推荐区 开始-->
				<?php if($area && $this->data['isarticle'] && $this->data['articleid']!=1):?>
				<div class="section-recommend col-md-3">
					<div class="section-tjq"><?php echo $area;?></div>
					<div class="section-nav"><div class="section-nav-con"></div></div>
				</div>
				<?php endif;?>
				<!-- 推荐区 结束-->
			</div>
        </div>
        <!-- WIKI区 结束-->

        <?php /*if($wgWikiname != 'home'): ?>
		<div class="cnxh-bg">
			<div class="cnxh">
			    <div class="tabchange">
				    <!--a class="tab wiki-like" href="javascript:;">猜你喜欢的WIKI</a-->
				    <a class="tab wiki-like" href="javascript:;">热门站点</a>
				    <a href="http://wiki.joyme.com" target="_blank" class="qbzd">全部站点</a>
			    </div>
			    <div class="common cnxh-con1 on">
			        <a target="_blank" href="http://wiki.joyme.com/pocketmon/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/3/34/%E5%8F%A3%E8%A2%8B%E5%A6%96%E6%80%AA%E5%A4%8D%E5%88%BB.png?v=201607261123"/><br/><span>口袋妖怪复刻</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/boli/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/5/57/%E5%A4%A9%E5%A4%A9%E6%89%93%E6%B3%A2%E5%88%A9.png?v=201607261123"/><br/><span>天天打波利</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/hzsg/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/8/81/%E5%90%88%E6%88%98%E4%B8%89%E5%9B%BD.png?v=201607261123"/><br/><span>合战三国</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/cq/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/4/44/%E5%85%8B%E9%B2%81%E8%B5%9B%E5%BE%B7%E6%88%98%E8%AE%B0.png?v=201607261123"/><br/><span>克鲁塞德战记</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/pvp/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/1/13/%E7%8E%8B%E8%80%85%E8%8D%A3%E8%80%80.png?v=201607261123"/><br/><span>王者荣耀</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/hszz/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/9/96/%E7%9A%87%E5%AE%A4%E6%88%98%E4%BA%89.png?v=201607261123"/><br/><span>皇室战争</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/stoneage/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/d/d6/%E7%9F%B3%E5%99%A8%E6%97%B6%E4%BB%A3%E8%B5%B7%E6%BA%90.png?v=201607261123"/><br/><span>石器时代起源</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/qjnn/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/9/91/%E5%A5%87%E8%BF%B9%E6%9A%96%E6%9A%96.png?v=201607261136"/><br/><span>奇迹暖暖</span></a>
			        <a target="_blank" href="http://wiki.joyme.com/mxw/"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/d/dc/%E5%86%92%E9%99%A9%E4%B8%8E%E6%8C%96%E7%9F%BF.png?v=201607261123"/><br/><span>冒险与挖矿</span></a>
			        <div class="fn-clear"></div>
			    </div>
			    <!--div class="common cnxh-con2">
			        <a href="#"><img src="http://joymepic.joyme.com/wiki/images/wikiadmin/3/34/%E5%8F%A3%E8%A2%8B%E5%A6%96%E6%80%AA%E5%A4%8D%E5%88%BB.png?v=201607221411"/><br/><span>口袋妖怪:复刻2</span></a>
			        <div class="fn-clear"></div>
			    </div-->
			</div>
		</div>
        <?php endif;*/?>
        
        <?php
//            include 'view/Modal.php'; ?>
                <div id="footer"  class="footer"<?php $this->html('userlangattributes') ?>>
                   <!--  <hr> -->
                   <div class="container footer-pc">
                        <div class="footer-con row visible-md visible-lg">
                            <span>© 2011－2017 joyme.com, all rights reserved</span>
                            <a href="http://www.joyme.com/help/aboutus" target="_blank" rel="nofollow">关于着迷</a> |
                            <a href="http://www.joyme.com/about/products" target="_blank" rel="nofollow">着迷产品</a> |
                            <a href="http://www.joyme.com/help/milestone" target="_blank" rel="nofollow">着迷大事记</a> |
                            <a href="http://www.joyme.com/gopublic/" target="_blank">着迷·新三板</a> |
                            <a href="http://www.joyme.com/about/press" target="_blank" rel="nofollow">媒体报道</a> |
                            <a href="http://www.joyme.com/about/business" target="_blank" rel="nofollow">商务合作</a> |
                            <a href="http://www.joyme.com/about/job/zhaopin" target="_blank" rel="nofollow">加入着迷</a> |
                            <a href="http://www.joyme.com/about/contactus" target="_blank" rel="nofollow">联系我们</a>|
                            <a href="http://www.joyme.com/help/law/parentsprotect" target="_blank" rel="nofollow">家长监护</a>|
                            <a href="http://www.joyme.com/sitemap.htm" target="_blank">网站地图</a>
                            <br>
                            <br>
                            <span>北京乐享方登网络科技股份有限公司</span>
                            <span> 北京市海淀区知春路27号量子芯座大厦12层&nbsp;&nbsp;&nbsp;客服电话：010-51292727</span>
                            <span><a href="http://www.miibeian.gov.cn/" target="_blank">京ICP备11029291号</a></span>
                            <span>京公网安备110108001706号</span>
                            <span><a href="http://joymepic.joyme.com/article/uploads/allimg/201603/1457504308371218.jpg" target="_blank">京网文[2014]0925-225号</a></span>
                        </div>
                   </div>
                   <div class="container footer-web">
                       <div class="row ">
                            <p>2011－2017 joyme.com, all rights reserved</p>
                       </div>
                   </div>
                </div>
                <!-- /footer -->
				<div class="toc-mengceng"></div>
                <?php if($wgWikiname != 'home'):?>
				<div class="share-right">
                    <a target="_blank" href="http://app.joyme.com/" class="erwei-ma"></a>
					<div class="bdsharebuttonbox" data-tag="share_1">
						<a class="bds_qzone" data-cmd="qzone" href="#"></a>
						<a class="bds_sqq" data-cmd="sqq"></a>
						<a class="bds_tsina" data-cmd="tsina"></a>
						<a class="bds_weixin" data-cmd="weixin"></a>
						<a class="bds_tieba" data-cmd="tieba"></a>
						<a class="top-icon" href="#"></a>
					</div>
					<script>
					window._bd_share_config = {
						    "common": {
						        "bdSnsKey": {},
						        "bdText": "",
						        "bdMini": "2",
						        "bdMiniList": false,
						        "bdPic": "",
						        "bdStyle": "0",
						        "bdSize": "24"
						    },
						    "share": {}
						};
					
						//以下为js加载部分
						with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];
					</script>
				</div>
				<div class="wechat-share">
				    <a class="fx" href="javascript:;"></a>
				    <a href="javascript:;" class="mulu"></a>
				    <a class="go-top" href="#"></a>
				    <div class="mengceng-share">
					    <div class="share-with-wrap">
					        <div class="share-title-m">
					            <h3>分享到...</h3>
					            <span class="close-btn"></span>
					            <div class="clearfix"></div>
					        </div>
					        <div class="share-list-wh">
					            <ul>
					            	<?php $shareurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);?>
					                <li class="kongjian"><a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=<?php echo $shareurl;?>&title=<?php urlencode($this->html('title'));?>&desc=&summary=&site="></a><span>QQ空间</span></li>
					                <li class="qq-friend"><a href="http://connect.qq.com/widget/shareqq/index.html?url=<?php echo $shareurl;?>&title=<?php urlencode($this->html('title'));?>&desc=&summary=&site=joyme"></a><span>QQ好友</span></li>
					                <li class="xinlang-wb"><a href="http://service.weibo.com/share/share.php?url=<?php echo $shareurl;?>&title=<?php urlencode($this->html('title'));?>&appkey=1343713053&searchPic=true"></a><span>新浪微博</span></li>
					                <li class="baidu-tieba"><a href="http://tieba.baidu.com/f/commit/share/openShareApi?url=<?php echo $shareurl;?>&title=<?php urlencode($this->html('title'));?>&desc=&comment="></a><span>百度贴吧</span></li>
									<div class="clearfix"></div>
					            </ul>
					        </div>
					    </div>
				    </div>
				</div>
				<?php endif;?>
        <?php $this->printTrail(); ?>

        </body>
        </html><?php
        wfRestoreWarnings();
    }
    
    
    /**
     * Render logo
     */
    private function renderLogo() {
        $mainPageLink = $this->data['nav_urls']['mainpage']['href'];
        $toolTip = Xml::expandAttributes(Linker::tooltipAndAccesskeyAttribs('p-logo'));
        ?>
        <div id="p-logo" class="text-center">
            <a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href']) ?>" <?php echo Xml::expandAttributes(Linker::tooltipAndAccesskeyAttribs('p-logo')) ?>><img class="logo_image" src="<?php $this->text('logopath'); ?>" alt="<?php $this->html('sitename'); ?>" /></a>
        </div>
        <?php
    }
    
    private function getSidebarOn($title){
		global $wgServer,$wgWikiname;
		$title = str_replace($wgServer.'/'.$wgWikiname.'/', '', $title);
		if($this->sidebarIsOn == false){
			$this->sidebarIsOn = $this->data['title'] == $title?true:false;
			return $this->sidebarIsOn == true?'visited':'';
		}else{
			return '';
		}
    }
    
    
    /**
     * Render one or more navigations elements by name, automatically reveresed
     * when UI is in RTL mode
     *
     * @param $elements array
     */
    private function renderNavigation($elements) {
        global $wgVectorUseSimpleSearch;        
        global $wgMediaWikiBootstrapSkinDisplaySidebarNavigation;
        global $wgMediaWikiBootstrapSkinSidebarItemsInNavbar;
        global $search_str;
        // If only one element was given, wrap it in an array, allowing more
        // flexible arguments
        if (!is_array($elements)) {
            $elements = array($elements);
            // If there's a series of elements, reverse them when in RTL mode
        } elseif ($this->data['rtl']) {
            $elements = array_reverse($elements);
        }
        // Render elements
        foreach ($elements as $name => $element) {
            echo "\n<!-- {$name} -->\n";
            switch ($element) :
                                        
                case 'EDIT':
                    if (!array_key_exists('edit', $this->data['content_actions'])) {
                        break;
                    }
                    $navTemp = $this->data['content_actions']['edit'];

                    if ($navTemp) {
                        ?>
                        <ul class="nav navbar-nav">
                            <li>
                                <a id="b-edit" href="<?php echo $navTemp['href']; ?>" class="btn btn-default">
                                    <i class="fa fa-edit"></i> <strong><?php echo $navTemp['text']; ?></strong>
                                </a>
                            </li>
                        </ul>
                        <?php
                    }
                    break;
                                        
                case 'ACTIONS':

                    $theMsg = 'actions';
                    $theData = array_reverse($this->data['view_urls']);
                    if(!empty($theData['ve-edit'])){
                    	$edit_url = $theData['ve-edit']['href'];
						$edit_str = htmlspecialchars($theData['ve-edit']['text']);
						$theData['edit']['text'] = '源代码';
						unset($theData['ve-edit']);
                    }else{
                    	foreach($theData as $key=>$link){
							if($key == 'edit'){
								$edit_url = $link['href'];
								$edit_str = htmlspecialchars($link['text']);
								unset($theData['edit']);
								continue;
							}else if($key=='viewsource'){
								$edit_url = $link['href'];
								$edit_str = htmlspecialchars($link['text']);
								unset($theData['viewsource']);
								continue;
							}
						}
						if(empty($edit_url)){
							$edit_url = 'index.php?title='.$this->data['title'].'&action=edit';
							$edit_str = htmlspecialchars('源代码');
							$theData['view']['href'] = $this->data['title'];
							$theData['view']['text'] = htmlspecialchars('刷新');
							$theData['view']['attributes'] = ' id="ca-view"';
						}
                    }
                    
                    if(!empty($theData['edit'])){
						$theData['edit']['text'] = htmlspecialchars('源代码');
                    	$theDataEditTemp = $theData['edit'];
                    	unset($theData['edit']);
                    	array_unshift($theData,$theDataEditTemp);
                    }
                    
                    unset($theData['unwatch']);

                    if (count($theData) > 0) : ?>
                        <ul class="nav navbar-nav caozuo-select" role="navigation">
                            <li class="dropdown" id="p-<?php echo $theMsg; ?>" class="vectorMenu<?php if (count($theData) == 0) echo ' emptyPortlet'; ?>">
                                <a class="dropdown-toggle" role="button" href="<?php echo $edit_url;?>"><?php echo $edit_str;?><b class="caret"></b></a>
                                <ul aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" class="dropdown-menu" <?php $this->html('userlangattributes') ?>>
                                    <?php foreach ($theData as $link):?>
									<?php if(strpos($link['attributes'],'favorite')!==false) continue;?>
                                        <li<?php echo $link['attributes'] ?>>
                                            <a href="<?php echo htmlspecialchars($link['href']) ?>" <?php echo $link['key'] ?> tabindex="-1"><?php echo htmlspecialchars($link['text']) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul><?php
                    endif;

                    break;    

                case 'TOPSIDEBARNAV':?>
                	<ul class="venus-menu">
                   <?php foreach ($this->data['sidebar'] as $key => $val) :     
                        ?>
						<li class="wiki-nav-<?=$val['info']["text"]; ?>">
							<a href="<?=$val['info']["href"]; ?>"><?=$val['info']["text"]; ?></a>
							<?php 
                        	if (!empty($val['list'])){
								echo '<ul>';
								foreach ($val['list'] as $b) {
									if (!empty($b['list'])){
										echo '<li><a href="javascript:;">' . $b['info']["text"] . '</a>';
										echo '<ul>';/*3级导航*/
										foreach ($b['list'] as $c) {
											echo '<li><a href="' . $c['info']["href"] . '">' . $c['info']["text"] . '</a></li>';
										};
										echo '</ul>';
									}else{
										echo '<li><a href="' . $b['info']["href"] . '">' . $b['info']["text"] . '</a>';
									}
									
									echo "</li>";
										 
									};
								echo '</ul>';
							}
							?>
						</li>
                       <?php
                    endforeach;?>
                    </ul>
                    <?php break;
                    case 'SIDEBARNAV':?>
                        <div class="wiki-nav">
                        <?php foreach ($this->data['sidebar'] as $key => $val) :          
                                            
						?>
						<div class="wiki-action wiki-nav-<?=$val['info']["text"]; ?>">
							<a class="fl <?=$this->getSidebarOn($val['info']["href"])?>" href="<?=$val['info']["href"]; ?>"><em><?=$val['info']["text"]; ?></em></a>
							<?php 
							if (!empty($val['list'])){
								/*2级导航*/
								echo '<ul class="wiki-ul-ej">';
								foreach ($val['list'] as $b) {
									
									if (!empty($b['list'])){
                                        echo '<li class="ej fn-clear"><a class="'.$this->getSidebarOn($b['info']["href"]).'" href="javascript:;">' . $b['info']["text"] . '</a>';

										echo '<b class="menu2">返回</b><ul class="sj"><cite></cite>';/*3级导航*/
										foreach ($b['list'] as $c) {
											echo '<li><a class="'.$this->getSidebarOn($c['info']["href"]).'" href="' . $c['info']["href"] . '">' . $c['info']["text"] . '</a></li>';
										};
										echo '</ul>';
									}else{
                                        echo '<li class="ej fn-clear"><a class="'.$this->getSidebarOn($b['info']["href"]).'" href="' . $b['info']["href"] . '">' . $b['info']["text"] . '</a>';
                                    }
									echo "</li>";
							
								};
								echo '</ul>';
							}
							?>
						</div>
					   <?php
					endforeach;?>
					</div>
					<?php break;        	
                    
                case 'TOOLBOX':

                    $theMsg = 'toolbox';
                    $theData = array_reverse($this->getToolbox());
                    ?>

                    <ul class="nav navbar-nav" role="navigation">

                        <li id="p-<?php echo $theMsg; ?>" class="dropdown<?php if (count($theData) == 0) echo ' emptyPortlet'; ?>">

                            <a data-toggle="dropdown" class="dropdown-toggle" role="button">
                                <?php $this->msg($theMsg) ?> <b class="caret"></b>
                            </a>

                            <ul class="dropdown-menu" aria-labelledby="<?php echo $this->msg($theMsg); ?>" role="menu" <?php $this->html('userlangattributes') ?>>

                                <?php
                                foreach ($theData as $key => $item) :

                                    if (preg_match('/specialpages|whatlinkshere/', $key)) {
                                        echo '<li class="divider"></li>';
                                    }

                                    echo $this->makeListItem($key, $item);

                                endforeach;
                                ?>
                            </ul>

                        </li>

                    </ul>
                    <?php
                    break;
                        
                case 'TOP-NAV-SEARCH':
                    ?>
                    <form class="navbar-form" action="<?php $this->text('wgScript') ?>" id="nav-searchform">
                        <input id="nav-searchInput" class="form-control search-query search-text" type="search" accesskey="f" title="<?php $this->text('searchtitle'); ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php echo $search_str; ?>'" placeholder="<?php echo $search_str; ?>" name="search" value="<?php echo htmlspecialchars($this->data['search']); ?>">
                        <?php echo $this->makeSearchButton('fulltext', array('id' => 'mw-searchButton', 'class' => 'searchButton btn')); ?>
                    </form>
                    <?php
                    break;
                                         
                case 'SEARCHNAV':
                    ?>
                    <li>
                        <form class="navbar-form navbar-right" action="<?php $this->text('wgScript') ?>" id="searchform">
                            <input id="searchInput" class="form-control" type="search" accesskey="f" title="<?php $this->text('searchtitle'); ?>" onfocus="this.placeholder=''" onblur="this.placeholder='<?php echo $search_str; ?>'" placeholder="<?php echo $search_str; ?>" name="search" value="<?php echo htmlspecialchars($this->data['search']); ?>">
                            <?php echo $this->makeSearchButton('fulltext', array('id' => 'mw-searchButton', 'class' => 'searchButton btn hidden')); ?>
                        </form>
                    </li>

                    <?php
                    break;
                                
                case 'LANGUAGES':
                    $theMsg = 'otherlanguages';
                    $theData = $this->data['language_urls'];
                    $options = "";
                    ?>
                    <?php foreach ($theData as $key => $val) : ?>
                        <li class="<?php echo $navClasses ?>">
                            <?php echo $this->makeLink($key, $val, $options); ?>
                        </li>
                    <?php endforeach; ?>
                    <?php
                    break;
                
            endswitch;
        }
    }

}
