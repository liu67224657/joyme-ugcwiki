<?php
if ( function_exists( 'wfLoadExtension' ) ) {
    wfLoadExtension( 'RecommendWiki' );
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['RecommendWiki'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['RecommendWikiAliases'] = __DIR__ . '/RecommendWiki.alias.php';
    // Register the CSS with ResourceLoader
    $wgResourceModules['ext.RecommendWiki.css'] = array(
        'styles' => 'RecommendWiki.css',
	    'localBasePath' => __DIR__,
	    'remoteExtPath' => 'RecommendWiki',
	    'position' => 'top'
    );
    $wgResourceModules['ext.RecommendWiki.js'] = array(
        'scripts' => 'RecommendWiki.js',
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'RecommendWiki'
    );
    $wgResourceModules['ext.RecommendWiki.not.logged'] = array(
        'styles' => array(
            'notlogged.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'RecommendWiki',
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