<?php

/**
 *Description:着迷wiki文章短评
 *author:Islander
 *date:14:21 2016/6/23
**/


class ShortCommentsAPI extends ApiBase {

    public function execute() {
		$pageid = $this->getMain()->getVal( 'pageID' );
		$body = $this->getMain()->getVal( 'text' );
		$dbr = wfGetDB( DB_SLAVE );
		$psc_row = $dbr->selectRow(
			'page_short_comment',
			array( 'psc_id' ),
			array( 'page_id' => $pageid, 'body' => $body )
		);
		if($psc_row && $psc_row->psc_id){
			$result = $this->getResult();
			$result->addValue( $this->getModuleName(), 'ok', array('msg'=>wfMessage( 'shortcomments-repeat' )->plain()) );
			return true;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
				'page_short_comment',
				array(
					'page_id' => $pageid,
					'body' => $body
				),
				__METHOD__
			);
		$id = $dbw->insertId();
		$dbw->commit();
		$result = $this->getResult();
        $result->addValue( $this->getModuleName(), 'ok', array('id'=>$id) );
        return true;
    }

    public function getAllowedParams() {
        return array(
            'pageID' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
			'text' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}