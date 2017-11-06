<?php
if ( function_exists( 'wfLoadExtension' ) ) {
    wfLoadExtension( 'SeoSettings' );
    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['SeoSettings'] = __DIR__ . '/i18n';
    $wgExtensionMessagesFiles['SeoSettingsAliases'] = __DIR__ . '/SeoSettings.alias.php';
    /* wfWarn(
        'Deprecated PHP entry point used for Renameuser extension. Please use wfLoadExtension instead, ' .
        'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
    ); */
    return true;
} else {
    die( 'This version of the SeoSettings extension requires MediaWiki 1.25+' );
}