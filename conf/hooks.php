<?php
use Joyme\core\Utils;

function wfGetPageIDSetToken(OutputPage &$out, &$text)
{
    global $wgNowArticleNamespace, $wgJoymeToken, $wgJoymePageId;
    $text = $text . '<input type="hidden" name="mytoken" id="mytoken" value="' . $wgJoymeToken . '">' .
        '<input type="hidden" name="pageId" id="pageId" value="' . $wgJoymePageId . '">';
}

$wgHooks['OutputPageBeforeHTML'][] = 'wfGetPageIDSetToken';

function getJoymewikiPageid($article, $row)
{
    global $wgThread,$wgWikiname, $wgNowArticleNamespace, $wgRequestInterfaceUrl, $wgJoymeToken, $wgJoymePageId;
    
    if($wgThread == false){
    	return false;
    }
    return true;
    $wgNowArticleNamespace = $namespace = $article->mTitle->mNamespace;
    $title = $article->mTitle->mTextform;
    $token = DataSynchronization::Protection($wgWikiname);
    $url = $wgRequestInterfaceUrl . '?c=wikiPosts&a=outPutPageId&wikikey=' . $wgWikiname . '&title=' . $title . '&token=' . $token . '&namespace=' . $namespace;
    $Specialdiscussion_area = new SpecialDiscussion();
    $res = $Specialdiscussion_area->senRequest($url);
    if ($res['rs'] == 0) {
        $wgJoymePageId = $res['result']['page_id'];
        $wgJoymeToken = DataSynchronization::Protection($res['result']['page_id']);
    }
    return true;

}

$wgHooks['ArticlePageDataAfter'][] = 'getJoymewikiPageid';

function wfGetIwtjs( $skin, &$text ){
	$str = <<<EOF
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?1a79c2baaace62c5deadcdb6d55d557a";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<script>
(function (G,D,s,c,p) {
c={//监测配置
UA:"UA-joyme-000001", //客户项目编号,由系统生成
NO_FLS:0,
WITH_REF:1,
URL:'http://static.joyme.com/js/iwt-min.js'
};
G._iwt?G._iwt.track(c,p):(G._iwtTQ=G._iwtTQ || []).push([c,p]),!G._iwtLoading && lo();
function lo(t) {
G._iwtLoading=1;s=D.createElement("script");s.src=c.URL;
t=D.getElementsByTagName("script");t=t[t.length-1];
t.parentNode.insertBefore(s,t);
}
})(this,document);
</script>
EOF;
	$text.=$str;
	return true;
}


$wgHooks['SkinAfterBottomScripts'][] = 'wfGetIwtjs';

function wfContentRefreshHook(&$content_actions)
{
    global $wgRequest, $wgRequest, $wgTitle;
    $action = $wgRequest->getText('action');
    if ($wgTitle->getNamespace() != NS_SPECIAL) {
        $content_actions['purge'] = array(
            'class' => false,
            'text' => wfMsg('refresh'),
            'href' => $wgTitle->getLocalUrl('action=purge')
        );
    }
    wfDebug("wfContentRefreshHook " . wfMsg('refresh') . " " . $wgTitle->getLocalUrl('action=purge'));
    return true;
}

$wgHooks['SkinTemplateContentActions'][] = 'wfContentRefreshHook';

function efAddSkinStylesAnon(OutputPage &$out, Skin &$skin)
{
    global $wgUser;
    if (!$wgUser->isLoggedIn()) {
        $goto_login = isset($_REQUEST['login']) ? 1 : 0;
        if ($goto_login) {
            $out->addInlineStyle('#pt-login { display:; }');
        } else {
            $out->addInlineStyle('#pt-login { display:none; }');
            $out->addInlineStyle('#right-navigation { display:none; }');
        }
    }
    return true;
}

$wgHooks['BeforePageDisplay'][] = 'efAddSkinStylesAnon';

function wfPageContentSaveCompleteHook(&$content_actions, &$user, $content, $summary, $args4, $args5, $args6, &$flags, $revision, &$status, $baseRevId)
{
    //这是一个同步操作！ 还是使用redis 异步处理比较合理
    global $wgRequest, $wgServer,$wgTitle, $wgUser, $wgIsUgcWiki,$wgWikiname,$wgSiteId,$wgJoymeUserInfo;
    //page数据同步

//	DataSynchronization::addWordsData($content_actions, $wgUser);
//  DataSynchronization::addPostsData($content_actions, $wgUser); //发帖后同步数据给审核后台

    //新增页面和编辑页面的情况下执行，内容没变，不执行
    if(!is_null($status->value['revision'])){
        //百度推送 如果文章新创建，主动推送地址给百度
        //ugcwiki文章类型
        if ($wgIsUgcWiki) {
            if ($wgTitle->getNamespace() == 0) {
                if($status->value['new']){
//            $url = $wgServer . '/' . $wgTitle->getBaseText();
//            Utils::baiduPush($url);
                    JoymeWikiUser::adduseractivity(
                        $wgUser->getId(),
                        'add_page',
                        '创建了页面 <a href="'.$wgServer.'/'.$wgWikiname.'/'.$wgTitle->getBaseText().'" target="_blank">'.$wgTitle->getBaseText().'</a>'
                    );
                    JoymeWikiUser::pointsreport(27,$wgJoymeUserInfo['uid']);
                    JoymeSite::wikiwebservicepost($wgTitle->getBaseText(),$content_actions->mTitle->mArticleID);
                }else{
                    JoymeWikiUser::adduseractivity(
                        $wgUser->getId(),
                        'edit_page',
                        '修改了页面 <a href="'.$wgServer.'/'.$wgWikiname.'/'.$wgTitle->getBaseText().'" target="_blank">'.$wgTitle->getBaseText().'</a>'
                    );
                    JoymeWikiUser::pointsreport(28,$wgJoymeUserInfo['uid']);
                }
                //上报到cms
                JoymeWikiUser::cmsreport($wgSiteId, $wgTitle->getBaseText(), strtotime($revision->getTimestamp()));
                
				//更新贡献者
                if($status->value['new']){
                	$edit_length = $revision->getCreateLength();
                }else{
                	$edit_length = $revision->getEditLength();
                }
                JoymePageContribute::updateContributeUser($wgTitle->getArticleID(),$edit_length);
                
                //更新用户站点编辑数
                JoymeSite::editSiteEditCountLog();
                JoymeSite::updateSitePageCount();
                JoymeSite::updateSiteEditCount();
                JoymeSite::updateSiteEditUserCount();
                JoymeWikiUser::addUserSiteOfferCount();
                JoymeWikiUser::editUserEditCountlog();
                JoymeWikiUser::updateUserEditCount();
                JoymeWikiUser::addUserSiteContribute($wgJoymeUserInfo['uid'],$wgSiteId);
            }
        }
    }


    return true;
}

$wgHooks['PageContentSaveComplete'][] = 'wfPageContentSaveCompleteHook';

// 删除文章帖子同步数据
function wfArticleDeleteCompleteHook(&$article, User &$user, $reason, $id, $content, $logEntry)
{
    global $wgWikiname, $com;
    $namespace = $article->mTitle->mNamespace;
    $DataSynchronization = new DataSynchronization();
    if (!in_array($namespace, $DataSynchronization->namespaceArr)) {
        return true;
    }
    $token = DataSynchronization::Protection($wgWikiname);
    $type = 'del';
    $title = $article->mTitle->mTextform;
    $posturl = "http://joymewiki.joyme.$com/?c=wikiPosts&a=UpdateCompletelyDelete&title=$title&page_namespace=$namespace&type=$type&wikikey=$wgWikiname&token=$token";
    $Specialdiscussion = new SpecialDiscussion();
    $res = $Specialdiscussion->senRequest($posturl);
    if ($res['result'] == 'fail') {
        Log::error(wfArticleDeleteCompleteHook . ' ' . $res['msg'] . ' ' . $posturl);
    }
    return true;
}

$wgHooks['ArticleDeleteComplete'][] = 'wfArticleDeleteCompleteHook';

//添加cdn的purgeurl
function wfTitleSquidURLs(Title $title,&$urls){
    if($urls){
        $wikiappurl = $urls[0]."?useskin=MediaWikiBootstrap2";
        $wikiappshareurl = $urls[0]."?useskin=JShare";
        $urls[] = $wikiappurl;
        $urls[] = $wikiappshareurl;
    }
    return true;
}

$wgHooks['TitleSquidURLs'][] = 'wfTitleSquidURLs';

