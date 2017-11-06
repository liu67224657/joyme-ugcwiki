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
        'name'          => 'JShare', 
        'namemsg'       => 'skinname-mediawikibootstrap', 
        'version'       => '1.1.0',
        'url'           => 'https://www.mediawiki.org/wiki/Skin:MediaWikiBootstrap',
        'author'        => '[https://mediawiki.org/wiki/User:nasirkhan Nasir Khan Saikat]',
        'descriptionmsg'=> 'mediawikibootstrap-desc',
        'license'       => 'GPL-2.0+',
);


$wgValidSkinNames['jshare']         = 'JShare';
$wgAutoloadClasses['SkinJShare']    = __DIR__ . '/JShare.skin.php';
$wgMessagesDirs['JShare']           = __DIR__ . '/i18n';

$wgResourceModules['skins.jshare'] = array(
		'styles' => array(
				'JShare/css/bootstrap.css'      => array( ),
				'JShare/css/font-awesome.min.css'   => array( ),
				'JShare/css/screen.css'             => array( ),
				'JShare/css/style.css'             => array( ),
				'JShare/css/print.css'              => array( 'media' => 'print' ),
				'JShare/css/joymedialog.css'              => array(),
				'JShare/css/joyme-style.css'             => array( ),
				'JShare/css/daoju.css'             => array( ),
        'JShare/css/wiki-nav.css'             => array( ),
        'JShare/css/comment.css'             => array( ),
		),
		'scripts' => array(
				'JShare/js/bootstrap.min.js',
				'JShare/js/fastclick.min.js',
				'JShare/js/mediawikibootstrap.js',
				'JShare/js/ugcwikiutil.js',
				'JShare/js/UserCenter.js',
				'JShare/js/mousewheel.min.js',
				'JShare/js/nav-script-tl.js',
				'JShare/js/bscroll.min.js',
				// 'JShare/js/scrollbar.min.js',
				'JShare/js/horizontalMove.js',
				'JShare/js/action.js',
				'JShare/js/comment.js'
				
		),
		'remoteBasePath'        => &$GLOBALS['wgStylePath'],
		'localBasePath'         => &$GLOBALS['wgStyleDirectory'],
		'position' => 'top',
);
 



// # Default options to customize skin
 $wgMediaWikiBootstrapSkinLoginLocation = 'footer';
 $wgMediaWikiBootstrapSkinAnonNavbar = false;
 $wgMediaWikiBootstrapSkinUseStandardLayout = false;
 $wgMediaWikiBootstrapSkinDisplaySidebarNavigation = false;
// # Show print/export in navbar by default
 $wgMediaWikiBootstrapSkinSidebarItemsInNavbar = array( 'coll-print_export' );
