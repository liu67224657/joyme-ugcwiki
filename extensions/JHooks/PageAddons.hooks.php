<?php
/**
 *Description:着迷wiki文章附加表钩子（文章编辑后执行，附加表添加page_id）
 *author:Islander
 *date:16:28 2016/6/24
**/
use Joyme\net\curl;

class PageAddonsHooks {
	/**
	 * Registers the <clicklike> tag with the Parser.
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function pageContentSaveComplete( &$article, &$user ) {
		global $wgWikiname, $wgEnv, $wgPhpServer;
		$articleID = $article->mTitle->mArticleID;
		$namespace = $article->mTitle->mNamespace;
		$title = $article->mTitle->mDbkeyform;
		$userID = $user->mId;
		$userName = $user->mName;
		$dbw = wfGetDB( DB_MASTER );
		$conds = array( 'page_id' => $articleID );
		$pageAddons = $dbw->selectRow( 'page_addons', array('page_id','edit_count'), $conds , __METHOD__ ,array('LIMIT'  =>1));
		if(!empty($pageAddons)){
			$dbw->update(
				'page_addons',
				array(
					'last_edit_user'=>$userName,
					'edit_count'=>$pageAddons->edit_count+1
				),
				array(
					'page_id'=>$pageAddons->page_id
				)
			);
			$dbw->update(
				'favoritelist',
				array(
					'fl_editcount'=>$pageAddons->edit_count+1,
					'fl_touchedtime'=>date('Y-m-d H:i:s', time())
				),
				array(
					'fl_pageid'=>$pageAddons->page_id,
					'fl_wikikey'=>$wgWikiname
				)
			);
		}else{
			// $touchedtime = $article->mTitle->getTouched();
			$editcount = $dbw->selectRowCount('revision', '1', 
				array('rev_page'=>$articleID));
			$dbw->insert(
				'page_addons',
				array(
					'page_id'=>$articleID,
					'like_count'=>0,
					'short_comment_count'=>0,
					'edit_count'=>$editcount,
					'last_edit_user'=>$userName
				)
			);
			$dbw->update(
				'favoritelist',
				array(
					'fl_editcount'=>1,
					'fl_touchedtime'=>date('Y-m-d H:i:s', time())
				),
				array(
					'fl_pageid'=>$articleID,
					'fl_wikikey'=>$wgWikiname
				)
			);
			if($namespace == 0){
				$url = 'http://webcache.joyme.'.$wgEnv .'/wiki/keyword/report.do';
				$wikiid = md5($wgWikiname);
				$wordurl = $wgPhpServer.'/'.$wgWikiname.'/'.$title;
				$params = array('wikiid'=>$wikiid, 'keyword'=>urlencode($title), 'url'=>urlencode($wordurl));
				$curl = new Curl();
				$content = $curl->Post($url, $params);
			}
		}
		$dbw->commit();
		return true;
	}

	public static function updatePageAddonsId( $article_name ){

		if( empty($article_name)){
			return false;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'recentchanges',
			array(
				'rc_cur_id'
			),
			array(
				'rc_title'=>$article_name
			),
			__METHOD__,
			array(
				'ORDER BY' =>'rc_timestamp DESC',
				'LIMIT'=>2
			)
		);

		$old_id = false;
		if($res ){
			$array = array();
			foreach($res as $k=>$v){
				$array[] = $v->rc_cur_id;
			}
			if(isset($array[1])){
				$old_id = $array[1];
			}else{
				$old_id = $array[0];
			}
		}
		$new_id = $dbr->selectRow(
			'page',
			'page_id',
			array(
				'page_title'=>$article_name
			)
		);
		if( $old_id && $new_id){
			$dbr->update(
				'page_addons',
				array(
					'page_id'=>$new_id->page_id
				),
				array(
					'page_id'=>$old_id
				)
			);
		}
		return true;
	}
	
	public static function getZanSum( $aid ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow( 'page_addons', '*', array('page_id'=>$aid) , __METHOD__ ,array('LIMIT'  =>1));
		if($res){
			$zansum = $res->like_count;
		}else{
			$zansum = 0;
		}
		return $zansum;
	}
}