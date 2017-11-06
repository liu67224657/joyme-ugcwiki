<?php

/**
 * seo info
 */
class SiteInfo {
	

	/**
	 * $wgSiteGameTitle = "六龙争霸3Dwiki";
	 * $wgSitename = "六龙争霸3D_六龙争霸3D官网合作站_玩法攻略_下载_着迷wiki";
	 * $wgMetaNamespace = "六龙争霸3D_六龙争霸3D官网合作站_玩法攻略_下载_着迷wiki";
	 * $wgSiteSEOKeywords = "六龙争霸3D,六龙争霸3D官网,六龙争霸3D攻略,下载";
	 * $wgSiteSEODescription ="着迷六龙争霸3Dwiki，是官方合作站点，更多游戏内容尽在着迷wiki。";

	 */
	
	public static function load() {
		global $wgThread,$wgMobileIndexStatus,$wgIsUgcWiki,$wgUserEditStatus,$wgSiteGameTitle,$wgSitename,$wgMetaNamespace,$wgSiteSEOKeywords,$wgSiteSEODescription,$wgDefaultSkin;
		

		$wgMemc = wfGetMainCache();
		
		$key = wfMemcKey( 'site', 'info', 1 );
		$data = $wgMemc->get( $key );
		
		// Try cache
		if ( $data ) {
			wfDebug( "Loading siteinfo from cache\n" );
			$siteinfo = json_decode($data);
		} else {
			try {
				$dbr = wfGetDB( DB_MASTER );
				
				$siteinfo = $dbr->selectRow( 'site_info', array(
					'sid',
					'site_name',
					'site_title',
					'site_seokeywords',
					'site_seodescription',
					'wiki_type',
					'useredit_status',
					'thread_status',
					'mindex_status',
					'skin_style',
				), false, __METHOD__ );
				$wgMemc->set( $key, json_encode($siteinfo) );
			} catch (Exception $e) {
				header( $_SERVER['SERVER_PROTOCOL'] . ' 500 MediaWiki configuration Error', true, 500 );
				echo 'This site does not exist. <a href="http://wiki.joyme.com/344604.shtml">create this wiki</a>';
				die( 1 );
			}
		}

		$wgSiteGameTitle = $siteinfo->site_name;
		$wgSitename = $siteinfo->site_title;
		$wgMetaNamespace = str_replace( ' ', '_', $wgSitename );
		$wgSiteSEOKeywords = $siteinfo->site_seokeywords;
		$wgSiteSEODescription = $siteinfo->site_seodescription;
		$wgIsUgcWiki = $siteinfo->wiki_type == 1?true:false;
		$wgUserEditStatus = $siteinfo->useredit_status == 1?true:false;
		$wgThread = $siteinfo->thread_status == 1?true:false;
		$wgMobileIndexStatus = $siteinfo->mindex_status == 1?true:false;
		$wgDefaultSkin = empty($siteinfo->skin_style)?'mediawikibootstrap':$siteinfo->skin_style;
		
	}


	/**
	 * clearCache site_info
	 */
	public static function clearCache() {
		global $wgUser, $wgMemc;
		
		// Kill site_info cache
		$wgMemc->delete( wfMemcKey( 'site', 'info', 1 ) );
	}
}
