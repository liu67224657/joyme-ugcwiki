<?php
class EchoArticleComments extends EchoBasicFormatter {

    /**
     * This is a workaround for backwards compatibility.
     * In https://gerrit.wikimedia.org/r/#/c/63076 we changed
     * the schema to save link-from-page-id instead of
     * link-from-namespace & link-from-title
     */
    protected function processParam( $event, $param, $message, $user ) {

        if($event->getType() == 'article-comments'){
            $params = $event->getExtra();
            $message->params( $params['username']?$params['username']:'' );
            $message->params( $params['article']?$params['article']:'' );

            if($params['type'] == 2){
                $message->params( $this->getMessage( 'article-thumb-up-content-reply-your' )->text().@$params['commentusername'] );
            }else{
                if($params['type'] == 3){
                    $message->params( $this->getMessage( 'article-thumb-up-content-reply-who' )->text().@$params['othername'] );
                }else{
                    $message->params( $this->getMessage( 'article-thumb-up-comments' )->text() );
                }
            }
            $message->params( $params['synopsis']?$params['synopsis']:'' );
            $message->params( $params['from']?$params['from']:'' );
        }
    }
}
