<?php

/**
 * Custom formatter for 'page-link' notifications
 */
class EchoArticleCiteMy extends EchoBasicFormatter {

    /**
     * This is a workaround for backwards compatibility.
     * In https://gerrit.wikimedia.org/r/#/c/63076 we changed
     * the schema to save link-from-page-id instead of
     * link-from-namespace & link-from-title
     */
    protected function processParam( $event, $param, $message, $user ) {

        if($event->getType() == 'article-cite-my'){
            $params = $event->getExtra();
            $message->params( @$params['username']?@$params['username']:'' );
            $message->params( @$params['article']?@$params['article']:'' );
            $message->params( @$params['synopsis']?@$params['synopsis']:'' );
            $message->params( @$params['from']?@$params['from']:'' );
        }
    }
}
