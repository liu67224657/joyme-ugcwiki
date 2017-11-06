<?php

/**
 * Custom formatter for 'page-link' notifications
 */
class EchoArticleThumbUp extends EchoBasicFormatter {

    /**
     * This is a workaround for backwards compatibility.
     * In https://gerrit.wikimedia.org/r/#/c/63076 we changed
     * the schema to save link-from-page-id instead of
     * link-from-namespace & link-from-title
     */
    protected function processParam( $event, $param, $message, $user ) {

        if($event->getType() == 'article-thumb-up'){
            $params = $event->getExtra();
            $message->params( $params['username']?$params['username']:'' );
            if($params['type'] == 1){
                $message->params( $this->getMessage( 'article-thumb-up-echo-comments-html' )->text() );
            }else{
                $message->params( $this->getMessage( 'article-thumb-up-content' )->text() );
            }
            $message->params( $params['synopsis']?$params['synopsis']:'' );
            $message->params( $params['from']?$params['from']:'' );
        }
    }
}
