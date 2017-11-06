<?php
if ( function_exists( 'wfLoadExtension' ) ) {
    wfLoadExtension( 'Discussion' );
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['Discussion'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['DiscussionAliases'] = __DIR__ . '/Discussion.alias.php';
    /* wfWarn(
        'Deprecated PHP entry point used for Renameuser extension. Please use wfLoadExtension instead, ' .
        'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
    ); */
    return true;
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}