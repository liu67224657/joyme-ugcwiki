<?php
if ( function_exists( 'wfLoadExtension' ) ) {

    require __DIR__ . "/autoload.php";

    wfLoadExtension( false ,__DIR__.'/json/aboutme.json');
    wfLoadExtension( false ,__DIR__.'/json/systemmessage.json');

    require_once( "$IP/extensions/AboutMe/AboutMe_Ajaxfonctions.php" );

    // Keep i18n globals so mergeMessageFileList.php doesn't break
    $wgMessagesDirs['AboutMe'] = __DIR__ . '/i18n';
    $wgMessagesDirs['GiveMeThumbUp'] = __DIR__ . '/i18n';

    $wgExtensionMessagesFiles['AboutMeAliases'] = __DIR__ . '/AboutMe.alias.php';
    $wgExtensionMessagesFiles['GiveMeThumbUpAliases'] = __DIR__ . '/AboutMe.alias.php';

    // Register the CSS with ResourceLoader
    $wgResourceModules['ext.AboutMe.css'] = array(
        'styles' =>'style/aboutMe.css',
	    'localBasePath' => __DIR__,
	    'remoteExtPath' => 'AboutMe',
	    'position' => 'top'
    );

    $wgResourceModules['ext.aboutMe.not.logged'] = array(
        'styles' => array(
            'style/notlogged.css'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'aboutMe',
        'position' => 'top'
    );

    $wgResourceModules['ext.AboutMe'] = array(
        'scripts' => array(
            'style/aboutMe.js',
            'style/m.version.js'
        ),
        'localBasePath' => __DIR__,
        'remoteExtPath' => 'AboutMe'
    );
    return true;
} else {
    die( 'This version of the Discussion extension requires MediaWiki 1.25+' );
}