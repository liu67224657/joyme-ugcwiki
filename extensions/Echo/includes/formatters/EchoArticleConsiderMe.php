<?php

/**
 * Custom formatter for 'page-link' notifications
 */
class EchoArticleConsiderMe extends EchoBasicFormatter {

    /**
     * This is a workaround for backwards compatibility.
     * In https://gerrit.wikimedia.org/r/#/c/63076 we changed
     * the schema to save link-from-page-id instead of
     * link-from-namespace & link-from-title
     */
    protected function processParam( $event, $param, $message, $user ) {

        if($event->getType() == 'article-consider-me'){
            $params = $event->getExtra();
            $message->params( @$params['username']?$params['username']:'' );
            if($params['type'] == true){
                $message->params( $this->getMessage( 'article_consider-me-link' )->text() );
            }else{
                $message->params( $this->getMessage( 'article_consider-me-link-cancel' )->text() );
            }
        }
    }
}
