<?php
/**
 * ShortComments extension - adds <SHORTCOMMENTS/> parserhook to allow commenting on pages
 *
 * @file
 * @ingroup Extensions
 * @author Islander <memcached@sina.cn>
 * @copyright Copyright © 2016- Islander
 * @link https://www.mediawiki.org/wiki/Extension:ShortComments Documentation
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
	'name' => 'ShortComments',
	'version' => '1.0',
	'author' => array( 'Islander' ),
	'descriptionmsg' => 'shortcomments-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:ShortComments'
);


// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.shortcomments.css'] = array(
	'styles' => 'ShortComments.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ShortComments',
	'position' => 'top' // available since r85616
);

$wgResourceModules['ext.shortcomments.js'] = array(
	'scripts' => 'ShortComments.js',
	'messages' => array(
		'shortcomments-once',
		'shortcomments-repeat'
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ShortComments'
);


// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['ShortComments'] = __DIR__ . '/i18n';
// magic word setup
$wgExtensionMessagesFiles['ShortCommentsMagic'] = __DIR__ . '/ShortComments.i18n.magic.php';
// $wgAutoloadClasses['ShortComments'] = __DIR__ . '/ShortComments.class.php';
$wgAutoloadClasses['ShortCommentsPage'] = __DIR__ . '/ShortCommentsPage.php';

// Hooked functions
$wgAutoloadClasses['ShortCommentsHooks'] = __DIR__ . '/ShortComments.hooks.php';
$wgHooks['ParserFirstCallInit'][] = 'ShortCommentsHooks::onParserFirstCallInit';

// API
$wgAutoloadClasses['ShortCommentsAPI'] = __DIR__ . '/ShortComments.api.php';$wgAutoloadClasses['ShortCommentsLikeAPI'] = __DIR__ . '/ShortCommentsLike.api.php';
$wgAPIModules['shortcomments'] = 'ShortCommentsAPI';
$wgAPIModules['shortcommentslike'] = 'ShortCommentsLikeAPI';
