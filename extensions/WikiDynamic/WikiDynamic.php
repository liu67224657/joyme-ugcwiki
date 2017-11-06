<?php
if ( function_exists( 'wfLoadExtension' ) ) {

    wfLoadExtension( 'WikiDynamic' );

    require_once( "$IP/extensions/WikiDynamic/WikiDynamic_Ajaxfonctions.php" );

    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['WikiDynamic'] = __DIR__ . '/i18n';

    $wgExtensionMessagesFiles['WikiDynamicAliases'] = __DIR__ . '/WikiDynamic.alias.php';

    // Register the CSS with ResourceLoader
    $wgResourceModules['ext.WikiDynamic.css'] = array(
        'styles' => array(
            'style/wikidynamic.css',
            'style/dty.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'WikiDynamic',
        'position' => 'top'
    );
    $wgResourceModules['ext.WikiDynamic.js'] = array(
        'scripts' => array(
            'style/wikidynamic.js'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'WikiDynamic',
        'position' => 'top'
    );
    return true;
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}