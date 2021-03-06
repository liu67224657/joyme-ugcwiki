<?php
# This file was automatically generated by the MediaWiki 1.26.3
# installer. If you make manual changes, please keep track in case you
# need to recreate them later.
#
# See includes/DefaultSettings.php for all configurable settings
# and their default values, but don't forget to make changes in _this_
# file, not there.
#
# Further documentation for configuration settings may be found at:
# https://www.mediawiki.org/wiki/Manual:Configuration_settings

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

## Uncomment this to disable output compression
# $wgDisableOutputCompression = true;

## The URL base path to the directory containing the wiki;
## defaults for all runtime URL paths are based off of this.
## For more information on customizing the URLs
## (like /w/index.php/Page_title to /wiki/Page_title) please see:
## https://www.mediawiki.org/wiki/Manual:Short_URL

## The protocol and server name to use in fully-qualified URLs
## alpha beta com
$wgEnv = substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.') + 1);

$urlarr = explode('/' , $_SERVER['REQUEST_URI']);

$wgWikiname = empty($urlarr[1])?'':$urlarr[1];

if(empty($wgWikiname)){
	die('no wikiname');
}
$wgWikiname = strtolower($wgWikiname);

$wgScriptPath = "/{$wgWikiname}";
$wgScript = "$wgScriptPath/index$wgScriptExtension";
$wgLoadScript = "http://wikicdn.joyme.{$wgEnv}/{$wgWikiname}/load{$wgScriptExtension}";

$wgIsUgcWiki = true;
$wgUserEditStatus = true;
$wgMobileIndexStatus = false;
//site info
$wgSiteGameTitle = '';
$wgSitename = '';
$wgMetaNamespace = '';
$wgSiteSEOKeywords = '';
$wgSiteSEODescription = '';
$wgPhpServer = "http://wiki.joyme." . $wgEnv;



## The URL path to static resources (images, scripts, etc.)
//$wgResourceBasePath = "";
$wgResourceBasePath = "http://wikicdn.joyme.{$wgEnv}";
$wgCrossSiteAJAXdomains = array("*.joyme.{$wgEnv}");

## The URL path to the logo.  Make sure you change this from the default,
## or else you'll overwrite your logo when you upgrade!
$wgLogo = "$wgResourceBasePath/resources/assets/wiki.png";

## UPO means: this is also a user preference option

$wgEnableEmail = false;
$wgEnableUserEmail = true; # UPO

$wgEmergencyContact = "to_group@staff.joyme.com";
$wgPasswordSender = "to_group@staff.joyme.com";

$wgEnotifUserTalk = false; # UPO
$wgEnotifWatchlist = false; # UPO
$wgEmailAuthentication = true;

## Database settings

# MySQL specific settings
$wgDBprefix = "";

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=utf8";

# Experimental charset support for MySQL 5.0.
$wgDBmysql5 = false;

## Shared memory settings
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();

## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads = true;
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUploadPath = "/images/$wgWikiname";
$wgUploadDirectory = "$IP/wiki/images/$wgWikiname";
$wgAllowExternalImages = true;
$wgAllowCopyUploads = true;
$wgFileExtensions = array('png', 'gif', 'jpg', 'jpeg');

# InstantCommons allows wiki to use images from https://commons.wikimedia.org
$wgUseInstantCommons = false;

## If you use ImageMagick (or any other shell command) on a
## Linux server, this will need to be set to the name of an
## available UTF-8 locale
$wgShellLocale = "en_US.utf8";

## If you want to use image uploads under safe mode,
## create the directories images/archive, images/thumb and
## images/temp, and make them all writable. Then uncomment
## this, if it's not already uncommented:
#$wgHashedUploadDirectory = false;

## Set $wgCacheDirectory to a writable directory on the web server
## to make your wiki go slightly faster. The directory should not
## be publically accessible from the web.
#$wgCacheDirectory = "$IP/cache";

# Site language code, should be one of the list in ./languages/Names.php
$wgLanguageCode = "zh-cn";

if($wgWikiname == 'azurlanewiki'){
    $wgLanguageCode = 'ja';
}

$wgSecretKey = "c3222989ab901f323293110f88581069b5c9a392497cb497ec079a99ba523530";

# Site upgrade key. Must be set to a string (default provided) to turn on the
# web installer while LocalSettings.php is in place
$wgUpgradeKey = "421857a1947cb99b";

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";

# Path to the GNU diff3 utility. Used for conflict resolution.
$wgDiff3 = "/usr/bin/diff3";

# The following permissions were set based on your choice in the installer
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit'] = false;

$wgGroupPermissions['lguser'] = $wgGroupPermissions['user'];
$wgGroupPermissions['user'] = $wgGroupPermissions['*'];

$wgGroupPermissions['sysop']['edit'] = true;

$wgGroupPermissions['bureaucrat'] += $wgGroupPermissions['sysop'];

## Default skin: you can change the default skin. Use the internal symbolic
## names, ie 'vector', 'monobook':
$wgDefaultSkin = "mediawikibootstrap";

# Enabled skins.
# The following skins were automatically enabled:

$wgSkinsList = array(
	'mediawikibootstrap'=>'左侧导航皮肤1.0版本',
	'mediawikibootstrap1'=>'顶部导航皮肤1.1版本',
	'mediawikibootstrap2'=>'社交APP皮肤1.0版本',
	'jshare'=>'社交APP分享皮肤1.0版本',
);

wfLoadSkin( 'Vector' );
require_once( "$IP/skins/MediaWikiBootstrap/MediaWikiBootstrap.php" );
require_once( "$IP/skins/MediaWikiBootstrap1/MediaWikiBootstrap1.php" );
require_once( "$IP/skins/MediaWikiBootstrap2/MediaWikiBootstrap2.php" );
require_once( "$IP/skins/JShare/JShare.php" );

# Enabled Extensions. Most extensions are enabled by including the base extension file here
# but check specific extension documentation for more details
# The following extensions were automatically enabled:
wfLoadExtension( 'Cite' );
wfLoadExtension( 'CiteThisPage' );
wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'Gadgets' );
wfLoadExtension( 'ImageMap' );
wfLoadExtension( 'InputBox' );
wfLoadExtension( 'Interwiki' );
wfLoadExtension( 'LocalisationUpdate' );
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'ParserFunctions' );
wfLoadExtension( 'PdfHandler' );
wfLoadExtension( 'Poem' );
wfLoadExtension( 'SpamBlacklist' );
//wfLoadExtension( 'SyntaxHighlight_GeSHi' );
wfLoadExtension( 'TitleBlacklist' );

wfLoadExtension( 'WikiEditor' );
$wgDefaultUserOptions['usebetatoolbar'] = 1;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;

# Displays the Preview and Changes tabs
$wgDefaultUserOptions['wikieditor-preview'] = 0;

# Displays the Publish and Cancel buttons on the top right side
$wgDefaultUserOptions['wikieditor-publish'] = 0;

# End of automatically generated settings.
# Add more configuration options below.
#自定义插件
require_once( "extensions/JoymePlugin/JoymeButton.php");
require_once( "extensions/JoymePlugin/JoymeHiddenId.php");
require_once( "extensions/JoymePlugin/JoymeLevelDiv.php");
require_once( "extensions/JoymePlugin/JoymeMovie.php");
require_once( "extensions/JoymePlugin/JoymeScript.php");
require_once( "extensions/JoymePlugin/JoymeSelect.php");
require_once( "extensions/JoymePlugin/JoymeSharePf.php");
require_once( "extensions/JoymePlugin/JoymeShowDiv.php");
require_once( "extensions/JoymePlugin/JoymeShowSpan.php");
require_once( "extensions/JoymePlugin/JoymeTabTag.php");
require_once( "extensions/JoymePlugin/JoymeTagSelect.php");
require_once( "extensions/JoymePlugin/JoymeViewAll.php");
require_once( "extensions/JoymePlugin/JoymeLunBo.php"); //轮播插件
require_once( "extensions/JoymePlugin/JoymeShowWeekId.php");
require_once( "extensions/JoymePlugin/JoymeCanvas.php"); // 蜘蛛网图插件
require_once( "extensions/JoymePlugin/JoymeZhenrong.php"); // 阵容插件
require_once( "extensions/JoymePlugin/JoymeIframe.php"); //iframe
require_once( "extensions/JoymePlugin/JoymeAudio.php"); //audio
require_once( "extensions/JoymePlugin/JoymeActiveUsers.php"); //活跃用户
//require_once( "extensions/JoymePlugin/JoymeRecentChanges.php"); //最近新增/编辑
require_once( "extensions/JoymePlugin/JoymeRank.php"); //排行榜
require_once( "extensions/JoymePlugin/ChangeSessionSkin.php");

//对比插件
require_once( "extensions/JoymePlugin/JoymeCardCompare.php"); // 卡牌对比插件
require_once( "extensions/JoymePlugin/JoymeCardComepareSel.php"); //卡牌对比选择插件
require_once( "extensions/JoymePlugin/JoymeIframe.php"); //iframe
require_once( "extensions/JoymePlugin/JoymeFormStart.php"); //form 开头
require_once( "extensions/JoymePlugin/JoymeFormEnd.php"); //form 结束

require_once( "extensions/Mobile/Mobile.php");//
require_once( "extensions/MobileDetect/MobileDetect.php"); //
require_once "extensions/MultimediaViewer/MultimediaViewer.php";//图片展示插件

require_once( "extensions/AJAXPoll/AJAXPoll.php"); //投票
require_once( "extensions/MsUpload/MsUpload.php" );
require_once( "extensions/ConfirmEdit/ConfirmEdit.php" );
require_once( "extensions/Tabber/Tabber.php" );
require_once( "extensions/SeoSettings/SeoSettings.php" );
require_once( "extensions/Discussion/Discussion.php" );
require_once( "extensions/RecommendUsers/RecommendUsers.php" );
require_once( "extensions/RecommendWiki/RecommendWiki.php" );
require_once( "extensions/AboutMe/AboutMe.php");
# 着迷公共函数库
require_once "$IP/extensions/JFunctions/JFunctions.php";
# 文章点赞 & 短评
require_once "$IP/extensions/JLike/JLike.php";
# 感谢大神
require_once "$IP/extensions/PageContribute/PageContribute.php";
# 着迷wiki贡献
require_once "$IP/extensions/JContribution/JContribution.php";
# 着迷wiki收藏
require_once "$IP/extensions/Favorites/Favorites.php";
# 着迷wiki评论
require_once "$IP/extensions/JComments/JComments.php";
# 着迷API
require_once "$IP/extensions/JApi/JApi.php";

//数组
include_once "$IP/extensions/Arrays/Arrays.php";

require_once "$IP/extensions/Variables/Variables.php";

//五星投票
require_once "$IP/extensions/VoteNY/VoteNY.php";

//SMW
require_once "$IP/extensions/SemanticMediaWiki/SemanticMediaWiki.php";
enableSemantics( 'joyme.'.$wgEnv );
include_once "$IP/extensions/SemanticForms/SemanticForms.php";
include_once "$IP/extensions/SemanticResultFormats/SemanticResultFormats.php";


//lua
require_once "$IP/extensions/Scribunto/Scribunto.php";
$wgScribuntoDefaultEngine = 'luastandalone';

//用户中心插件
require_once("$IP/extensions/SocialProfile/SocialProfile.php");
$wgUserProfileDisplay['friends'] = true;
$wgUserProfileDisplay['foes'] = true;
$wgUserBoard = true;
$wgUserProfileDisplay['board'] = true;
$wgUserProfileDisplay['stats'] = true;
$wgUserProfileDisplay['activity'] = true;

$wgUserCenterUrl = 'http://uc.joyme.'.$wgEnv.'/usercenter/page?pid=';

$wgNamespacesWithSubpages = array_fill(
	0, 200, true
);

//通知插件
require_once "$IP/extensions/Echo/Echo.php";
//创建wiki
require_once "$IP/extensions/CreateWiki/CreateWiki.php";
//wiki动态
require_once "$IP/extensions/WikiDynamic/WikiDynamic.php";
//JOYME 钩子
require_once "$IP/extensions/JHooks/JHooks.php";
//推荐区
require_once "$IP/extensions/RecommendArea/RecommendArea.php";
//一键系统消息
require_once "$IP/extensions/QuickMessage/QuickMessage.php";
//广告区插件
require_once "$IP/extensions/JoymePlugin/JoymeAdvertising.php";
// 模板规范(文章列表模板插件)
require_once( "extensions/ArticleList/ArticleList.php");

//初心者
//require_once( "extensions/JNovices/JNovices.php");
//认证大神
//require_once( "extensions/JProficients/JProficients.php");

//最近新增/编辑
require_once( "extensions/JoymeRecentChanges/JoymeRecentChanges.php");

$wgNoFollowLinks = false;


/**
 * @see $wgSharedDB
 */
$wgSharedDB = 'homewiki';

$wgSharedTables = array(
	'user',
	'user_stats',
	'user_properties' ,
	'user_board',
	'user_board_list',
	'user_user_follow',
	'user_fields_privacy',
	'user_gift',
	'user_points_archive',
	'user_points_monthly',
	'user_points_weekly',
	'user_profile',
	'user_system_gift',
	'user_system_messages',

	'echo_email_batch',
	'echo_event',
	'echo_notification',
	'echo_target_page',

	'user_addition',
	'user_editcount_log',
	'user_site_addition',
	'user_site_relation',
	'user_action_log',

	'site_editcount_log',

	'joyme_sites',

	'recommend_users',
	'recommend_wiki',

	'favoritelist'
);

//评论
$wgComment = true;

// 讨论区
$wgThread = false;

//用户中心
$joyme_u_status = 'on';
$joyme_u_adminid = array(3403834);


$wgEnvFile = dirname(__FILE__).'/conf/'.$wgEnv.".php";
if (!file_exists($wgEnvFile)) {
	die('no this environment');
}
require_once($wgEnvFile);
//joyme-class 引入
global $wgAutoloadLocalClasses;
$wgAutoloadLocalClasses += array(
	'JoymeWikiUser'=> __DIR__ .'/extensions/JoymeClass/JoymeWikiUser.php',
	'JoymeSite'=> __DIR__ .'/extensions/JoymeClass/JoymeSite.php',
	'Joyme'=> __DIR__ .'/extensions/JoymeClass/Joyme.php',
	'GetPingYing'=> __DIR__ .'/extensions/JoymeClass/GetPingYing.php',
	'DataSynchronization'=> __DIR__ .'/extensions/JoymeClass/DataSynchronization.php',
	'InterestTesting'=> __DIR__ .'/extensions/JoymeClass/InterestTesting.php',
	'SynchronousData' => __DIR__ .'/extensions/JoymeClass/SynchronousData.php',
	'JoymeReminderMessage' => __DIR__ . '/extensions/AboutMe/SendSystemMessageClass.php',
	'JoymePageAddons' => __DIR__ . '/extensions/JoymeClass/PageAddons.php',
);

//siteinfo 站点设置
require_once "$IP/extensions/SiteInfo/SiteInfo.php";
SiteInfo::load();

//set seo
$wgUseWikiSeo = false;
wfLoadExtension( 'WikiSEO' );

//站点id
$joymewikiuser = new JoymeWikiUser();
$joymesiteinfo = $joymewikiuser->getSiteInfo($wgWikiname);
if($joymesiteinfo){
	$wgSiteId = $joymesiteinfo[1]['site_id'];
	$wgSiteRealName = $joymesiteinfo[1]['site_name'];
}

$wgArticlePath = "/$wgWikiname/$1";
$wgServer = "http://wiki.joyme." . $wgEnv ;

if(strstr($_SERVER['HTTP_HOST'],'m.wiki.joyme.')){
	$wgServer = "http://m.wiki.joyme." . $wgEnv ;
}

/*if (isMobile()){
	$wgServer = "http://m.wiki.joyme." . $wgEnv . "";
}else{
	$wgServer = "http://wiki.joyme." . $wgEnv . "";
}*/

if (isMobile() && $wgMobileIndexStatus) {
	if ($_SERVER['REQUEST_URI'] == '/'.$wgWikiname.'/' || urldecode($_SERVER['REQUEST_URI']) == '/'.$wgWikiname.'/首页' || $_SERVER['REQUEST_URI'] == '/%e9%a6%96%e9%a1%b5') {
		header('location:/' . $wgWikiname . '/手机版首页');
	}
}

if($wgUserEditStatus == true){
	$wgGroupPermissions['user'] = $wgGroupPermissions['lguser'];
}



//可视化编辑器

if ( $wgIsUgcWiki ) {

	//可视化编辑器
	require_once( "extensions/VisualEditor/VisualEditor.php");
	// Enable by default for everybody
	$wgDefaultUserOptions['visualeditor-enable'] = 1;
	//// Don't allow users to disable it
	$wgHiddenPrefs[] = 'visualeditor-enable';
	//      $wgVisualEditorSupportedSkins = array( 'vector', 'joyme1' ,'op','marvel','naruto');
	$wgVisualEditorSupportedSkins = array( 'mediawikibootstrap','mediawikibootstrap1','vector', 'joyme1');

	$wgVirtualRestConfig['modules']['parsoid'] = array(
		// URL to the Parsoid instance
		// Use port 8142 if you use the Debian package
		'url' => 'http://parsoid.joyme.'.$wgEnv,
		// Parsoid "domain", see below (optional)
		'domain' => $wgWikiname,
		// Parsoid "prefix", see below (optional)
		'prefix' => $wgWikiname
	);
}

## Set $wgCacheDirectory to a writable directory on the web server
## to make your wiki go slightly faster. The directory should not
## be publically accessible from the web.
#$wgCacheDirectory = "$IP/cache";
$wgCacheDirectory = "$IP/cache/{$wgWikiname}";
$wgResourceLoaderMaxQueryLength = -1;


//$wgUseFileCache = true;
//$wgFileCacheDirectory = "$IP/cache/{$wgWikiname}";

$wgMessageCacheType = CACHE_ACCEL;

$wgUseLocalMessageCache = true;
$wgUseGzip = true;
$wgEnableSidebarCache = false;

$wgMainWANCache = 'mediawiki-main-default';
$wgWANObjectCaches[$wgMainWANCache] = [
	'class'    => 'WANObjectCache',
	'cacheId'  => CACHE_DB,
	'channels' => [ 'purge' => 'wancache-main-default-purge' ]
];


#################### 钩子开始################
require_once(dirname(__FILE__) . '/conf/hooks.php');

################钩子结束######################
# 用户登录

use Joyme\core\JoymeUser;

JoymeUser::initByRequest();
$wgIsLogin = JoymeUser::isLogin();
$wgJoymeUserInfoCheck = false;
$wgJoymeUserInfo = array();
if($wgIsLogin){
	$wgJoymeUserInfo = array(
		'uid'=>JoymeUser::getUid(),
		'uno'=>JoymeUser::getUno(),
		'profileid' => JoymeUser::getPid()
	);
	$wgLoginDomain = JoymeUser::getLoginDomain();
}

//$wgJobRunRate = 0.01;


$wgShowIPinHeader = false;
$wgExternalLinkTarget = "_blank";

$wgShowExceptionDetails = true;

//设置讨论区热帖缓存时间
$wgHotDataCacheExpiredTime = 60*60;
//设置接口请求域名
$wgRequestInterfaceUrl = "http://joymewiki.joyme.".$wgEnv."/";
//设置缓存目录
$wgPostsCachePath = $IP.'/cache/'.$wgWikiname.'/pc/wikiposts';
//
$wgStaticUrl = 'http://static.joyme.'.$wgEnv;
//定义新namespace
define("NS_RECOMMEND", 3000);
$wgExtraNamespaces[NS_RECOMMEND] = "推荐";

$wgRequest = RequestContext::getMain()->getRequest();
$userskin = $wgRequest->getVal("useskin");
if(!empty($userskin)){
	$wgParserCacheType = CACHE_NONE;
}