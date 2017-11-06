<?php
if ( function_exists( 'wfLoadExtension' ) ) {

    wfLoadExtension( 'CreateWiki' );

    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['CreateWiki'] = __DIR__ . '/i18n';

    $wgExtensionMessagesFiles['CreateWikiAliases'] = __DIR__ . '/CreateWiki.alias.php';

    require_once( "$IP/extensions/CreateWiki/CreateWiki_Ajaxfunctions.php" );

    // Register the CSS with ResourceLoader
    $wgResourceModules['ext.CreateWiki.js'] = array(
        'scripts' => array(
            'style/createwiki.js',
            'style/jquery.form.js',
            'style/jquery.validate.js'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'CreateWiki',
        'position' => 'top'
    );

    $wgResourceModules['ext.CreateWiki'] = array(
        'styles' => array(
            'style/createwiki.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'CreateWiki',
        'position' => 'top'
    );

    $wgResourceModules['ext.CreateWiki.not.logged'] = array(
        'styles' => array(
            'style/notlogged.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'CreateWiki',
        'position' => 'top'
    );
    return true;
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}