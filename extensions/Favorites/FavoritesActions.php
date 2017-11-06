<?php

class FavoriteAction {

	function __construct($action,$title,$article = null) {
		$user = User::newFromSession();
		
		if ($article) {
			$output = $article->getContext()->getOutput();
		} else {
			$output = false;
		}
		
		if ($action == 'favorite') {
			$result = $this->doFavorite($title, $user);
			$message = 'addedfavoritetext';
		} else {
			$result = $this->doUnfavorite($title, $user);
			$message = 'removedfavoritetext';
		}
		
		if ($result == true) {
			if ($output) {
				// don't do this if we are calling from the API
				$output->addWikiMsg( $message, $title->getPrefixedText() );
			}
			$user->invalidateCache();
			return true;
		} else {
			if ($output) {
				// don't do this if we are calling from the API
				$output->addWikiMsg( 'favoriteerrortext', $title->getPrefixedText() );
			}
			return false;
		}
		
		
	}
	
	function doFavorite( Title $title, User $user  ) {
		global $wgWikiname, $wgRequest,$wgServer;
		$success = false;
		wfProfileIn( __METHOD__ );
		$dbw = wfGetDB( DB_MASTER );
		$articleid = $title->getArticleID();
		$pa_data = $dbw->select('page_addons', 
			array('edit_count','pa_timestamp'),
			array('page_id'=>$articleid));
		if(!$pa_data){
			$editcount = $pa_data->edit_count;
			$touchedtime = $pa_data->pa_timestamp;
		}else{
			$touchedtime = $title->getTouched();
			$editcount = $dbw->selectRowCount('revision', '1', 
			array('rev_page'=>$articleid));
		}
		$wikins = $wgRequest->getVal('wikins');
		$dbw->insert( 'favoritelist',
				array(
						'fl_user' => $user->getId(),
						'fl_namespace' => $wikins,
						'fl_title' => $title->getDBkey(),
						'fl_wikikey' => $wgWikiname,
						'fl_pageid' => $articleid,
						'fl_notificationtimestamp' => null,
						'fl_editcount' => $editcount,
						'fl_touchedtime' => $touchedtime,
				), __METHOD__, 'IGNORE' );
		
		wfProfileOut( __METHOD__ );
		if ( $dbw->affectedRows() > 0 ) {
			$success = true;
		}
		// 钩子
		/*JoymeWikiUser::addActionLog(
			$user->getId(),
			6,
			'收藏了页面<a href="'.$title->getLocalURL().'" target="_blank">'.$title->getDBkey().'</a>'
		);*/
		JoymeWikiUser::adduseractivity(
			$user->getId(),
			'favirate_page',
			'收藏了页面 <a href="'.$wgServer.$title->getLocalURL().'" target="_blank">'.$title->getDBkey().'</a>'
		);
		JoymeWikiUser::pointsreport(22,$title->getFirstRevision()->getRawUser());
		return $success;
	}
	
	function doUnfavorite( Title $title, User $user  ) {
		global $wgWikiname, $wgRequest;
		$success = false;
		$wikikey = $wgRequest->getVal('wikikey');
		$wikins = $wgRequest->getVal('wikins');
		if($wikikey){
			$wgWikiname = $wikikey;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'favoritelist',
				array(
						'fl_user' => $user->getId(),
						'fl_namespace' => $wikins,
						'fl_title' => $title->getDBkey(),
						'fl_wikikey'=> $wgWikiname
				), __METHOD__
		);

		if ( $dbw->affectedRows() > 0) {
			$success = true;
			JoymeWikiUser::pointsreport(35,$title->getFirstRevision()->getRawUser());
		} 
		
		return $success;
	}
	

	
	
}



