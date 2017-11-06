<?php
/**
 * MediaWikiBootstrap Skin
 *
 * @file
 * @ingroup Skins
 * @author Nasir Khan Saikat http://nasirkhn.com
 */

if ( !defined( 'MEDIAWIKI' ) ) {
        die( 'This is an extension to the MediaWiki package and cannot be run standalone.' );
}

$wgExtensionCredits['skin'][] = array(
        'path'          => __FILE__,
        'name'          => 'MediaWikiBootstrap', 
        'namemsg'       => 'skinname-mediawikibootstrap', 
        'version'       => '1.1.0',
        'url'           => 'https://www.mediawiki.org/wiki/Skin:MediaWikiBootstrap',
        'author'        => '[https://mediawiki.org/wiki/User:nasirkhan Nasir Khan Saikat]',
        'descriptionmsg'=> 'mediawikibootstrap-desc',
        'license'       => 'GPL-2.0+',
);


$wgValidSkinNames['mediawikibootstrap']         = 'MediaWikiBootstrap';
$wgAutoloadClasses['SkinMediaWikiBootstrap']    = __DIR__ . '/MediaWikiBootstrap.skin.php';
$wgMessagesDirs['MediaWikiBootstrap']           = __DIR__ . '/i18n';

global $wgWikiname;

if($wgWikiname == 'home'){
	$wgResourceModules['skins.mediawikibootstrap'] = array(
			'styles' => array(
					'MediaWikiBootstrap/css/bootstrap.css'      => array( ),
					'MediaWikiBootstrap/css/font-awesome.min.css'   => array( ),
					'MediaWikiBootstrap/css/screen.css'             => array( ),
					'MediaWikiBootstrap/css/home.css'             => array( ),
					'MediaWikiBootstrap/css/print.css'              => array( 'media' => 'print' ),
					'MediaWikiBootstrap/css/bootstrap-datepicker.min.css'              => array( ),
					'MediaWikiBootstrap/css/daoju.css'              => array( ),
					'MediaWikiBootstrap/css/joymedialog.css'              => array( ),
			),
			'scripts' => array(
					'MediaWikiBootstrap/js/bootstrap.min.js',
					'MediaWikiBootstrap/js/mediawikibootstrap.js',
					'MediaWikiBootstrap/js/ugcwikiutil.js',
					'MediaWikiBootstrap/js/UserCenter.js',
					'MediaWikiBootstrap/js/bootstrap-datepicker.js',
					'MediaWikiBootstrap/js/mousewheel.min.js',
					// 'MediaWikiBootstrap/js/scrollbar.min.js',
					'MediaWikiBootstrap/js/horizontalMove.js',
					'MediaWikiBootstrap/js/action.js'

			),
			'remoteBasePath'        => &$GLOBALS['wgStylePath'],
			'localBasePath'         => &$GLOBALS['wgStyleDirectory'],
			'position' => 'top',
	);
}else{
	$wgResourceModules['skins.mediawikibootstrap'] = array(
			'styles' => array(
					'MediaWikiBootstrap/css/bootstrap.css'      => array( ),
					'MediaWikiBootstrap/css/font-awesome.min.css'   => array( ),
					'MediaWikiBootstrap/css/screen.css'             => array( ),
					'MediaWikiBootstrap/css/style.css'             => array( ),
					'MediaWikiBootstrap/css/print.css'              => array( 'media' => 'print' ),
					'MediaWikiBootstrap/css/daoju.css'              => array( ),
					'MediaWikiBootstrap/css/joymedialog.css'              => array( ),
					'MediaWikiBootstrap/css/joymerecentchanges.css'              => array( ),
			),
			'scripts' => array(
					'MediaWikiBootstrap/js/bootstrap.min.js',
					'MediaWikiBootstrap/js/mediawikibootstrap.js',
					'MediaWikiBootstrap/js/ugcwikiutil.js',
					'MediaWikiBootstrap/js/UserCenter.js',
					'MediaWikiBootstrap/js/mousewheel.min.js',
					// 'MediaWikiBootstrap/js/scrollbar.min.js',
					'MediaWikiBootstrap/js/horizontalMove.js',
					'MediaWikiBootstrap/js/action.js'
					
			),
			'remoteBasePath'        => &$GLOBALS['wgStylePath'],
			'localBasePath'         => &$GLOBALS['wgStyleDirectory'],
			'position' => 'top',
	);
}
 



// # Default options to customize skin
 $wgMediaWikiBootstrapSkinLoginLocation = 'footer';
 $wgMediaWikiBootstrapSkinAnonNavbar = false;
 $wgMediaWikiBootstrapSkinUseStandardLayout = false;
 $wgMediaWikiBootstrapSkinDisplaySidebarNavigation = false;
// # Show print/export in navbar by default
 $wgMediaWikiBootstrapSkinSidebarItemsInNavbar = array( 'coll-print_export' );
