<?php

class FavoriteListAPI extends ApiBase {

    public function execute() {
		$list = '';
		$pagesize = 20;
		$params = $this->extractRequestParams();
		$pageno = $params['pageno'] ? $params['pageno'] : 1;
		$wikikey = $params['wikikey'] ? $params['wikikey'] : '';
		$uid = $params['uid'] ? $params['uid'] : '';
		$skip = ($pageno-1)*$pagesize;
		
		$user = User::newFromId($uid);
		if($user->whoIs($uid) == false){
			$res = array('msg'=>'用户不存在');
			$this->getResult()->addValue( null, $this->getModuleName(), $res );
			return true;
		}
		// 查询总数
		$dbr = wfGetDB ( DB_SLAVE );
		$conds = array('fl_user' => $uid);
		if(!empty($wikikey)){
			$conds['fl_wikikey'] = $wikikey;
		}
		$total = $dbr->selectRowCount( 'favoritelist', '1', $conds);
		
		if($total < 1){
			$res = array('msg'=>'没有更多数据了');
			$this->getResult()->addValue( null, $this->getModuleName(), $res );
			return true;
		}
		$data = $dbr->select('favoritelist', '*',
			$conds , __METHOD__ ,
			array('ORDER BY'=>'fl_touchedtime desc', 
					'LIMIT'=>$pagesize, 'OFFSET'=>$skip));
		$siteinfo = $this->getSiteInfo($uid);
		foreach($data as $row){
			$unfavoritlink = '<span style="cursor:pointer" class="unfavorite" data-wikikey="'.$row->fl_wikikey .'" data-title="'.$row->fl_title .'">取消收藏</span>';
			$link = '<a href="'.'/'.$row->fl_wikikey . '/' . $row->fl_title .'">'.$row->fl_title .'</a>';
			$time = date('Y.m.d', strtotime($row->fl_touchedtime));
			$list .= '<dd>'.$link.' '.$unfavoritlink.' <span>'.$siteinfo[$row->fl_wikikey].'</span><span>'.$row->fl_editcount.'</span><span>'.$time.'</span></dd>';
		}
		$res = array('li'=>$list);
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }
	
	public function getSiteInfo($uid){
		$dbr = wfGetDB ( DB_SLAVE );
		$sql = 'SELECT DISTINCT fl_wikikey from favoritelist WHERE fl_user = '.$uid;
		$res = $dbr->query($sql);
		$siteinfo = $sitekeys = array();
		foreach($res as $row){
			$sitekeys[] = $row->fl_wikikey;
		}
		$res = $dbr->select('joyme_sites', array('site_key', 'site_name'), 'site_key IN (\''.implode('\',\'', $sitekeys).'\')');
		foreach($res as $val){
			$siteinfo[$val->site_key] = $val->site_name;
		}
		return $siteinfo;
	}

    public function getAllowedParams() {
        return array(
            'uid' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'integer'
            ),
            'wikikey' => array(
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