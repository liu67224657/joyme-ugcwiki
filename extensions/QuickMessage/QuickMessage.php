<?php
if ( function_exists( 'wfLoadExtension' ) ) {

    wfLoadExtension( 'QuickMessage' );

    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['QuickMessage'] = __DIR__ . '/i18n';

    require_once( "$IP/extensions/QuickMessage/QuickUserMessage_AjaxFunctions.php" );

    $wgExtensionMessagesFiles['QuickMessageAliases'] = __DIR__ . '/QuickMessage.alias.php';

    // Register the CSS with ResourceLoader
    $wgResourceModules['ext.QuickMessage.js'] = array(
        'scripts' => array(
            'style/jquery.form.js',
            'style/jquery.validate.js',
            'style/quickmessage.js'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'QuickMessage',
        'position' => 'top'
    );

    $wgResourceModules['ext.QuickMessage.css'] = array(
        'styles' => array(
            'style/quickmessage.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'QuickMessage',
        'position' => 'top'
    );
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}