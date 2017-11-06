<?php

use Joyme\net\Curl;
use Joyme\core\Log;

class ArticleList {
	
	// public static $width = 0;
	public static $css = '';
	
	public static function onParserSetup( &$parser ) {
		$parser->setHook( 'arclist', 'ArticleList::renderTag' );
	}

	public static function renderTag( $input, $args, $parser ) {
		global $wgWikiname, $wgEnv, $wgOut;
		// wfProfileIn( __METHOD__ );
		$parser->disableCache();
		$wgOut->addModuleStyles( 'ext.articlelist.css' );
		$wgOut->addModules( 'ext.articlelist.js' );
		$args = func_get_args();
		$parser = $args[0];
		$params = array();
		$params = $args[1];

		$url = 'http://wiki.joyme.'. $wgEnv .'/'. $wgWikiname .'/api.php?action=askargs&format=json';
		if( !empty( $params['category'] ) ){
			$url .= '&conditions=分类:' . $params['category'];
		}else{
			return '本插件仅仅支持分类筛选,请设置分类参数';
		}
		$url .= '&printouts=tag|image|headimage|Modification%20date';
		$url .= '&parameters=sort%3DModification%20date|order%3Ddesc';
		if( !empty( $params['limit'] ) ){
			$url .= '|limit%3D'.intval($params['limit']);
		}
		$curl = new Curl();
		$json = $curl->Get( $url );
		$data = json_decode($json, true);
		if( count( $data['query']['results'] ) < 1 ){
			Log::error( 'articlelistjson:'.$json );
			Log::error( 'articlelist:'.$url );
			return '没有找到相关数据';
		}
		
		if( !empty( $params['width'] ) ){
			$width = intval($params['width']);
			self::$css = $width ? 'width:'. $width . 'px;' : '';
		}else{
			self::$css = '';
		}
		
		if( $params['type'] == 'tagtitledate' ){
			return ArticleList::typeTagTitleDate( $data['query']['results'] );
		}else if( $params['type'] == 'imagetitledateread' ){
			return ArticleList::typeImageTitleDateRead( $data['query']['results'] );
		}else if( $params['type'] == 'onelist' ){
			return ArticleList::oneList( $data['query']['results'] );
		}else if( $params['type'] == 'twolist' ){
			return ArticleList::twoList( $data['query']['results'] );
		}else{
			return '暂不支持格式:'.$params['type'];
		}
		
		// wfProfileOut( __METHOD__ );
		return $output;
	}
	
	// [标签]文章标题 12.10
	public static function typeTagTitleDate( $data ){
		$html = '<div class="wjm1125 list-wjm" style="'. self::$css .'"><ul>';
		foreach( $data as $row ){
			$title = !empty($row['fulltext']) ? $row['fulltext'] : '';
			$url = !empty($row['fullurl']) ? $row['fullurl'] : '';
			$row = $row['printouts'];
			$tag = !empty($row['tag']) ? $row['tag'][0] : '';
			$date = !empty($row['Modification date']) ? $row['Modification date'][0] : time();
			$html .= '<li><a class="li-a bl" href="'. $url .'"><u class="tag">['. $tag .']</u><u class="w6">&nbsp;&nbsp;</u><i class="tit" style="width: 235px;">'. $title .'</i></a><i class="date">'. date('m-d', $date) .'</i></li>';
		}
		$html .= '</ul></div>';
		return $html;
	}
	
	// [图片]文章标题 12.10 浏览量
	public static function typeImageTitleDateRead( $data ){
		global $wgResourceBasePath;
		$html = '<div class="wjm1125 list2-wjm" style="'. self::$css .'"><ul>';
		foreach( $data as $row ){
			$title = !empty($row['fulltext']) ? $row['fulltext'] : '';
			$url = !empty($row['fullurl']) ? $row['fullurl'] : '';
			$row = $row['printouts'];
			$imagename = !empty($row['image']) ? $row['image'][0] : '';//url
			$read = !empty($row['read']) ? $row['read'][0] : 0;
			$date = !empty($row['Modification date']) ? $row['Modification date'][0] : time();
			$imagesrc = self::getImgSrc( $imagename );
			
			$html .= '<li><i class="list-img"><a href="'. $url .'"><img src="'. $imagesrc .'"></a></i><div class="list-titall" style="width: 210px;"><a class="tit bl" href="'. $url .'"><u>'. $title .'</u></a><a class="gray a0" href="'. $url .'"><u class="list-pic"><img src="'.$wgResourceBasePath.'/extensions/ArticleList/images/lishi.png"></u><i class="date">'. date('m-d', $date) .'</i></a></div><div class="c-wjm"></div></li>';
		}
		$html .= '</ul></div>';
		return $html;
	}
	
	// [图片]文章标题 单列 type = 0 单列， type = 1 多列
	public static function oneList( $data , $adddiv = true, $addcss=false ){
		if( $addcss ){
			self::$css .= 'margin-right: 0px;';
		}
		$html = '<div class="wjm1125 list3-wjm" style="'. self::$css .'">';
		$i = 0;
		foreach( $data as $row ){
			$title = !empty($row['fulltext']) ? $row['fulltext'] : '';
			$url = !empty($row['fullurl']) ? $row['fullurl'] : '';
			$row = $row['printouts'];
			if( $i == 0 ){
				$headimage = !empty($row['headimage']) ? $row['headimage'][0] : '';
				$imagesrc = self::getImgSrc( $headimage );
				$html .= '<a class="list-img" href="'. $url .'"><img src="'. $imagesrc .'" title="'. $title .'"></a><ul>';
			}else{
				$html .= '<li><a class="tit bl" href="'. $url .'">'. $title .'</a></li>';
			}
			$i++;
		}
		$html .= '</ul></div>';
		if( $adddiv ){
			$html .= '<div class="c-wjm"></div>';
		}
		return $html;
	}
	
	// [图片]文章标题 两列
	public static function twoList( $data ){
		if( count($data) == 1 ){
			return self::oneList($data);
		}
		$size = ceil( count( $data ) / 2 );
		$data = array_chunk( $data, $size);
		// self::$css .= 'margin-right: 0px;';
		return self::oneList($data[0], false) . self::oneList($data[1], true, true);
	}
	
	public static function getImgSrc( $imgName ){
		global $wgOut;
		$parser = new Parser();
		$title = new Title();
		$message_text = $parser->parse( "[[file:$imgName]]", $title, $wgOut->parserOptions(), true );
		$message = $message_text->getText();
		preg_match_all('/<img.*?src="(.*?)".*?>/is', $message, $match);
		if( !empty($match[1]) && !empty($match[1][0]) ){
			return $match[1][0];
		}else{
			return '';
		}
	}
}