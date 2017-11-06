<?php
/**
 *Description:Joyme wiki 评论
 *author:Islander
 *date:14:03 2016/7/22
**/
use Joyme\qiniu\Qiniu_Utils;
class JCommentsHooks {
	
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ){
		global $wgTitle, $wgRequest, $wgWikiname, $wgQiNiuBucket, $wgResourceBasePath, $wgEnv;
		$namespace = $wgTitle->mNamespace;
		$articleid = $wgTitle->mArticleID;
		$action = $wgRequest->getVal('action');
		if($namespace==0 && $articleid>1 && $action==null && $wgWikiname!= 'home'){
			$jpa = new JoymePageAddons();
			$pageaddons = $jpa->getPageAddons($articleid);
			$java_pageid = empty($pageaddons->java_page_id)?0:intval($pageaddons->java_page_id);
			$out = $skin->getOutPut();
			$out->addModuleStyles( 'ext.jcomments.css' );
			$out->addModules( 'ext.jcomments.js' );
			$out->addScript('<script type="text/javascript">var java_page_id = '.$java_pageid.';</script>');
			$out->addScript('<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/js/plupload.full.min.js"></script>');
			$out->addScript('<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/js/qiniu.js"></script>');
		}
	}
	
	public static function onSkinAfterContent(  &$data, Skin $skin  ) {
		global $wgTitle, $wgRequest, $wgWikiname, $wgQiNiuBucket, $wgResourceBasePath, $wgEnv,$wgExtensionAssetsPath;
		$namespace = $wgTitle->mNamespace;
		$articleid = $wgTitle->mArticleID;
		$action = $wgRequest->getVal('action');
		if($namespace==0 && $articleid>1 && $action==null && $wgWikiname!= 'home'){
			// $out = $skin->getOutPut();
			// $out->addModuleStyles( 'ext.jcomments.css' );
			// $out->addModules( 'ext.jcomments.js' );
			// var_dump($skin->getOutPut());exit;
			// 获取上传token
			$uptoken = Qiniu_Utils::Qiniu_UploadToken($wgQiNiuBucket);
			$data .= '<input type="hidden" name="uptoken" id="uptoken" value="'. $uptoken .'">';
			$data .= '<div class="section-pinglun" id="JCbox">
				<div class="pl-fa-mc" style="display:none;">
					<div id="container-scroller">
						<span class="scroller-close-btn"></span>
						<div class="image-scroller">
							<img src="" alt="" class="feature-image"/>
							<div class="preview">
								<img src="" class="preview-url" alt="" height="180" width="135" />
							</div>
						</div>
					</div>
				</div>
				<div class="web-pl-tc">
					<div class="web-pl-con">
					 <div class="web-pl-img">
						<img src="" title="弹出图片title"/>
					<span class="close-btn-pl-m"></span>
					</div>
					</div>
				</div>
                <div class="fbpl" id="let-pinglun">
                    <div class="fbpl-title"><h2>发表评论</h2><span>参与度 &nbsp;<em class="commentSum">0</em></span>
                    <div class="fn-clear"></div>
                    </div>
                    <div class="text-area"><textarea id="jcomment"></textarea></div>
                    <div class="fbpl-detail">
                        <p class="dxzs" id="plnum"><span>0</span>/200</p>
                        <div class="bq-tp">
                            <div class="talk-btn">
								<!--表情-->
								<div class="talk-btn-face">
								</div>
							</div>
                            <div class="tupian">
                                <p class="tp-choose"><a class="choose-img"><img src="'.$wgResourceBasePath.'/extensions/JComments/images/wiki-nry_25.jpg"><span>图片</span></a></p>
                                <div class="tp-details">
                                    <p class="tp-num"><img src="'.$wgResourceBasePath.'/extensions/JComments/images/wiki-nry_33.jpg"><span>图片(0/1)</span></p>
                                    <span class="sanjiao"></span>
                                    <!--<div class="tp-left"><img src="'.$wgResourceBasePath.'/extensions/JComments/images/wiki-nry_37.jpg"><span></span></div>-->
                                    <div class="tp-right" id="szlistBtn"><img id="upImg" src="'.$wgResourceBasePath.'/extensions/JComments/images/wik-nry-tuguize.jpg?v=0.1"></div>
                                </div>
                            </div>
                            <div class="fabu"><input type="button" value="发布"></div>
                        </div>
                    </div>
                    <div class="fn-clear"></div>
                </div>
                    </div>
	<div class="m-sixin">
	<div class="sixin-popup"></div>
		<ul class="sx-list">
			<li><a class="userfollowsx" href="javascript:;" target="_blank">私信</a></li>
			<li class="get-status"><a class="userfollowstatus" href="javascript:;"  data-uid="">正在获取关注状态</a></li>
			<li><a class="userfollowcancel" href="javascript:;">取消</a></li>
		</ul>
	</div>
';
			// $out->addScript('<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/js/plupload.full.min.js"></script>');
			// $out->addScript('<script type="text/javascript" src="http://static.joyme.'.$wgEnv.'/js/qiniu.js"></script>');
		}
		// $skin->process();
		return true;
	}
}