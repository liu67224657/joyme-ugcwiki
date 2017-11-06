<?php
if ( function_exists( 'wfLoadExtension' ) ) {
    wfLoadExtension( 'RecommendUsers' );
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['RecommendUsers'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['RecommendUsersAliases'] = __DIR__ . '/RecommendUsers.alias.php';
    // Register the CSS with ResourceLoader

    require_once( "$IP/extensions/RecommendUsers/RecommendUser_AjaxFunctions.php" );

    $wgResourceModules['ext.RecommendUser.css'] = array(
        'styles' => 'RecommendUser.css',
	    'localBasePath' => __DIR__,
	    'remoteExtPath' => 'RecommendUser',
	    'position' => 'top'
    );
    $wgResourceModules['ext.RecommendUser.js'] = array(
    		'scripts' => 'RecommendUser.js',
    		'localBasePath' => __DIR__ ,
    		'remoteExtPath' => 'RecommendUsers',
    		'position' => 'bottom',
    );
    $wgResourceModules['ext.RecommendUsers.not.logged'] = array(
        'styles' => array(
            'notlogged.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'RecommendUsers',
        'position' => 'top'
    );
    /* wfWarn(
        'Deprecated PHP entry point used for Renameuser extension. Please use wfLoadExtension instead, ' .
        'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
    ); */
    return true;
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}