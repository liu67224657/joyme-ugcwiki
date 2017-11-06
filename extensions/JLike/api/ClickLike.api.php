<?php

/**
 *Description:着迷wiki文章点赞
 *author:Islander
 *date:16:28 2016/6/24
**/


class ClickLikeAPI extends ApiBase {

    public function execute() {
		$pageid = $this->getMain()->getVal( 'pageID' );
		$dbr = wfGetDB( DB_SLAVE );
		$pa_res = $dbr->selectRow(
			'page_addons',
			array( 'like_count' ),
			array( 'page_id' => $pageid ),
			__METHOD__
		);
		$like_count = $pa_res->like_count+1;
		$dbw = wfGetDB( DB_MASTER );
		if($pa_res){
			$dbw->update(
					'page_addons',
					array('like_count'=>$like_count),
					array('page_id' => $pageid),
					__METHOD__
				);
		}else{
			$dbw->insert(
				'page_addons',
				array(
					'page_id' => $pageid,
					'like_count' => $like_count
				),
				__METHOD__
			);
		}
		$dbw->commit();
		$result = $this->getResult();
        $result->addValue( $this->getModuleName(), 'ok', array('like_count'=>$like_count) );
		$title = Title::newFromID($pageid);
		$articletitle = $title->getDBkey();
        return true;
    }

    public function getAllowedParams() {
        return array(
            'pageID' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            )
        );
    }
}