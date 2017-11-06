<?php
/**
 * ClickLike extension - adds <CLICKLIKE/> parserhook to allow commenting on pages
 *
 * @file
 * @ingroup Extensions
 * @author Islander <memcached@sina.cn>
 * @copyright Copyright © 2016- Islander
 * @link https://www.mediawiki.org/wiki/Extension:ClickLike Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'ClickLike',
	'version' => '1.0',
	'author' => array( 'Islander' ),
	'descriptionmsg' => 'clicklike-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:ClickLike'
);

// ResourceLoader support for MediaWiki 1.17+
// $wgResourceModules['ext.comments.css'] = array(
	// 'styles' => 'Comments.css',
	// 'localBasePath' => __DIR__,
	// 'remoteExtPath' => 'Comments',
	// 'position' => 'top' // available since r85616
// );

// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.clicklike.css'] = array(
	'styles' => 'ClickLike.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ClickLike',
	'position' => 'top' // available since r85616
);

$wgResourceModules['ext.clicklike.js'] = array(
	'scripts' => 'ClickLike.js',
	'messages' => array(
		'clicklike-once'
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ClickLike'
);


// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['ClickLike'] = __DIR__ . '/i18n';
// magic word setup
$wgExtensionMessagesFiles['ClickLikeMagic'] = __DIR__ . '/ClickLike.i18n.magic.php';
// $wgAutoloadClasses['ClickLike'] = __DIR__ . '/ClickLike.class.php';
$wgAutoloadClasses['ClickLikePage'] = __DIR__ . '/ClickLikePage.php';

// Hooked functions
$wgAutoloadClasses['ClickLikeHooks'] = __DIR__ . '/ClickLike.hooks.php';
$wgHooks['ParserFirstCallInit'][] = 'ClickLikeHooks::onParserFirstCallInit';

// API
$wgAutoloadClasses['ClickLikeAPI'] = __DIR__ . '/ClickLike.api.php';
$wgAPIModules['clicklike'] = 'ClickLikeAPI';
