<?php
use Joyme\core\Request;
$wgAjaxExportList[] = 'wfAboutMeSendSystemMessages';

function wfAboutMeSendSystemMessages( ){

    $sign = trim(Request::getParam('sign'));
    if( $sign == '!/^(-joyem-%-send-*message%$' ){
        $messages = trim(Request::getParam('message'));
        $keys = trim(Request::getParam('wiki_key'));
        SendSystemMessage::sendJoymeSystemMessage( $keys, $messages);
    }
    return true;
}
