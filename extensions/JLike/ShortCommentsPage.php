<?php

use Joyme\net\Curl;

class ShortCommentsPage{

	public $aid = 0; // 文章id

	public function __construct ( $id ) {
		$this->aid = $id;
	}
	
	// 短评列表
	public function shortCommentsList(){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'page_short_comment',
			'*',
			array( 'page_id' => $this->aid ),
			__METHOD__,
			array('ORDER BY' => 'like_count DESC','LIMIT'=>20)
		);
		$list = array();
		if ( $res !== false ) {
			foreach ( $res as $row ) {
				$list[] = $row;
			}
		}
		return $list;
	}
	
	// 添加短评
	public function addShortComment( $con, $uid ){
		$dbw = wfGetDB( DB_MASTER );
		$data = $dbw->selectRow(
			'page_short_comment',
			'*',
			array( 'page_id' => $this->aid, 'body'=>$con),
			__METHOD__
		);
		if($data){
			$this->addClickLike( $data->psc_id, $uid );
			$res = array('psc_id'=>$data->psc_id, 
				'body'=>$data->body,
				'like_count'=>$data->like_count+1);
		}else{
			$dbw->insert(
				'page_short_comment',
				array( 'page_id' => $this->aid,
					'like_count'=>0, 
					'body'=>$con),
				__METHOD__
			);
			$id = $dbw->insertId();
			$type = 1; // log纪录类型为添加
			$this->shortCommentLog($id, $uid, $type);
			$res = array('psc_id'=>$id, 
				'body'=>$con,
				'like_count'=>0);
		}
		return $res;
	}
	
	public function addClickLike( $pscid, $uid ){
		$dbw = wfGetDB( DB_MASTER );
		$sql = 'UPDATE page_short_comment SET like_count = like_count +1 WHERE psc_id = '.$pscid;
		$dbw->query($sql);
		$type = 2; // log纪录类型为添加
		$this->shortCommentLog($pscid, $uid, $type);
		return array('psc_id'=>$pscid);
	}

	public function checkWord($con){
		global $wgEnv;
		$url = 'http://servapi.joyme.' . $wgEnv . '/servapi/verify/word';
		$curl = new Curl();
		$res = $curl->Post( $url, array('word'=>$con) );
		$res = json_decode($res, true);
		return $res;
	}


	//获取短评数
	public function getPageShortCommentNum(){

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRowCount(
			'page_short_comment',
			'*',
			array( 'page_id' => $this->aid )
		);
		return $res;
	}

	//获取短评的点赞数总和
	public function getShortCommentLikeCount(){

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'page_short_comment',
			'like_count',
			array( 'page_id' => $this->aid )
		);
		$count = 0;
		if($res->numRows()){
			foreach($res as $k=>$v){
				$count+=$v->like_count;
			}
		}
		return $count;
	}
	
	public function shortCommentLog($pscid, $uid, $type ){
		global $wgRequest;
		$ip = $wgRequest->getIP();
		$user = $uid == 0 ? $ip : $uid;
		$dbw = wfGetDB( DB_MASTER );
		// type: 1 添加, 2 点赞
		$dbw->insert( 'page_short_comment_log', 
			array( 'page_id' => $this->aid,
				'psc_id' => $pscid,
				'user' => $user,
				'type' => $type,
				'create_time' => time()
			) );
		$id = $dbw->insertId();
		return $id;
	}
	
}