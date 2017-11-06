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
        'name'          => 'MediaWikiBootstrap1', 
        'namemsg'       => 'skinname-mediawikibootstrap', 
        'version'       => '1.1.0',
        'url'           => 'https://www.mediawiki.org/wiki/Skin:MediaWikiBootstrap',
        'author'        => '[https://mediawiki.org/wiki/User:nasirkhan Nasir Khan Saikat]',
        'descriptionmsg'=> 'mediawikibootstrap-desc',
        'license'       => 'GPL-2.0+',
);


$wgValidSkinNames['mediawikibootstrap1']         = 'MediaWikiBootstrap1';
$wgAutoloadClasses['SkinMediaWikiBootstrap1']    = __DIR__ . '/MediaWikiBootstrap1.skin.php';
$wgMessagesDirs['MediaWikiBootstrap1']           = __DIR__ . '/i18n';

$wgResourceModules['skins.mediawikibootstrap1'] = array(
		'styles' => array(
				'MediaWikiBootstrap1/css/bootstrap.css'      => array( ),
				'MediaWikiBootstrap1/css/font-awesome.min.css'   => array( ),
				'MediaWikiBootstrap1/css/screen.css'             => array( ),
				'MediaWikiBootstrap1/css/style.css'             => array( ),
				'MediaWikiBootstrap1/css/print.css'              => array( 'media' => 'print' ),
				'MediaWikiBootstrap1/css/joymedialog.css'              => array(),
				'MediaWikiBootstrap1/css/joyme-style.css'             => array( ),
				'MediaWikiBootstrap1/css/daoju.css'             => array( ),
                'MediaWikiBootstrap1/css/wiki-nav.css'             => array( ),
                'MediaWikiBootstrap1/css/joymerecentchanges.css'             => array( ),
		),
		'scripts' => array(
				'MediaWikiBootstrap1/js/bootstrap.min.js',
				'MediaWikiBootstrap1/js/fastclick.min.js',
				'MediaWikiBootstrap1/js/mediawikibootstrap.js',
				'MediaWikiBootstrap1/js/ugcwikiutil.js',
				'MediaWikiBootstrap1/js/UserCenter.js',
				'MediaWikiBootstrap1/js/mousewheel.min.js',
				'MediaWikiBootstrap1/js/nav-script-tl.js',
				// 'MediaWikiBootstrap1/js/scrollbar.min.js',
				'MediaWikiBootstrap1/js/horizontalMove.js',
				'MediaWikiBootstrap1/js/action.js'
				
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
