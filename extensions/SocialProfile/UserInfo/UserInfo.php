<?php
/**
 * UserInfo extension
 * Adds <welcomeUser/> tag to display user-specific social information
 *
 * @file
 * @ingroup Extensions
 * @version 1.4.1
 * @author David Pean <david.pean@gmail.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @link https://www.mediawiki.org/wiki/Extension:UserInfo Documentation
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'UserInfo',
	'version' => '1.1.0',
	'author' => array( 'TianMing' ),
	'descriptionmsg' => 'userinfo-desc',
	'url' => 'https://wiki.joyme.com',
);

// Register the CSS with ResourceLoader
$wgResourceModules['ext.socialprofile.userinfo.css'] = array(
	'styles' => 'UserInfo.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SocialProfile/UserInfo',
	'position' => 'top'
);
// Register the JS with ResourceLoader
$wgResourceModules['ext.socialprofile.userinfo.js'] = array(
		'scripts' => 'UserInfo.js',
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'SocialProfile/UserInfo',
		'position' => 'bottom'
);

$wgHooks['ParserFirstCallInit'][] = 'wfUserInfo';
/**
 * Register <welcomeUser /> tag with the parser
 *
 * @param $parser Parser
 * @return Boolean: true
 */
function wfUserInfo( &$parser ) {
	$parser->setHook( 'userinfo', 'getUserInfo' );
	return true;
}

function getUserInfo( $input, $argv, $parser ) {
	global $wgUser, $wgOut, $wgLang;
	
	//$parser->disableCache();

	// Add CSS
	//$wgOut->addModuleStyles( 'ext.socialprofile.userinfo.css' );

	$username = empty($argv['username'])?'':$argv['username'];
	$show = empty($argv['show'])?1:intval($argv['show']);
	$width = empty($argv['width'])?32:intval($argv['width']);
	
	$user = User::newFromName( $username );
	//No such user
	if ($username == null){
		return 'username is null';
	}elseif( $user == null || $user->getId() == 0 ){
		return 'this user not exists';
	}
	//$ust = new UserStats( $user->getId(),$username );
	//$stats = $ust->getUserStats();
	
	$profile = new JoymeWikiUser();
	$userprofile = $profile->getProfile($user->getId());
	
	if(empty($userprofile)){
		return 'this userprofile not exists';
	}else{
		$username = $user->getName();
		$userprofile = $userprofile[0];
		$userprofile['icon'] = $userprofile['icon'].'?imageView2/1/w/'.$width.'/h/'.$width;
		$style = ' width="'.$width.'px" height="'.$width.'px" ';
	}
	// Make an avatar
	//$avatar = new wAvatar( $wgUser->getID(), 'l' );
	
	$group = $user->getGroups();
	
	if(in_array('sysop', $group) || in_array('bureaucrat', $group)){
		$groupicon = 'mp-usergroup-manager';
	}else{
		$groupicon = '';
	}
	$daoju = '<span class="'.(empty($userprofile['headskin'])?'':'daoju daoju'.$userprofile['headskin']).'"></span>';
	// Profile top images/points
	if($show == 1){
		$output = '<font class="mp-userinfo">'.
			'<a target="_blank" href="' . htmlspecialchars( '/home/用户:'.$username ) . '" class="userinfo" data-username="'.$userprofile['profileid'].'" rel="nofollow">'.
				'<span class="userGroup_box '.$groupicon.'"><cite></cite><img class="usericon_admin" '.$style.' src="'.$userprofile['icon'].'"/>'.$daoju.($userprofile['vtype']>0?'<span class="user-vip" title="'.$userprofile['vdesc'].'"></span>':'').'</span>'.
				'<span>'.$username.'</span>'.
			'</a>'.
		'</font>';
	}elseif($show == 2){
		$output = '<font class="mp-userinfo">'.
			'<a target="_blank" href="' . htmlspecialchars( '/home/用户:'.$username ) . '" class="userinfo" data-username="'.$userprofile['profileid'].'" rel="nofollow">'.
				'<span class="userGroup_box '.$groupicon.'"><cite></cite><img class="usericon_admin" '.$style.' src="'.$userprofile['icon'].'"/>'.$daoju.($userprofile['vtype']>0?'<span class="user-vip" title="'.$userprofile['vdesc'].'"></span>':'').'</span>'.
			'</a>'.
		'</font>';
	}else{
		$output = '<font class="mp-userinfo">'.
			'<a target="_blank" href="' . htmlspecialchars( '/home/用户:'.$username ) . '" class="userinfo" data-username="'.$userprofile['profileid'].'" rel="nofollow">'.
				'<span>'.$username.'</span>'.
			'</a>'.
		'</font>';
	}
	return $output;
}


