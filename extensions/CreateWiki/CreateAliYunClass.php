<?php
class CreateAliYunClass{

    static $file_path = "/extensions/CreateWiki/aliyun/TopSdk.php";

    static protected $accessKeyId = 'm2LJu94lrAKPMGBm';
    static protected $accessKeySecret = 'jO3aBvvxQKfBoBEHXadiLhG0YFi8OJ';
    static protected $serverUrl = 'http://rds.aliyuncs.com/';
    static protected $dBInstanceId = 'rdsnu7brenu7bre';
    static private $accountName = 'wikiuser';
    static private $charSet = 'utf8';

    //Ali cloud query the database exists
    static function alyfindDataBase( $key ){

        self::getPath();

        $c = new AliyunClient;
        $c->accessKeyId = self::$accessKeyId;
        $c->accessKeySecret = self::$accessKeySecret;
        $c->serverUrl=self::$serverUrl;

        $req = new Rds20130528DescribeDatabasesRequest();
        $req->setdBInstanceId(self::$dBInstanceId);
        $req->setdBName( $key );

        $resp = $c->execute($req);
        return $resp->Databases->Database;
    }


    //Ali cloud create the database
    static function alyCreateDatabase( $key ,$dbDescription){

        self::getPath();
        $c = new AliyunClient;
        $c->accessKeyId = self::$accessKeyId;
        $c->accessKeySecret = self::$accessKeySecret;
        $c->serverUrl=self::$serverUrl;
        $req = new Rds20130528CreateDatabaseRequest();
        $req->setAccountName(self::$accountName);
        $req->setdBName( $key );
        $req->setCharacterSetName(self::$charSet);
        $req->setdBDescription( $dbDescription );
        $req->setdBInstanceId(self::$dBInstanceId);
        $resp = $c->execute($req);
        if(!isset($resp->Code)){
            return true;
        }
        return false;
    }

    static public function getPath(){

        global $IP;

        include_once $IP.self::$file_path;
    }
}