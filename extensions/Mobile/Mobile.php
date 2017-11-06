<?php
/**
 * Check is mobile
 * @param null
 * @return bool
 * author:pengzhang
 * */
function isMobile(){
	$userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

	$patterns = array(
		'mobi',
		'240x240',
		'240x320',
		'320x320',
		'alcatel',
		'android',
		'audiovox',
		'bada',
		'benq',
		'blackberry',
		'cdm-',
		'compal-',
		'docomo',
		'ericsson',
		'hiptop',
		'htc[-_]',
		'huawei',
		'ipod',
		'kddi-',
		'kindle',
		'meego',
		'midp',
		'mitsu',
		'mmp\/',
		'mot-',
		'motor',
		'ngm_',
		'nintendo',
		'opera.m',
		'palm',
		'panasonic',
		'philips',
		'phone',
		'playstation',
		'portalmmm',
		'sagem-',
		'samsung',
		'sanyo',
		'sec-',
		'sendo',
		'sharp',
		'silk',
		'softbank',
		'symbian',
		'teleca',
		'up.browser',
		'webos',
	);
	$patternsStart = array(
		'lg-',
		'sie-',
		'nec-',
		'lge-',
		'sgh-',
		'pg-',
	);
	$regex = '/^(' . implode( '|', $patternsStart ) . ')|(' . implode( '|', $patterns ) . ')/i';
	$isMobile = (bool)preg_match( $regex, $userAgent );
	return $isMobile;
}
?>