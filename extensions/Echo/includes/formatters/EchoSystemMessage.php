<?php

/**
 * Custom formatter for 'page-link' notifications
 */
class EchoSystemMessage extends EchoBasicFormatter {

    /**
     * This is a workaround for backwards compatibility.
     * In https://gerrit.wikimedia.org/r/#/c/63076 we changed
     * the schema to save link-from-page-id instead of
     * link-from-namespace & link-from-title
     */
    protected function processParam( $event, $param, $message, $user ) {

        if($event->getType() == 'echo-system-message'){
            $params = $event->getExtra();
            $message->params( $params['content']?$params['content']:'' );
        }
    }
}
