<?php
/**
 * Description:刷新缓存
 * Author: gradydong
 * Date: 2016/11/30
 * Time: 17:30
 * Copyright: Joyme.com
 */

class SpecialRefreshCache extends SpecialPage {


    /**
     * Constructor
     */
    public function __construct() {
        SpecialPage::__construct( 'RefreshCache');
    }

    /**
     * Group this special page under the correct header in Special:SpecialPages.
     *
     * @return string
     */
    /*function getGroupName() {
        return 'users';
    }*/

    /**
     * Show the special page.
     *
     * @param $params Mixed: parameter(s) passed to the page or null
     */
    public function execute( $params ) {
        $out = $this->getOutput();
        $request = $this->getRequest();
        $user = $this->getUser();
        $this->setHeaders();

        global $wgWikiname;
        if($wgWikiname !='home'){
            $out->redirectHome('Special:RefreshCache');
            return false;
        }

        $out->addModuleStyles(array(
            'ext.socialprofile.userprofile.useraccount.css',
            'ext.socialprofile.userprofile.usercentercommon.css'
        ));

        $out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');

        if ( $request->wasPosted() ) {
            $purgeurl = $request->getVal('purgeurl');
            if(!empty($purgeurl)){
//                $urls = array();
//                $urls[] = urlencode($purgeurl);
                /*CdnCacheUpdate::purge( $urls );
                $out->addHTML( '刷新成功' );
                $out->addHTML( '<br/>' );*/

                $this->varnishPurge ($purgeurl,$out);

                $out->addHTML( '<a href="/home/index.php?title=Special:RefreshCache">返回</a>' );
            }else{
                $out->addHTML( '
                <form action="" method="post">
                    URL：<input type="text" name="purgeurl">
                    <input type="submit" name="提交">
                </form>
        ' );
            }
        }else{
            $out->addHTML( '
                <form action="" method="post">
                    URL：<input type="text" name="purgeurl">
                    <input type="submit" name="提交">
                </form>
        ' );
        }

        $out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );

    }

    function varnishPurge ($txtUrl,$out)
    {
        if(strstr($txtUrl, 'http://')){
            // Step one: prepare the string, strip the http:// prefix
            $txtUrl = str_replace("http://", "", $txtUrl);

            // Get the hostname/fqdn and the URL
            $hostname = substr($txtUrl, 0, strpos($txtUrl, '/'));
            $url = substr($txtUrl, strpos($txtUrl, '/'), strlen($txtUrl));


            global $wgSquidServers;

            foreach ($wgSquidServers as $server){
                // Open connection to Varnish and send the Purge request
                $errno = (integer) "";
                $errstr = (string) "";
//                $varnish_sock = fsockopen("127.0.0.1", "80", $errno, $errstr, 10);
                $varnish_sock = fsockopen($server, "80", $errno, $errstr, 10);
                if (!$varnish_sock) {
                    error_log("Varnish connect error: ". $errstr ."(". $errno .")");
                } else {
                    // Build the request
                    $cmd = "PURGE ". $url ." HTTP/1.0\r\n";
                    $cmd .= "Host: ". $hostname ."\r\n";
                    $cmd .= "Connection: Close\r\n";
                    // Finish the request
                    $cmd .= "\r\n";

                    // Send the request
                    $out->addHTML( "Sending request: <blockquote>". nl2br($cmd) ."</blockquote>" );
                    fwrite($varnish_sock, $cmd);

                    // Get the reply
                    $out->addHTML( "Received answer: <blockquote>" );
                    $response = "";
                    while (!feof($varnish_sock)) {
                        $response .= fgets($varnish_sock, 128);
                    }
                    $out->addHTML( nl2br($response) );
                    $out->addHTML( "</blockquote>" );
                }

                // Close the socket
                fclose($varnish_sock);
            }

        }else{
            $out->addHTML( 'Bad URL' );
        }
        $out->addHTML( '<br/>' );
    }

}
