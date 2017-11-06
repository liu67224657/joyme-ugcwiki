<?php
/**
 * @file
 * @ingroup SpecialPage Favoritelist
 */

/**
 * Constructor
 *
 * @param $par Parameter
 *        	passed to the page
 */
use Joyme\core\Request;
use Joyme\page\Page;
class SpecialFavoritelist extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'Favoritelist' );
	}
	public function execute($par) {
		global $wgWikiname,$wgEnv;
		$context = $this->getContext();
		$output = $this->getOutput();
		
		$user = $this->getUser();
		
		// This feature is available only to logged-in users.
		if ( !$user->isLoggedIn() ) {
			$output->setPageTitle( $this->msg( 'boardblastlogintitle' )->plain() );
			$output->addWikiMsg( 'boardblastlogintext' );
			$output->redirect('http://wiki.joyme.'.$wgEnv);
			return false;
		}
		
		if($wgWikiname !='home'){
			$output->redirectHome('Special:Favoritelist');
			return;
		}
		$this->setHeaders();
		$output->addModuleStyles( array('ext.socialprofile.userprofile.usercentercommon.css','ext.favorites.style','ext.socialprofile.userboard.headskin.css') );
		$output->addModules( 'ext.favorites' );
		$param = $this->getRequest()->getText ( 'param' );
		$vwfav = new ViewFavorites ($context);
		$vwfav->wfSpecialFavoritelist ( $par );
	}

	protected function getGroupName() {
		return 'other';
	}
}

class ViewFavorites {
	
	private $context;
	private $user;
	private $out;
	private $request;
	private $lang;
	
	private $total = 0;
	private $wiki_key = '';
	private $pagesize = 20;
	private $pageno = 1;
	private $_pagelist = '';// 分页html
	private $siteinfo = array();
	private $userwiki = array();
	
	
	public function __construct($context) {
		$this->context = $context;
		$this->out = $this->context->getOutput();
		$this->request = $this->context->getRequest();
		$this->lang = $this->context->getLanguage();
		$this->user = $this->context->getUser();
		$this->uid = $this->user->getId();
		$this->wiki_key = $this->request->getText('wiki_key');
		$this->pageno = $this->request->getInt( 'pb_page' ) ? $this->request->getInt( 'pb_page' ) : 1;
	}
	
	public function wfSpecialFavoritelist($par) {
		global $wgFeedClasses;
		$specialTitle = SpecialPage::getTitleFor ( 'Favoritelist' );
		$this->out->setRobotPolicy ( 'noindex,nofollow' );
		// Anons don't get a favoritelist
		if ($this->user->isAnon ()) {
			// $this->out->setPageTitle ( wfMessage ( 'favoritenologin' ) );
			// $llink = Linker::linkKnown ( SpecialPage::getTitleFor ( 'Userlogin' ), wfMessage ( 'loginreqlink' )->text (), array (), array (
					// 'returnto' => $specialTitle->getPrefixedText () 
			// ) );
			// $this->out->addHTML ( wfMessage ( 'favoritelistanontext', $llink )->text () );
//			echo '<script>mw.loginbox.login();</script>';
			echo '<script>loginDiv();</script>';
			return;
		}
		
		$html = '<div class="row">
            <!-- 左侧区域 开始 -->
            <div class="col-md-9">
                <div id="main">
                    <div class="like-list-box ">
					<input type="hidden" id="favListSum" name="favListSum" value="'.$this->total .'">
                        '.$this->getFormHeader().'
                        '.$this->viewFavList ( $this->user, $this->out, $this->request ).'
                    </div>
                    '.$this->getPageList().'
                </div>
            </div>
            <!-- 左侧区域 结束 -->
            <!-- 右侧区域  开始 -->
            '.$this->getRight().'
            <!-- 右侧区域  开始 -->
        </div>';
		$this->out->addHTML($html);
	}
	
	private function getFormHeader(){
		$dbr = wfGetDB ( DB_SLAVE );
		// $res = $dbr->select('user_site_relation', 'site_id', '`status`<3 AND user_id='.$this->uid);
		$sql = 'SELECT DISTINCT fl_wikikey from favoritelist WHERE fl_user = '.$this->uid;
		$res = $dbr->query($sql);
		$sitekeys = array();
		foreach($res as $row){
			$sitekeys[] = $row->fl_wikikey;
		}
		if(count($sitekeys)==0){
			// $output->addHTML( '没有贡献wiki' );
			return;
		}
		
		$siteres = $dbr->select('joyme_sites', 'site_name,site_key', 'site_type=1 AND site_key IN (\''.implode('\',\'', $sitekeys).'\')');
		foreach($siteres as $row){
			$firstcat = JMCommonFns::getFirstCharter($row->site_name);
			$this->userwiki[$firstcat][$row->site_key] = $row->site_name;
		}
		ksort($this->userwiki);
		$specialTitle = SpecialPage::getTitleFor ( 'Favoritelist' );
		$title = $specialTitle->getPrefixedText();
		$html = '<h1 class="page-h1 pag-hor-20 fn-clear">
					我的收藏
					<div class="fn-right fn-clear sc-select">
						选择WIKI
						<div class="fn-right ele-select">
						<form method="get" action="'.wfScript().'" id="favform">'.
						Html::hidden( 'uid', $this->uid ).
						Html::hidden( 'title', $title ).
							'<div class="select-area">
								<div class="select-ele">
									<span class="select-value">所有</span>
									<i class="fa fa-angle-down"></i>
								</div>
								<select name="wiki_key" >
									<option value="">所有</option>';
		foreach($this->userwiki as $item){
			foreach($item as $key=>$val){
				$selected = $this->wiki_key==$key ? 'selected="selected"':'';
				$html .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
			}
		}	
		$html .= '</select></div></form></div></div></h1>';
		return $html;
	}
	
	private function getRight(){
		$joymewikiuser = new JoymeWikiUser();
		$user_profiles = $joymewikiuser->getProfile(array($this->uid));
		$user_profiles = $user_profiles[0];
		$user_sex = '';

		if($user_profiles['sex'] === ''){
			$user_sex = '';
		}else if($user_profiles['sex'] == 1){
			$user_sex = 'man';
		}else if($user_profiles['sex'] == 0){
			$user_sex = 'female';
		}
		$back_link = Title::makeTitle( NS_USER, $user_profiles['nick'] );
        $user_link = htmlspecialchars( $back_link->getFullURL() );
		
		$html = '<div class="col-md-3 web-hide ">
                <div id="sidebar">
                    <div class="user-mess-box"> 
                        <div class="user-int-mess">
                            <a target="_blank" href="'.$user_link.'" class="userinfo" data-username="'.$user_profiles['profileid'].'"><img src="'.$user_profiles['icon'].'">'.($user_profiles['vtype']>0?'<span class="user-vip" title="'.$user_profiles['vdesc'].'"></span>':'').'<span class="luojiaoye-def luojiaoye-dec-0'.$user_profiles['headskin'].'"></span></a>
                            <font class="nickname">'.$user_profiles['nick'].'</font>
                            <i class="user-sex '.$user_sex.'"></i>
                        </div>
                        <div class="user-messing-situ ">
                            <span class="link-count" id="favtotal" data-count="'.$this->total .'">收藏页面数量:'.$this->total .'</span>
                        </div>
                    </div>
                    <!-- 广告位  开始 -->
                    <!-- <div class="ad-con">
                        <cite><img src="img/ad-img.jpg"><i>活动</i></cite>
                    </div> -->
                    <!-- 广告位  结束 -->
                </div>
            </div>';
		return $html;
	}
	
	private function getPageList(){
		if($this->total == 0 || $this->total<=$this->pagesize){
			return '';
		}
		$self = SpecialPage::getTitleFor ( 'Favoritelist' );
		$url = $self->getLocalURL(
			array('uid'=>$this->uid,'wiki_key'=>$this->wiki_key)
		);
		$_page = new Page(array('total' => $this->total,'perpage'=>$this->pagesize,'nowindex'=>$this->pageno,'pagebarnum'=>10,'url'=>$url,'classname'=>array( 'main_page'=>'paging','active'=>'on')));
        $this->_pagelist = '<div class="paging">'.$_page->show(2).'</div>';
		return $this->_pagelist;
	}
	
	private function viewFavList($user, $output, $request) {

		$uid = $this->user->getId ();
		// $output->setPageTitle ( wfMessage ( 'favoritelist' ) );
		
		// if ($request->wasPosted () && $this->checkToken ( $request, $this->user )) {
			// $titles = $this->extractTitles ( $request->getArray ( 'titles' ) );
			// $this->unfavoriteTitles ( $titles, $user );
			// $user->invalidateCache ();
			// $output->addHTML ( wfMessage ( 'favoritelistedit-normal-done', $GLOBALS ['wgLang']->formatNum ( count ( $titles ) ) )->parse () );
			// $this->showTitles ( $titles, $output, $this->user->getSkin () );
		// }
		
		$this->total = $this->countFavoritelist($user);
		if ($this->total == 0) {
			$html = '<div class="no-data"><cite class="no-data-img"></cite><p>还没有收藏内容</p></div>';
			return $html;
		}
		// $this->showNormalForm ( $output, $user );
		return $this->showList ( $output, $user );
		
		// $dbr = wfGetDB ( DB_SLAVE, 'favoritelist' );
		// $recentchanges = $dbr->tableName( 'recentchanges' );
		
		// $favoritelistCount = $dbr->selectField ( 'favoritelist', 'COUNT(fl_user)', array (
				// 'fl_user' => $uid 
		// ), __METHOD__ );
		// Adjust for page X, talk:page X, which are both stored separately,
		// but treated together
		// $nitems = floor($favoritelistCount / 2);
		
	}
	
	/**
	 * Check the edit token from a form submission
	 *
	 * @param $request WebRequest        	
	 * @param $user User        	
	 * @return bool
	 */
	private function checkToken($request, $user) {
		return $user->matchEditToken ( $request->getVal ( 'token' ), 'favorite' );
	}
	
	/**
	 * Extract a list of titles from a blob of text, returning
	 * (prefixed) strings; unfavoritable titles are ignored
	 *
	 * @param $list mixed        	
	 * @return array
	 */
	private function extractTitles($list) {
		$titles = array ();
		if (! is_array ( $list )) {
			$list = explode ( "\n", trim ( $list ) );
			if (! is_array ( $list ))
				return array ();
		}
		foreach ( $list as $text ) {
			$text = trim ( $text );
			if (strlen ( $text ) > 0) {
				$title = Title::newFromText ( $text );
				// if( $title instanceof Title && $title->isFavoritable() )
				$titles [] = $title->getPrefixedText ();
			}
		}
		return array_unique ( $titles );
	}
	
	/**
	 * Print out a list of linked titles
	 *
	 * $titles can be an array of strings or Title objects; the former
	 * is preferred, since Titles are very memory-heavy
	 *
	 * @param $titles An
	 *        	array of strings, or Title objects
	 * @param $output OutputPage        	
	 */
	private function showTitles($titles, $output) {
		$talk = wfMessage ( 'talkpagelinktext' )->text ();
		// Do a batch existence check
		$batch = new LinkBatch ();
		foreach ( $titles as $title ) {
			if (! $title instanceof Title)
				$title = Title::newFromText ( $title );
			// if( $title instanceof Title ) {
			// $batch->addObj( $title );
			// $batch->addObj( $title->getTalkPage() );
			// }
		}
		$batch->execute ();
		// Print out the list
		$output->addHTML ( "<ul>\n" );
		foreach ( $titles as $title ) {
			if (! $title instanceof Title)
				$title = Title::newFromText ( $title );
			if ($title instanceof Title) {
				$output->addHTML ( "<li>" . Linker::link ( $title ) . 
				"</li>\n" );
			}
		}
		$output->addHTML ( "</ul>\n" );
	}
	
	/**
	 * Count the number of titles on a user's favoritelist, excluding talk pages
	 *
	 * @param $user User        	
	 * @return int
	 */
	private function countFavoritelist($user) {
		$dbr = wfGetDB ( DB_MASTER );
		$conds = array('fl_user' => $user->getId ());
		if(!empty($this->wiki_key)){
			$conds['fl_wikikey'] = $this->wiki_key;
		}
		$res = $dbr->select ( 'favoritelist', 'COUNT(fl_user) AS count', 
			$conds , __METHOD__ );
		$row = $dbr->fetchObject ( $res );
		return ceil ( $row->count ); // Paranoia
	}
	
	/**
	 * Get a list of titles on a user's favoritelist, excluding talk pages,
	 * and return as a two-dimensional array with namespace, title and
	 * redirect status
	 *
	 * @param $user User        	
	 * @return array
	 */
	private function getFavoritelistInfo($user) {
		$titles = array ();
		$dbr = wfGetDB ( DB_MASTER );
		$uid = intval ( $user->getId () );
		// list ( $favoritelist, $page ) = $dbr->tableNamesN ( 'favoritelist', 'page' );
		$data = $dbr->select('favoritelist', '*',
			array('fl_user'=>$uid));
		return $data;
		// $sql = "SELECT fl_namespace, fl_title, page_id, page_len, page_is_redirect
			// FROM {$favoritelist} LEFT JOIN {$page} ON ( fl_namespace = page_namespace
			// AND fl_title = page_title ) WHERE fl_user = {$uid}";
		// $res = $dbr->query ( $sql, __METHOD__ );
		// if ($res && $dbr->numRows ( $res ) > 0) {
			// $cache = LinkCache::singleton ();
			// while ( $row = $dbr->fetchObject ( $res ) ) {
				// $title = Title::makeTitleSafe ( $row->fl_namespace, $row->fl_title );
				// if ($title instanceof Title) {

					// if ($row->page_id) {
						// $cache->addGoodLinkObj ( $row->page_id, $title, $row->page_len, $row->page_is_redirect );
					// } else {
						// $cache->addBadLinkObj ( $title );
					// }

					// if (! $title->isTalkPage ())
						// $titles [$row->fl_namespace] [$row->fl_title] = $row->page_is_redirect;
				// }
			// }
		// }
		// return $titles;
	}
	
	
	/**
	 * Remove a list of titles from a user's favoritelist
	 *
	 * $titles can be an array of strings or Title objects; the former
	 * is preferred, since Titles are very memory-heavy
	 *
	 * @param $titles An
	 *        	array of strings, or Title objects
	 * @param $user User        	
	 */
	private function unfavoriteTitles($titles, $user) {
		global $wgWikiname;
		$dbw = wfGetDB ( DB_MASTER );
		
		foreach ( $titles as $title ) {
			
			if (! $title instanceof Title)
				$title = Title::newFromText ( $title );
			if ($title instanceof Title) {
				
				$dbw->delete ( 'favoritelist', array (
						'fl_user' => $user->getId (),
						'fl_namespace' => ($title->getNamespace () | 1),
						'fl_title' => $title->getDBkey (),
						'fl_wikikey'=> $wgWikiname
				), __METHOD__ );
				$article = new Article ( $title );
				Hooks::run ( 'UnfavoriteArticleComplete', array (
						&$user,
						&$article 
				) );
			}
		}
	}
	
	/**
	 * Show the standard favoritelist editing form
	 *
	 * @param $output OutputPage        	
	 * @param $user User        	
	 */
	private function showNormalForm($output, $user) {
		
		if (($count = $this->countFavoritelist ( $user )) > 0) {
			//$self = SpecialPage::getTitleFor ( 'Favoritelist' );
			// $form = Xml::openElement ( 'form', array (
					// 'method' => 'post',
					// 'action' => $self->getLocalUrl ( array (
							// 'action' => 'edit' 
					// ) ) 
			// ) );
			//$form .= Html::hidden ( 'token', $this->user->getEditToken ( 'favorite' ) );
			// $form .= "<fieldset>\n<legend>" . wfMsgHtml( 'favoritelistedit-normal-legend' ) . "</legend>";
			// $form .= wfMsgExt( 'favoritelistedit-normal-explain', 'parse' );
			$form = $this->buildRemoveList ( $user, $this->user->getSkin () );
			// $form .= '<p>' . Xml::submitButton( wfMsg( 'favoritelistedit-normal-submit' ) ) . '</p>';
			//$form .= '</fieldset></form>';
			$output->addHTML ( $form );
		}
	}
	
	// list
	private function showList($output, $user) {
		
		if ($this->total > 0) {
			return $this->buildRemoveList ( $user, $this->user->getSkin ());
			// $s = '';
			// for($i=0;$i<20;$i++){
				// $s .= $this->buildRemoveList ( $user, $this->user->getSkin ());
			// }
			return $s;
		}
		return '';
	}
	
	/**
	 * Build the part of the standard favoritelist editing form with the actual
	 * title selection checkboxes and stuff.
	 * Also generates a table of
	 * contents if there's more than one heading.
	 *
	 * @param $user User        	
	 */
	private function buildRemoveList($user) {
		$list = "";
		$skip = ($this->pageno-1)*$this->pagesize;
		$toc = Linker::tocIndent ();
		$tocLength = 0;
		$data = $this->getFavoritelistInfo ( $user );

		$dbr = wfGetDB( DB_SLAVE );
		$conds = array('fl_user' => $user->getId ());
			if(!empty($this->wiki_key)){
				$conds['fl_wikikey'] = $this->wiki_key;
			}
		$data = $dbr->select('favoritelist', '*',
			$conds , __METHOD__ ,
			array('ORDER BY'=>'fl_touchedtime desc', 
					'LIMIT'=>$this->pagesize, 'OFFSET'=>$skip));
		$list .= '<dl class="like-list list-item">
                            <dt class="fn-clear">
                                <span class="like-tit">标题</span>
								<span class="form-action">操作</span>
                                <span class="form-wiki">WIKI</span>
                                <span class="edit-num">编辑次数</span>
                                <span class="last-time">更改时间</span>
                            </dt>';
		$wikikeys = array();
		foreach($data as $row){
			$wikikeys[] = $row->fl_wikikey;
		}
		$res = $dbr->select('joyme_sites', array('site_key', 'site_name'), 'site_key IN (\''.implode('\',\'', $wikikeys).'\')');
		foreach($res as $val){
			$this->siteinfo[$val->site_key] = $val->site_name;
		}
		foreach($data as $row){
			$title = Title::makeTitleSafe ( $row->fl_namespace, $row->fl_title );
			
			$list .= $this->buildRemoveLine ( $title, $row );
			
		}
		$list .= '</dl>';
		
		return $list;
		// foreach ( $data as $namespace => $pages ) {
			// $tocLength ++;
			// $heading = htmlspecialchars ( $this->getNamespaceHeading ( $namespace ) );
			// $anchor = "editfavoritelist-ns" . $namespace;
			
			// $list .= Linker::makeHeadLine ( 2, ">", $anchor, $heading, "" );
			// $toc .= Linker::tocLine ( $anchor, $heading, $tocLength, 1 ) . Linker::tocLineEnd ();
			
			// $list .= "<ul>\n";
			// foreach ( $pages as $dbkey => $redirect ) {
				// $title = Title::makeTitleSafe ( $namespace, $dbkey );
				// $list .= $this->buildRemoveLine ( $title, $redirect );
			// }
			// $list .= "</ul>\n";
		// }
		// if ($tocLength > 10) {
			// $list = Linker::tocList ( $toc ) . $list;
		// }
		
		// return $list;
	}
	
	/**
	 * Get the correct "heading" for a namespace
	 *
	 * @param $namespace int        	
	 * @return string
	 */
	private function getNamespaceHeading($namespace) {
		return $namespace == NS_MAIN ? wfMessage ( 'blanknamespace' )->text () : htmlspecialchars ( $GLOBALS ['wgContLang']->getFormattedNsText ( $namespace ) );
	}
	
	/**
	 * Build a single list item containing a check box selecting a title
	 * and a link to that title, with various additional bits
	 *
	 * @param $title Title        	
	 * @param $redirect bool        	
	 * @return string
	 */
	private function buildRemoveLine($title, $row) {
		global $wgWikiname;
		// In case the user adds something unusual to their list using the raw editor
		// We moved the Tools array completely into the "if( $title->exists() )" section.
		// $showlinks = false;
		
		$link = Linker::link ( $title );
			$unfavoritlink = '<span style="cursor:pointer" class="unfavorite" data-wikikey="'.$row->fl_wikikey .'" data-title="'.$title->getDBkey().'" data-ns="'.$row->fl_namespace.'">取消收藏</span>';
			// if( $row->fl_namespace == 0 ){
				// $link = '<a target="_blank" href="'.'/'.$row->fl_wikikey . '/' . $title->getDBkey().'">'.$title->getDBkey().'</a>';
			// }else if( $row->fl_namespace == 6 ){
				// $link = '<a target="_blank" href="'.'/'.$row->fl_wikikey . '/文件:' . $title->getDBkey().'">'.$title->getDBkey().'</a>';
			// }
			preg_match("#<a[^>]*>(.*?)</a>#is", "$link", $match);
			$href = '/'.$row->fl_wikikey .'/'.$match[1];
			$link = '<a target="_blank" href="'. $href .'">'. $match[1] .'</a>';
			$time = date('Y.m.d', strtotime($row->fl_touchedtime));
			return '<dd>'.$link.' '.$unfavoritlink.' <span>'.$this->siteinfo[$row->fl_wikikey].'</span><span>'.$row->fl_editcount.'</span><span>'.$time.'</span></dd>';
		// }
	}
}
?>