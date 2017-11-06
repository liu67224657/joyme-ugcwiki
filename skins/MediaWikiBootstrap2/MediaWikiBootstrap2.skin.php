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
class SkinMediaWikiBootstrap2 extends SkinTemplate {

    public $skinname        = 'mediawikibootstrap2';
    public $stylename       = 'mediawikibootstrap2';
    public $template        = 'MediaWikiBootstrapTemplate2';
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
        $out->addModuleScripts('skins.mediawikibootstrap2');
    }

    /**
     * Loads skin and user CSS files.
     * @param OutputPage $out
     */
   function setupSkinUserCss( OutputPage $out ) {
        parent::setupSkinUserCss( $out );
        
        $styles = array( 'mediawiki.skinning.interface', 'skins.mediawikibootstrap2','ext.socialprofile.userinfo.css' );
        wfRunHooks( 'SkinMediawikibootstrapStyleModules', array( $this, &$styles ) );
        $out->addModuleStyles( $styles );
    }

}

/**
 * Template class of the MediaWikiBootstrap Skin
 * @ingroup Skins
 */
class MediaWikiBootstrapTemplate2 extends BaseTemplate {

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
        global $wgSquidMaxage;

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

        $wgRequest->response()->header('Cache-Control: s-maxage=' . $wgSquidMaxage
            . ', must-revalidate, max-age=0' );

        ?>
        <!-- 将这段话添加到heade结束标签之前  开始-->
        <script type="text/javascript">
            document.domain=window.location.hostname;
            window.addEventListener("DOMContentLoaded", function (){
                document.addEventListener("touchstart", function (){return false}, true)
            }, true);
        </script>
        <script src="http://static.joyme.com/js/jquery-1.9.1.min.js"></script>
        <?php 
        
        echo '<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/mobile/cms/apparticledetail/js/lib/layer/layer.js"></script>';
        echo '<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/mobile/cms/apparticledetail/js/commentcommon.js?2017061609"></script>';
        ?>
        <!-- 将这段话添加到heade结束标签之前  结束-->
        <?php if ($wgGroupPermissions['*']['edit'] || $wgMediaWikiBootstrapSkinAnonNavbar || $this->data['loggedin']) : ?>
        <?php endif; ?>
        
        <!-- WIKI区 开始-->
        <div id="wrapper">
            <!-- 导航区 开始-->
    
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
							
							<?php if($this->data['isarticle']):?>
							<?php $edit = JoymePageAddons::getPageLastEditUser($this->data['articleid']);?>
							<div class="h1-bottom">
								<?php if ($edit != ''):
								$jwu = new JoymeWikiUser();
								$profile = $jwu->getProfileid($edit['user_id']);
								?>
								<p class="info1"><span>最后编辑：</span><span  id="wgArticleUserID" data-uid="<?php echo $edit['user_id']?>"><?php echo isset($edit['last_edit_user'])?$edit['last_edit_user']:''?></span> <span>更新日期：<?php echo isset($edit['time'])?$edit['time']:''?></span><span>&nbsp;&nbsp;&nbsp;来源：着迷网</span></p>
								<?php endif;?>
								
								<div class="select-choose visible-md visible-lg">
									<?php  $this->renderNavigation(array('ACTIONS')); ?>
								</div>
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
					<!--<section id="content-after-block" class="joymewiki-block">
					<?php //if ($this->data['catlinks']): ?>
						<!-- catlinks -->
							<?php //$this->html('catlinks'); ?>
						<!-- /catlinks -->
					<?php //endif; ?>
					<!-- 脚注 -->
					<?php //if ($this->data['printfooter']): ?>
						<!-- printfooter -->
						<!--<div class="printfooter">-->
							<?php //$this->html('printfooter'); ?>
						<!--</div>-->
						<!-- /printfooter -->
					<?php //endif; ?>
					<!--</section>-->
                    <!-- 新评论 -->
                    <?php if($this->data['thispage']!='首页'):?>
                    <div class="comment-wrapper"> 
                         <div class="comments"> 
                              <div class="triangle"></div> 
                              <p class="comment-p">评论</p> 
                         </div> 
                         <div class="comment-box"> 
                         </div> 
                    </div>
                    <?php endif; ?>
				</div>
				
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
                            <a href="http://www.joyme.com/about/contactus" target="_blank" rel= "nofollow">联系我们</a>|
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
				
				<div class="wechat-share">
				    <a class="fx" href="javascript:;"></a>
				    <a href="javascript:;" class="mulu"></a>
				    <a class="go-top" href="javascript:void(0)"></a>
				    
				</div>
				<?php endif;?>
        <?php $this->printTrail(); ?>
        <?php $this->renderShare($this->data['thispage'],$this->data['articleid']); ?>
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
							<a href="<?=JoymeSite::changeSkinUrl($val['info']["href"]); ?>"><?=$val['info']["text"]; ?></a>
							<?php
                        	if (!empty($val['list'])){
								echo '<ul>';
								foreach ($val['list'] as $b) {
									if (!empty($b['list'])){
										echo '<li><a href="javascript:;">' . $b['info']["text"] . '</a>';
										echo '<ul>';/*3级导航*/
										foreach ($b['list'] as $c) {
											echo '<li><a href="' . JoymeSite::changeSkinUrl($c['info']["href"]) . '">' . $c['info']["text"] . '</a></li>';
										};
										echo '</ul>';
									}else{
										echo '<li><a href="' . JoymeSite::changeSkinUrl($b['info']["href"]) . '">' . $b['info']["text"] . '</a>';
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

    private function renderShare($title,$articleid)
    {
        global $wgEnv,$wgWikiname,$wgSiteGameTitle;
         $clientpic = JoymeSite::getWikiFirstPic($title);
        $share_url = 'http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.urlencode($title).'?useskin=JShare';
        $url = 'http://wiki.joyme.'.$wgEnv.'/'.$wgWikiname.'/'.urlencode($title).'?useskin=MediaWikiBootstrap2';

         $share_url = JoymeSite::getShareShortUrl($share_url,$title);
         $publishtime = JoymeSite::getPageCreateTime($articleid);
        $gamename = JoymeSite::getGameNameByKey();

        if($title!='首页'){
            $doc_title = "WIKI文章";
            $comment_status = 'yes';
        }else{
            $doc_title = $gamename;
            $comment_status = 'no';
        }

        
        $discussion = $wgSiteGameTitle;
        $wikilist_url = 'http://hezuo.joyme.'.$wgEnv.'/wiki/index.php?c=wiki&a=aclist&wikikey='.$wgWikiname.'&queryflag=-1';
        $html = '
        <div id="_share_status" style="display: none;">yes</div>
        <div id="domian" style="display: none;"></div>
        <div id="_title" style="display: none;">'.$title.'</div>
        <div id="_doc_title" style="display: none;">'.$doc_title.'</div>
        <div id="_desc" style="display: none;">'.$title.'</div>
        <div id="_clientpic" style="display: none;">'.$clientpic.'</div>
        <div id="_share_url" style="display: none;">'.$share_url.'</div>
        <div id="_share_task" style="display: none;"></div>
        <div id="_url" style="display: none;">'.$url.'</div>
        <div id="_ptime" style="display: none;">'.$publishtime.'</div>
        <div id="_ctype" style="display: none;">2</div>
        <div id="_unikey" style="display: none;"></div>
        <div id="_shoucang_status" style="display: none;"></div>
        <div id="_comment_status" style="display: none;">'.$comment_status.'</div>
        <div id="_wikikey" style="display: none;">'.$wgWikiname.'</div>
        <div id="_contentid" style="display: none;">'.$wgWikiname.'</div>
        <div id="_favorite_status" style="display: none;">yes</div>
        <div id="_directid" style="display: none;"></div>
        <div id="_discussion" style="display: none;">'.$discussion.'</div>
        <div id="_wikilist_status" style="display: none;">yes</div>
        <div id="_wikilist_url" style="display: none;">'.$wikilist_url.'</div>
        ';
        echo $html;
    }

}
