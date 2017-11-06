<?php
use Joyme\core\Request;
use Joyme\page\Page;
class SpecialJContribution extends SpecialPage {
	
	private $year = 0;
	private $month = 0;
	private $wiki_key = '';
	private $actype = '';
	private $userid = 0;
	private $pageno = 1;
	private $pagesize = 25;
	private $username = '';
	private $editcount = 0;
	private $userwiki = array();
	private $actypes = array('edit'=>'修改', 'new'=>'新增');
	
	function __construct() {
		parent::__construct( 'JContribution' );
	}

	function execute( $par ) {
		global $wgWikiname;
		$context = $this->getContext();
		// $this->user = $this->context->getUser();
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$output->addModuleStyles( array('ext.socialprofile.userprofile.usercentercommon.css','ext.jcontribution.css','ext.socialprofile.userboard.headskin.css') );
		$output->addModules( 'ext.jcontribution.js' );
		# Get request data from, e.g.
		$this->year = $request->getInt( 'year' );
		$this->month = $request->getInt( 'month' );
		if($this->year == 0 && $this->month){
			$this->year = date('Y', time());
		}
		$this->pageno = $request->getInt( 'pb_page' ) ? $request->getInt( 'pb_page' ) : 1;
		$this->wiki_key = $request->getText( 'wiki_key' );
		$this->actype = $request->getText( 'actype' );
		$this->userwiki = array();
		$this->userid = $request->getInt( 'userid' );
		$url = 'Special:JContribution&userid='.$this->userid;
		
		if($wgWikiname !='home'){
			$output->redirectHome($url);
			return;
		}
		
		if(!$this->userid){
			$this->user = $context->getUser();
			$this->userid = $this->user->getId();
			$this->wiki_key = '';
			$url = 'Special:JContribution';
		}else{
			$this->user = User::newFromId($this->userid);
		}
		
		
		$output->setPageTitle( '编辑列表' );
		
		// user
		$dbr = wfGetDB( DB_SLAVE );

		if(User::isUsableName($this->user->getName()) == false){
			$output->addHTML( '该用户不存在' );
			return '';
		}
		
		$this->username = $this->user->getName();
		// user wiki
		$res = $dbr->select('user_site_relation', 'site_id', '`status`<3 AND user_id='.$this->userid);
		$siteids = array();
		foreach($res as $row){
			$siteids[] = $row->site_id;
		}
		if(count($siteids)==0){
			// $output->addHTML( '没有贡献wiki' );
			$output->addHTML($this->noDataShow());
			return;
		}
		
		$siteres = $dbr->select('joyme_sites', 'site_name,site_key', 'site_type=1 AND site_id IN ('.implode(',', $siteids).')');
		foreach($siteres as $row){
			$firstcat = JMCommonFns::getFirstCharter($row->site_name);
			$this->userwiki[$firstcat][$row->site_key] = $row->site_name;
		}
		ksort($this->userwiki);
		if($this->wiki_key == ''){
			$tmp = current($this->userwiki);
			$this->wiki_key = key($tmp);
		}
		$output->addHTML( $this->getBody() );
	}
	
	public function getMain(){
		global $wgWikiname;
		$skip = ($this->pageno-1)*$this->pagesize;
		$limit = $this->pagesize;
		$dbname = $this->wiki_key.'wiki';
		
		$dbr = wfGetDB( DB_SLAVE );
		$dbr->selectDB($dbname);
		
		$cond = "rev_user=$this->userid AND page_namespace IN(0,6,10)";
		if($this->actype == 'new'){
			$cond .= ' AND rev_parent_id = 0';
		}else if($this->actype == 'edit'){
			$cond .= ' AND rev_parent_id != 0';
		}
		if($this->year && $this->month){
			$cond .= ' AND rev_timestamp > '.$this->year.sprintf('%02d', $this->month).'00000000';
			if($this->month+1 == 13){
				$cond .= ' AND rev_timestamp < '.($this->year+1) .'0000000000';
			}else{
				$cond .= ' AND rev_timestamp < '.$this->year.sprintf('%02d', ($this->month+1)).'00000000';
			}
		}else if($this->year){
			$cond .= ' AND rev_timestamp > '.$this->year .'0000000000';
			$cond .= ' AND rev_timestamp < '.($this->year+1) .'0000000000';
		}
		$res = $dbr ->selectRow(
			array( 'revision', 'page' ),
			array( 'count(*) as total' ),
			$cond,
			__METHOD__,
			array(),
			array( 'page' => array( 'INNER JOIN', array(
				'rev_page=page_id' ) ) )
		);
		$this->editcount = $total = intval($res->total);
		if( $this->editcount == 0 ){
			$html = '<div class="no-data"><cite class="no-data-img"></cite></div>';
			return $html;
		}
		$fields = 'rev_id,rev_page,rev_text_id,rev_timestamp,rev_comment,rev_user_text,rev_user,rev_minor_edit,rev_deleted,rev_len,rev_parent_id,rev_sha1,rev_content_format,rev_content_model,page_namespace,page_title,page_is_new,page_latest,page_is_redirect,page_len';
		$revdata = $dbr->select(
				array( 'revision', 'page' ),
				$fields,
				$cond,
				__METHOD__,
				array('ORDER BY'=>'rev_id desc', 
						'LIMIT'=>$limit, 
						'OFFSET'=>$skip),
				array( 'page' => array( 'INNER JOIN', array(
						'page_id=rev_page' ) ) )
		);
		$pager = new JContribsPager( $this->getContext(), array() );
		$html = '<div class="edit-list-box">'.$this->getEditCount().'<ul class="edit-list list-item">';
		foreach ($revdata as $key => $value) {
			$value->user_name = $this->username;
			$value->ts_tags = null;
			$tr = $pager->formatRow($value);
			$tr = str_replace("/$wgWikiname/", "/$this->wiki_key/", $tr);
			$html .= $tr;
		}
		$html .= '</ul></div>';
		$url = $this->getPageTitle()->getLocalURL(
			array('userid'=>$this->userid,
			'wiki_key'=>$this->wiki_key,
			'year'=>$this->year,
			'month'=>$this->month,
			'actype'=>$this->actype));
		
		if($total > $this->pagesize){
			$_page = new Page(array('total' => $total,'perpage'=>$this->pagesize,'nowindex'=>$this->pageno,'pagebarnum'=>10,'url'=>$url,'classname'=>array( 'main_page'=>'paging','active'=>'on')));
			$html .= '<div class="paging">'.$_page->show(2).'</div>';
		}
		return $html;
	}
	
	private function getBody(){
		$title = $this->getPageTitle()->getPrefixedText();
		$body = '<div class="row">
            <!-- 左侧区域 开始 -->
            <div class="col-md-9"><div class="select-box fn-clear pag-hor-20">
			<form method="get" action="'.wfScript().'" id="jcform">'.
				Html::hidden( 'userid', $this->userid ).
				Html::hidden( 'title', $title ).
					$this->getYearSel().
					$this->getMonthSel().
					$this->getWikiSel().
					$this->getActypeSel().'
                </form></div>
                <div id="main">'.$this->getMain().'</div>
            </div>
            <!-- 左侧区域 结束 -->
            <!-- 右侧区域  开始 -->
            '.$this->getRitht().'
            <!-- 右侧区域  结束 -->
        </div>';
		return $body;
	}
	
	private function noDataShow(){
		$title = $this->getPageTitle()->getPrefixedText();
		$body = '<div class="row">
            <!-- 左侧区域 开始 -->
            <div class="col-md-9"><div class="select-box fn-clear pag-hor-20">
			<form method="get" action="'.wfScript().'" id="jcform">'.
			Html::hidden( 'userid', $this->userid ).
			Html::hidden( 'title', $title ).
					$this->getYearSel().
					$this->getMonthSel().
					$this->getWikiSel().
					$this->getActypeSel().'
                </form></div>
                <div id="main">'.$this->getMain().'</div>
            </div>
            <!-- 左侧区域 结束 -->
            <!-- 右侧区域  开始 -->
            '.$this->getRitht().'
            <!-- 右侧区域  结束 -->
        </div>';
		return $body;
	}
	
	private function getYearSel(){
		$startyear = 2012;// 选择wiki开始年份
		$nowyear = date('Y', time());
		$html = '<div class="select-area"><div class="select-ele"><span class="select-value">截止年份</span><i class="fa fa-angle-down"></i></div>';
		$html .= '<select name="year"><option value="0">选择年</option>';
		for($i=$nowyear;$i>$startyear;$i--){
			$selected = $this->year==$i ? 'selected="selected"':'';
			$html .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}
		$html .= '</select></div>';
		return $html;
	}
	
	private function getMonthSel(){
		$html = '<div class="select-area"><div class="select-ele"><span class="select-value">截止月份</span><i class="fa fa-angle-down"></i></div>';
		$html .= '<select name="month"><option value="0">选择月</option>';
		for($i=1;$i<13;$i++){
			$selected = $this->month==$i ? 'selected="selected"':'';
			$html .= '<option value="'.$i.'" '.$selected.'>'.$i.'月</option>';
		}
		$html .= '</select></div>';
		return $html;
	}
	
	private function getWikiSel(){
		$html = '<div class="select-area"><div class="select-ele"><span class="select-value">贡献WIKI</span><i class="fa fa-angle-down"></i></div>';
		$html .= '<select name="wiki_key">';
		if(empty($this->userwiki)){
			$html .= '<option value="">选择wiki</option>';
			$html .= '</select></div>';
			return $html;
		}
		foreach($this->userwiki as $item){
			foreach($item as $key=>$val){
				$selected = $this->wiki_key==$key ? 'selected="selected"':'';
				$html .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
			}
		}
		$html .= '</select></div>';
		return $html;
	}
	
	private function getActypeSel(){
		$html = '<div class="select-area"><div class="select-ele"><span class="select-value">动作</span><i class="fa fa-angle-down"></i></div>';
		$html .= '<select name="actype"><option value="">选择动作</option>';
		foreach($this->actypes as $key=>$val){
			$selected = $this->actype==$key ? 'selected="selected"':'';
			$html .= '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
		}
		$html .= '</select></div>';
		return $html;
	}
	
	private function getEditCount(){
		global $wgUser;
		$wikiname = '';
		foreach($this->userwiki as $item){
			foreach($item as $key=>$val){
				if($this->wiki_key == $key){
					$wikiname = $val;
					break;
				}
			}
		}
		$html = '<h1 id="editSum" class="page-h1 pag-hor-20 fn-clear" data-editSum="'.$this->editcount .'">';
		if($wgUser->mId == $this->userid){
			$html .= '我在'.$wikiname.'编辑了'.$this->editcount .'条';
		}else{
			$html .= $this->username. $wikiname .'编辑了'.$this->editcount .'条';
		}
		$html .= '</h1>';
		return $html;
	}
	
	private function getRitht(){
		$joymewikiuser = new JoymeWikiUser();
		$joymewikiuser->getUserAddition($this->userid);
		$user_profiles = $joymewikiuser->getProfile(array($this->userid));
		$user_profiles = $user_profiles[0];
		$dbr = wfGetDB( DB_SLAVE );
		$dbr->selectDB('homewiki');
		$usr_res = $dbr->select('user_site_relation', '`status`', 'user_id='.$this->userid);
		$manage_count = 0;//管理WIKI数 1
		$contribs_count = 0;//贡献WIKI数 2 
		$attention_count = 0;//关注WIKI数 3
		foreach($usr_res as $row){
			if($row->status == 1){
				$manage_count++;
			}else if($row->status == 2){
				$contribs_count++;
			}else if($row->status == 3){
				$attention_count++;
			}
		}
		$user_sex = '';
		if($user_profiles['sex'] === ''){
			$user_sex = '';
		}else if($user_profiles['sex'] == 1){
			$user_sex = 'man';
		}else if($user_profiles['sex'] == 0){
			$user_sex = 'female';
		}
		$stats = new UserStats($this->userid, $this->username);
        $stats_data = $stats->getUserStats();
		
		$back_link = Title::makeTitle( NS_USER, $user_profiles['nick'] );
        $user_link = htmlspecialchars( $back_link->getFullURL() );
					
		$html = '<div class="col-md-3 web-hide ">
                <div id="sidebar">
                    <div class="user-mess-box"> 
                        <div class="user-int-mess">
                            <a href="'.$user_link.'"><img src="'.$user_profiles['icon'].'"><span class="luojiaoye-def luojiaoye-dec-0'.$user_profiles['headskin'].'"></span>'.($user_profiles['vtype']>0?'<span class="user-vip" title="'.$user_profiles['vdesc'].'"></span>':'').'</a>
                            <font class="nickname">'.$user_profiles['nick'].'</font>
                            <i class="user-sex '.$user_sex.'"></i>
                        </div>
                        <div class="user-messing-situ wiki-count">
                            <a href="javascript:;"><i>'.$joymewikiuser->total_edit_count .'</i><br>总编辑次数</a>
                            <a href="javascript:;"><i>'.$manage_count.'</i><br>管理WIKI数</a>
                            <a href="javascript:;"><i>'.$contribs_count.'</i><br>贡献WIKI数</a>
                            <a href="javascript:;"><i>'.$attention_count.'</i><br>关注WIKI数</a>
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
}


/**
 * Pager for Special:Contributions
 * @ingroup SpecialPage Pager
 */
class JContribsPager extends ReverseChronologicalPager {
	public $mDefaultDirection = IndexPager::DIR_DESCENDING;
	public $messages;
	public $target;
	public $namespace = '';
	public $mDb;
	public $preventClickjacking = false;

	/** @var IDatabase */
	public $mDbSecondary;

	/**
	 * @var array
	 */
	protected $mParentLens;

	public function __construct( IContextSource $context, array $options ) {
		parent::__construct( $context );

		$msgs = array(
			'diff',
			'hist',
			'pipe-separator',
			'uctop'
		);

		foreach ( $msgs as $msg ) {
			$this->messages[$msg] = $this->msg( $msg )->escaped();
		}

	
	}

	public function formatRow( $row ) {

		// $ret = '';
		$classes = array();

		MediaWiki\suppressWarnings();
		try {
			$rev = new Revision( $row );
			$validRevision = (bool)$rev->getId();
		} catch ( Exception $e ) {
			$validRevision = false;
		}
		MediaWiki\restoreWarnings();

		if ( $validRevision ) {
			$classes = array();

			$page = Title::newFromRow( $row );
			$link = Linker::link(
				$page,
				htmlspecialchars( $page->getPrefixedText() ),
				array(),
				$page->isRedirect() ? array( 'redirect' => 'no' ) : array()
			);
			# Mark current revisions
			$topmarktext = '';
			$user = $this->getUser();
			if ( $row->rev_id == $row->page_latest ) {
				$topmarktext .= '<span class="mw-uctop">' . $this->messages['uctop'] . '</span>';
				# Add rollback link
				if ( !$row->page_is_new && $page->quickUserCan( 'rollback', $user )
					&& $page->quickUserCan( 'edit', $user )
				) {
					// $this->preventClickjacking();
					$topmarktext .= ' ' . Linker::generateRollback( $rev, $this->getContext() );
				}
			}
			$histlink = Linker::linkKnown(
				$page,
				'修改历史',
				array(),
				array( 'action' => 'history' )
			);

			$lang = $this->getLanguage();
			$comment = $lang->getDirMark() . Linker::revComment( $rev, false, true );
			$date = $lang->userTimeAndDate( $row->rev_timestamp, $user );
                        
            # 日期
            $d = htmlspecialchars( $date );
			if ( $rev->isDeleted( Revision::DELETED_TEXT ) ) {
				$d = '<span class="history-deleted">' . $d . '</span>';
			}

            $userlink = '';

			if ( $rev->getParentId() === 0 ) {
				$nflag = '<span class="eidt-add">新增</span>';
			} else {
				$nflag = '<span class="eidt-change">修改</span>';
			}

			if ( $rev->isMinor() ) {
				$mflag = ChangesList::flag( 'minor' );
			} else {
				$mflag = '';
			}

			$del = Linker::getRevDeleteLink( $user, $rev, $page );
			if ( $del !== '' ) {
				$del .= ' ';
			}

			$diffHistLinks = $this->msg( 'parentheses' )
				->rawParams( $histlink )
				->escaped();
				
			$tagSummary = $row->rev_comment;
		}
		// Let extensions add data '.$tagSummary.'
		Hooks::run( 'ContributionsLineEnding', array( $this, &$ret, $row, &$classes ) );
			$li = '<li><div class="list-item-l">'.$nflag.'</div>
						<div class="list-item-r">
							<div class="item-r-text">
							   '.$d.$diffHistLinks.'内容：<a href="javascript:;">'.$link.'</a>
							</div>
						</div>
					</li>';
		// }
		return $li;
	}
	
	private function sandboxParse($wikiText) {
		global $wgTitle, $wgUser;
		$myParser = new Parser();
		$myParserOptions = ParserOptions::newFromUser($wgUser);
		$result = $myParser->parse($wikiText, $wgTitle, $myParserOptions);
		return $result->getText();
	}
        
	public function getQueryInfo(){}
	
	public function getIndexField(){}
}
?>