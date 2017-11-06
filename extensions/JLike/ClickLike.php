<?php

class ClickLike{

	public $aid = 0; // 文章id

	public function __construct ( $aid ) {
		$this->aid = $aid;
	}
	
	public function checkIsClickLike( $uid ){
		global $wgRequest;
		$ip = $wgRequest->getIP();
		$dbr = wfGetDB( DB_SLAVE );
		$user = $uid == 0 ? $ip : $uid;
		$res = $dbr->selectRow(
			'page_clicklike',
			'pcl_id',
			array( 'user'=>$user, 'page_id' => $this->aid ),
			__METHOD__
		);
		if($res && $res->pcl_id){
			return true;
		}else{
			return false;
		}
	}
	
	public function clickLikeLog( $uid ){
		global $wgRequest;
		$ip = $wgRequest->getIP();
		$user = $uid == 0 ? $ip : $uid;
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( 'page_clicklike', 
			array( 'page_id' => $this->aid,
				'user' => $user,
				'create_time' => time()
			) );
		$id = $dbw->insertId();
		return $id;
	}

}