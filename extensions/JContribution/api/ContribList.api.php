<?php

class ContribListAPI extends ApiBase {
	public $year = 0;
	public $month = 0;
	public $wikikey = '';
	public $actype = '';
	public $userid = 0;
	public $pageno = 1;
	private $pagesize = 25;
	private $username = '';
	private $user;
	
    public function execute() {
		$params = $this->extractRequestParams();
		extract($params);
		$this->pageno = $pageno ? $pageno : 1;
		$this->year = $year ? $year : 0;
		$this->month = $month ? $month : 0;
		$this->actype = $actype ? $actype : '';
		$this->wikikey = $wikikey;
		$this->userid = $userid;
		$skip = ($this->pageno-1)*$this->pagesize;

		$this->user = User::newFromId($userid);
		if($this->user->whoIs($userid) == false){
			$res = array('msg'=>'用户不存在');
			$this->getResult()->addValue( null, $this->getModuleName(), $res );
			return true;
		}
		$this->username = $this->user->getName();
		// 获取列表
		$list = $this->getList();
		$res = array('li'=>$list);
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }
	
	private function getList(){
		global $wgWikiname;
		$skip = ($this->pageno-1)*$this->pagesize;
		$limit = $this->pagesize;
		$dbname = $this->wikikey.'wiki';
		
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
			$cond .= ' AND rev_timestamp > '.$this->year.'0000000000';
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
		$total = $res->total;
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
		
		$html = '';
		foreach ($revdata as $key => $row) {
			$row->user_name = $this->username;
			$row->ts_tags = null;
			$tr = $this->formatRow($row);
			$html .= str_replace("/$wgWikiname/", "/$this->wikikey/", $tr);
		}
		return $html;
	}
	
	public function formatRow( $row ) {

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
				array( 'class' => 'mw-contributions-title' ),
				$page->isRedirect() ? array( 'redirect' => 'no' ) : array()
			);
			$histlink = Linker::linkKnown(
				$page,
				'修改历史',
				array(),
				array( 'action' => 'history' )
			);

			$lang = $this->getLanguage();
			$comment = $lang->getDirMark() . Linker::revComment( $rev, false, true );
			$date = $lang->userTimeAndDate( $row->rev_timestamp, $this->user );
                        
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

			$diffHistLinks = $this->msg( 'parentheses' )
				->rawParams( $histlink )
				->escaped();
			$tagSummary = htmlspecialchars($row->rev_comment);
		}
		// '.$tagSummary.'
		$li = '<li><div class="list-item-l">'.$nflag.'</div>
					<div class="list-item-r">
						<div class="item-r-text">
						   '.$d.'（'.$diffHistLinks.'）内容：<a href="javascript:;">'.$link.'</a>
						</div>
					</div>
				</li>';
		return $li;
	}

    public function getAllowedParams() {
        return array(
            'userid' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
            'wikikey' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'year' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'month' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'actype' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'pageno' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            )
        );
    }
}