<?php
/**
 * JContribution Extensions
 * @author Islander
 * @date 14:59 2016/6/30
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'JContribution',
	'author' => 'Islander',
	'url' => 'https://www.mediawiki.org/wiki/Extension:JContribution',
	'descriptionmsg' => 'JContribution-desc',
	'version' => '0.0.0',
);

$wgResourceModules['ext.jcontribution.css'] = array(
	'styles' => 'edit-select.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JContribution',
	'position' => 'top' // available since r85616
);

$wgResourceModules['ext.jcontribution.js'] = array(
	'scripts' => 'JContribution.js',
	'messages' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JContribution'
);
// edit-select.css
$wgAutoloadClasses['SpecialJContribution'] = __DIR__ . '/SpecialJContribution.php';
$wgMessagesDirs['JContribution'] = __DIR__ . "/i18n";
$wgExtensionMessagesFiles['JContributionAlias'] = __DIR__ . '/JContribution.alias.php';
$wgSpecialPages['JContribution'] = 'SpecialJContribution';

// ajax
// require_once( "JContribution.ajax.php" );

// API ContribList.api.php
$wgAutoloadClasses['ContribListAPI'] = __DIR__ . '/api/ContribList.api.php';
$wgAPIModules['contriblist'] = 'ContribListAPI';
