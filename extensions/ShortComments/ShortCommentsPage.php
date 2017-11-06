<?php

/**
 * Class for Comments methods that are not specific to one comments,
 * but specific to one comment-using page
 */
class ShortCommentsPage extends ContextSource {

	/**
	 * @var Integer: page ID (page.page_id) of this page.
	 */
	public $id = 0;

	/**
	 * @var Title: title object for this page
	 */
	public $title = null;

	/**
	 * Constructor
	 *
	 * @param $pageID: current page ID
	 */
	public function __construct ( $pageID, $context ) {
		$this->id = $pageID;
		$this->setContext( $context );
		$this->title = Title::newFromID( $pageID );
	}
	
	// 短评列表
	public function shortCommentsList(){
		$dbr = wfGetDB( DB_SLAVE );
		$css = array('tbb', 'jgl', 'zb', 'tbzb');
		$list = '<p class="dp-des" id="shortcommentlist">';
		$res = $dbr->select(
			'page_short_comment',
			'*',
			array( 'page_id' => $this->id ),
			__METHOD__,
			array('ORDER BY' => 'psc_id DESC','LIMIT'=>5)
		);
		if ( $res !== false ) {
			foreach ( $res as $row ) {
				//(<em>'.$row->like_count.'</em>)
				$rndKey = array_rand($css);
				$list .= '<span class="'.$css[$rndKey].'" data-id="'.$row->psc_id.'">'.$row->body.'</span>';
			}
		}
		$list .= '<span class="more">更多&gt;</span></p>';
		return $list;
	}
	
	// 短评form
	public function shortCommentsForm(){
		// $output = '<form action="" method="post" name="shortcommentForm">' . "\n";
		// $output .= '<input type="text" name="shortcomment" value="">' . "\n";
		// $output .= '<input type="button" value="' .
				// wfMessage( 'shortcomments-post' )->plain() . '" class="site-button" />';
		// $output .= '</form>' . "\n";
		$output = '<p class="tjpl"><input id="shortcomment" name="shortcomment" type="text" class="text" placeholder="最多10个字" value=""><input type="button" class="button" value="发表评论" id="shortcommentbtn"></p>';
		return $output;
	}
	
	// 展示点赞数
	function displayNum(){
		$dbr = wfGetDB( DB_SLAVE );
		$count = 0;
		$s = $dbr->selectRow(
			'page_addons',
			array( 'like_count' ),
			array( 'page_id' => $this->id ),
			__METHOD__
		);
		if ( $s !== false ) {
			$count = $s->like_count;
		}
		return $count;
	}

	/**
	 * Gets the total amount of comments on this page
	 *
	 * @return int
	 */
	function countTotal() {
		$dbr = wfGetDB( DB_SLAVE );
		$count = 0;
		$s = $dbr->selectRow(
			'Comments',
			array( 'COUNT(*) AS CommentCount' ),
			array( 'Comment_Page_ID' => $this->id ),
			__METHOD__
		);
		if ( $s !== false ) {
			$count = $s->CommentCount;
		}
		return $count;
	}

	/**
	 * Displays the form for adding new comments
	 *
	 * @return string HTML output
	 */
	function displayData() {
		$output = '<input type="hidden" name="pageId" value="' . $this->id . '" />' . "\n";
		// $output .= Html::hidden( 'token', $this->getUser()->getEditToken() );
		return $output;
	}

}