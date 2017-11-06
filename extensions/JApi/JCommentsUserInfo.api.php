<?php

class JCommentsUserInfoAPI extends ApiBase {

    public function execute() {
		global $wgWikiname, $wgSiteGameTitle, $wgSiteId;
		$type = $this->getMain()->getVal( 'type' );
		$dbr = wfGetDB( DB_SLAVE );
		if( $type == 'uid' ){
			$uids = $this->getMain()->getVal( 'uids' );
			$res = $dbr->select(array( 'user_site_relation' ),
					'user_id',
					"status=1 AND site_id={$wgSiteId} AND user_id IN ({$uids})",
					__METHOD__
				);
			$info = array();
			foreach($res as $row){
				$info[] = $row->user_id;
			}
			$arr = array('userinfo'=>$info,'rs'=>0,'msg'=>'success');
		}else if( $type == 'name' ){
			$names = $this->getMain()->getVal( 'names' );
			$namesarr = explode(',', $names);
			foreach($namesarr as $k=>$v){
				$namesarr[$k] = ucfirst($v);
			}
			$res = $dbr->select(array( 'user' ),
					'user_id,user_name',
					"user_name IN ('".implode('\',\'', $namesarr)."')",
					__METHOD__
				);
			$info = array();
			foreach($res as $row){
				$info[] = $row->user_id;
			}
			$arr = array('userinfo'=>$info,'rs'=>0,'msg'=>'success');
		}else{
			$arr = array('rs'=>1,'msg'=>'error:'.$type);
		}
		$result = $this->getResult();
        $result->addValue( $this->getModuleName(), 'res', json_encode($arr) );
        return true;
    }

    public function getAllowedParams() {
        return array(
            'uids' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'names' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
			'type' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}