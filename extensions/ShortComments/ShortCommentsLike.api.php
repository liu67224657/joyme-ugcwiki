<?php

/**
 *Description:着迷wiki文章短评,点赞
 *author:Islander
 *date:14:21 2016/6/23
**/


class ShortCommentsLikeAPI extends ApiBase {

    public function execute() {
		$pageid = $this->getMain()->getVal( 'pageID' );
		$pscid = $this->getMain()->getVal( 'pscID' );
		$dbr = wfGetDB( DB_SLAVE );
		$pa_row = $dbr->selectRow(
			'page_addons',
			array('like_count', 'short_comment_count'),
			array( 'page_id' => $pageid )
		);
		$psc_row = $dbr->selectRow(
			'page_short_comment',
			array('like_count'),
			array( 'psc_id' => $pscid )
		);
		if(!$pa_row || !$psc_row){
			return true;
		}
		$dbw = wfGetDB( DB_MASTER );
		$short_comment_count = $pa_row->short_comment_count+1;
		$pa_res = $dbw->update(
				'page_addons',
				array('short_comment_count' => $short_comment_count),
				array('page_id' => $pageid),
				__METHOD__
			);
		
		$like_count = $psc_row->like_count+1;
		$psc_res = $dbw->update(
				'page_short_comment',
				array('like_count' => $like_count),
				array('psc_id' => $pscid),
				__METHOD__
			);
		$dbw->commit();
		$result = $this->getResult();
        $result->addValue( $this->getModuleName(), 'ok', array('like_count'=>$like_count) );
        return true;
    }

    public function getAllowedParams() {
        return array(
            'pageID' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'pscID' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'text' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}