<?php
/**
 *Description:Joyme wiki 评论
 *author:Islander
 *date:14:03 2016/7/22
**/
class JLikeHooks {
	
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		global $wgTitle, $wgRequest, $wgWikiname, $wgQiNiuBucket;
		$namespace = $wgTitle->mNamespace;
		$articleid = $wgTitle->mArticleID;
		$action = $wgRequest->getVal('action');
		if($namespace==0 && $articleid>1 && $action==null && $wgWikiname!= 'home'){
			$out->addModules( 'ext.jlike.js' );
		}
		return true;
	}
	
	public static function onSkinAfterContent(  &$data, Skin $skin  ) {
		global $wgTitle, $wgRequest, $wgWikiname, $wgQiNiuBucket;
		$namespace = $wgTitle->mNamespace;
		$articleid = $wgTitle->mArticleID;
		$action = $wgRequest->getVal('action');
		if($namespace==0 && $articleid>1 && $action==null && $wgWikiname!= 'home'){
			// $data .= '<div class="fn-clear"></div><div class="dianzan" id="let-dianzan"><p id="ClickLike" class="dz-icon"><span class="dz-num">0</span><b class="dz">点赞</b></p><div class="dz-detail" id="duanping"></div><div class="fn-clear"></div></div>';
		}
		return true;
	}
}