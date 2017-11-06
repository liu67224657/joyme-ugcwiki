<?php
if ( function_exists( 'wfLoadExtension' ) ) {

    wfLoadExtension( 'RecommendArea' );

    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['RecommendArea'] = __DIR__ . '/i18n';

    $wgExtensionMessagesFiles['RecommendAreaAliases'] = __DIR__ . '/RecommendArea.alias.php';

    // Register the CSS with ResourceLoader
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}