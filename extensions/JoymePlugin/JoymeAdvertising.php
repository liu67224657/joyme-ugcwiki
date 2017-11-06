<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xinshi
 * Date: 15-1-113
 * Time: 下午2:47
 * To change this template use File | Settings | File Templates.
 */
function JoymeAdvertising() {

    global $wgParser;
    $wgParser->setHook( "JoymeAdvertising", "JoymeAdvertisingShow" );
}

function JoymeAdvertisingShow($input,$argv) {

    if(!empty($argv['cid'])){
        $cid = intval($argv['cid']);
        return '<script type="text/javascript" src="http://joyme.adsame.com/s?z=joyme&c='.$cid.'"></script>';
    }
}

$wgExtensionFunctions[] = "JoymeAdvertising";
