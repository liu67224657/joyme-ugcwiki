<?php

/**
 * Class for Comments methods that are not specific to one comments,
 * but specific to one comment-using page
 */
class ClickLikePage extends ContextSource {

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
	function __construct ( $pageID, $context ) {
		$this->id = $pageID;
		$this->setContext( $context );
		$this->title = Title::newFromID( $pageID );
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