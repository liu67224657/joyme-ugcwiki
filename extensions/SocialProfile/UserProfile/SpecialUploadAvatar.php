<?php
/**
 * A special page for uploading avatars
 * This page is a big hack -- it's just the image upload page with some changes
 * to upload the actual avatar files.
 * The avatars are not held as MediaWiki images, but
 * rather based on the user_id and in multiple sizes
 *
 * Requirements: Need writable directory $wgUploadPath/avatars
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use Joyme\core\JoymeUploadFile;
use Joyme\qiniu\Qiniu_Utils;
class SpecialUploadAvatar extends SpecialUpload {
	public $avatarUploadDirectory;
	public $icon;
	public $brief;
	public $sex;
	public $username;

	/**
	 * Constructor
	 */
	public function __construct( $request = null ) {
		SpecialPage::__construct( 'UploadAvatar', 'upload', false/* listed? */ );
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Let the parent handle most of the request, but specify the Upload
	 * class ourselves
	 */
	protected function loadRequest() {
		$request = $this->getRequest();
		parent::loadRequest( $request );
		$this->mUpload = new UploadAvatar();
		$this->mUpload->initializeFromRequest( $request );
	}

	/**
	 * Show the special page. Let the parent handle most stuff, but handle a
	 * successful upload ourselves
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
			$out->redirectHome('Special:UploadAvatar');
			return false;
		}

		$out->addModuleStyles(array(
			'ext.socialprofile.userprofile.useraccount.css',
			'ext.socialprofile.userprofile.usercentercommon.css'
		));
//		$out->addModuleStyles( array(
//			'ext.socialprofile.userprofile.useraccount.css',
//		) );

		$out->addModuleScripts(array(
			'ext.socialprofile.userprofile.useruploadimg.js',
		));

		// If the user isn't logged in, display an error
		if ( !$user->isLoggedIn() ) {
			$this->displayRestrictionError();
			return;
		}
//		$this->username = $user->mName;
		$accountsecurity = new SpecialAccountSecurity();
		$accountsecurity->initData();
		$this->icon = $accountsecurity->icon;

		$out->addHTML( '<!-- 内容区域 开始 -->
		<div class="container">
			<div class="row">
				<div class="setting-con">
			');
		$out->addHTML( $accountsecurity->getLeftSection("portrait") );
		$out->addHTML( $this->getRightSection() );
		$out->addHTML( '			
				</div>
			</div>
		</div>
	<!-- 内容区域 结束 -->' );
	}


	public function getRightSection(){
		global $wgQiNiuBucket,$wgQiNiuPath;

		$uptoken = Qiniu_Utils::Qiniu_UploadToken($wgQiNiuBucket);

		$output = '
			<div class="col-md-9 pag-hor-20">
				<div class="setting-r">
					<h3 class="setting-tit web-hide">修改头像</h3>
					<div class="acatar-con">
						<div class="edit-acatar">
							<img id="file_prev" src="'.$this->icon.'">
						</div>
						<div>
							<span class="acatar-btn fileinput-button">
								<label for="file" id="uploadAvatarBtn"><i id="file">上传头像</i></label>
							</span>
							<p class="web-hide">支持jpg、png、jpeg、bmp，图片大小2M以内，图片宽高大于等于150*150</p>
							<p class="web-hide">友情提示：最好上传规则图片，否则会影响头像显示效果</p>
							<button class="web-hide btn-sure uploadicon">保存</button>
							<button class="web-show btn-sure uploadicon">保存</button>
						</div>
						<input type="hidden" id="qiniu_domain" name="qiniu_domain" value="' . $wgQiNiuPath . '"/>
						<input type="hidden" id="uptoken" name="uptoken" value="' . $uptoken . '"/>
						<input type="hidden" name="usericon" id="usericon"/>
						<input type="hidden" name="usericonwidth" id="usericonwidth" value=""/>
						<input type="hidden" name="usericonheight" id="usericonheight" value=""/>
					</div>
				</div>
			</div>
		';
		$output.="<script type='text/javascript' src='http://lib.joyme.com/static/third/qiniuupload/qiniu.js'></script>";
		$output.="<script type='text/javascript' src='http://lib.joyme.com/static/third/qiniuupload/plupload/plupload.full.min.js'></script>";

		return $output;
	}


	/**
	 * Show some text and linkage on successful upload.
	 *
	 * @param $ext String: file extension (gif, jpg or png)
	 */
	private function showSuccess( $ext ) {
		global $wgAvatarKey, $wgUploadPath, $wgUploadAvatarInRecentChanges;

		$user = $this->getUser();
		$log = new LogPage( 'avatar' );
		if ( !$wgUploadAvatarInRecentChanges ) {
			$log->updateRecentChanges = false;
		}
		$log->addEntry(
			'avatar',
			$user->getUserPage(),
			$this->msg( 'user-profile-picture-log-entry' )->inContentLanguage()->text()
		);

		$uid = $user->getId();

		$output = '<h1>' . $this->msg( 'uploadavatar' )->plain() . '</h1>';
		$output .= UserProfile::getEditProfileNav( $this->msg( 'user-profile-section-picture' )->plain() );
		$output .= '<div class="profile-info">';
		$output .= '<p class="profile-update-title">' .
			$this->msg( 'user-profile-picture-yourpicture' )->plain() . '</p>';
		$output .= '<p>' . $this->msg( 'user-profile-picture-yourpicturestext' )->plain() . '</p>';

		$output .= '<table class="avatar-success-page">';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-large' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $wgUploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_l.' . $ext . '?ts=' . rand() . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-medlarge' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $wgUploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_ml.' . $ext . '?ts=' . rand() . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-medium' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $wgUploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_m.' . $ext . '?ts=' . rand() . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td class="title-cell" valign="top">' .
				$this->msg( 'user-profile-picture-small' )->plain() .
			'</td>
			<td class="image-cell">
				<img src="' . $wgUploadPath . '/avatars/' . $wgAvatarKey . '_' . $uid . '_s.' . $ext . '?ts=' . rand() . '" alt="" />
			</td>
		</tr>';
		$output .= '<tr>
			<td>
				<input type="button" onclick="javascript:history.go(-1)" class="site-button" value="' . $this->msg( 'user-profile-picture-uploaddifferent' )->plain() . '" />
			</td>
		</tr>';
		$output .= '</table>';
		$output .= '</div>';

		$this->getOutput()->addHTML( $output );
	}

	/**
	 * Displays the main upload form, optionally with a highlighted
	 * error message up at the top.
	 *
	 * @param $msg String: error message as HTML
	 * @param $sessionKey String: session key in case this is a stashed upload
	 * @param $hideIgnoreWarning Boolean: whether to hide "ignore warning" check box
	 * @return HTML output
	 */
	protected function getUploadForm( $message = '', $sessionKey = '', $hideIgnoreWarning = false ) {
		global $wgUseCopyrightUpload, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['avatar'] === false ) {
			$message = $this->msg( 'socialprofile-uploads-disabled' )->plain();
		}

		if ( $message != '' ) {
			$sub = $this->msg( 'uploaderror' )->plain();
			$this->getOutput()->addHTML( "<h2>{$sub}</h2>\n" .
				"<h4 class='error'>{$message}</h4>\n" );
		}

		if ( $wgUserProfileDisplay['avatar'] === false ) {
			return '';
		}

		$ulb = $this->msg( 'uploadbtn' );

		$source = null;

		if ( $wgUseCopyrightUpload ) {
			$source = "
				<td align='right' nowrap='nowrap'>" . $this->msg( 'filestatus' )->plain() . "</td>
				<td><input tabindex='3' type='text' name=\"wpUploadCopyStatus\" value=\"" .
				htmlspecialchars( $this->mUploadCopyStatus ) . "\" size='40' /></td>
				</tr><tr>
				<td align='right'>" . $this->msg( 'filesource' )->plain() . "</td>
				<td><input tabindex='4' type='text' name='wpUploadSource' id='wpUploadSource' value=\"" .
				htmlspecialchars( $this->mUploadSource ) . "\" /></td>
				";
		}

		$output = '<h1>' . $this->msg( 'uploadavatar' )->plain() . '</h1>';
		$output .= UserProfile::getEditProfileNav( $this->msg( 'user-profile-section-picture' )->plain() );
		$output .= '<div class="profile-info">';

		if ( $this->getAvatar( 'l' ) != '' ) {
			$output .= '<table>
				<tr>
					<td>
						<p class="profile-update-title">' .
							$this->msg( 'user-profile-picture-currentimage' )->plain() .
						'</p>
					</td>
				</tr>';
				$output .= '<tr>
					<td>' . $this->getAvatar( 'l' ) . '</td>
				</tr>
			</table>';
		}

		$output .= '<form id="upload" method="post" enctype="multipart/form-data" action="">';
		// The following two lines are delicious copypasta from HTMLForm.php,
		// function getHiddenFields() and they are required; wpEditToken is, as
		// of MediaWiki 1.19, checked _unconditionally_ in
		// SpecialUpload::loadRequest() and having the hidden title doesn't
		// hurt either
		// @see https://phabricator.wikimedia.org/T32953
		$output .= Html::hidden( 'wpEditToken', $this->getUser()->getEditToken(), array( 'id' => 'wpEditToken' ) ) . "\n";
		$output .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) . "\n";
		$output .= '<table>
				<tr>
					<td>
						<p class="profile-update-title">' .
							$this->msg( 'user-profile-picture-choosepicture' )->plain() .
						'</p>
						<p style="margin-bottom:10px;">' .
							$this->msg( 'user-profile-picture-picsize' )->plain() .
						'</p>
						<input tabindex="1" type="file" name="wpUploadFile" id="wpUploadFile" size="36"/>
					</td>
				</tr>
				<tr>' . $source . '</tr>
				<tr>
					<td>
						<input tabindex="5" type="submit" name="wpUpload" class="site-button" value="' . $ulb . '" />
					</td>
				</tr>
			</table>
			</form>' . "\n";

		$output .= '</div>';

		return $output;
	}

	/**
	 * Gets an avatar image with the specified size
	 *
	 * @param $size String: size of the image ('s' for small, 'm' for medium,
	 * 'ml' for medium-large and 'l' for large)
	 * @return String: full img HTML tag
	 */
	function getAvatar( $size ) {
		global $wgAvatarKey, $wgUploadDirectory, $wgUploadPath;
		$files = glob(
			$wgUploadDirectory . '/avatars/' . $wgAvatarKey . '_' .
			$this->getUser()->getID() . '_' . $size . '*'
		);
		if ( isset( $files[0] ) && $files[0] ) {
			return "<img src=\"{$wgUploadPath}/avatars/" .
				basename( $files[0] ) . '" alt="" border="0" />';
		}
	}


}

class UploadAvatar extends UploadFromFile {
	public $mExtension;

	function createThumbnail( $imageSrc, $imageInfo, $imgDest, $thumbWidth ) {
		global $wgUseImageMagick, $wgImageMagickConvertCommand;

		if ( $wgUseImageMagick ) { // ImageMagick is enabled
			list( $origWidth, $origHeight, $typeCode ) = $imageInfo;

			if ( $origWidth < $thumbWidth ) {
				$thumbWidth = $origWidth;
			}
			$thumbHeight = ( $thumbWidth * $origHeight / $origWidth );
			$border = ' -bordercolor white  -border  0x';
			if ( $thumbHeight < $thumbWidth ) {
				$border = ' -bordercolor white  -border  0x' . ( ( $thumbWidth - $thumbHeight ) / 2 );
			}
			if ( $typeCode == 2 ) {
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
					' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
					$thumbWidth . '+0+0   -quality 100 ' . $border . ' ' .
					$imageSrc . ' ' . $this->avatarUploadDirectory . '/' . $imgDest . '.jpg'
				);
			}
			if ( $typeCode == 1 ) {
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
					' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
					$thumbWidth . '+0+0 ' . $imageSrc . ' ' . $border . ' ' .
					$this->avatarUploadDirectory . '/' . $imgDest . '.gif'
				);
			}
			if ( $typeCode == 3 ) {
				exec(
					$wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
					' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
					$thumbWidth . '+0+0 ' . $imageSrc . ' ' .
					$this->avatarUploadDirectory . '/' . $imgDest . '.png'
				);
			}
		} else { // ImageMagick is not enabled, so fall back to PHP's GD library
			// Get the image size, used in calculations later.
			list( $origWidth, $origHeight, $typeCode ) = getimagesize( $imageSrc );

			switch( $typeCode ) {
				case '1':
					$fullImage = imagecreatefromgif( $imageSrc );
					$ext = 'gif';
					break;
				case '2':
					$fullImage = imagecreatefromjpeg( $imageSrc );
					$ext = 'jpg';
					break;
				case '3':
					$fullImage = imagecreatefrompng( $imageSrc );
					$ext = 'png';
					break;
			}

			$scale = ( $thumbWidth / $origWidth );

			// Create our thumbnail size, so we can resize to this, and save it.
			$tnImage = imagecreatetruecolor(
				$origWidth * $scale,
				$origHeight * $scale
			);

			// Resize the image.
			imagecopyresampled(
				$tnImage,
				$fullImage,
				0, 0, 0, 0,
				$origWidth * $scale,
				$origHeight * $scale,
				$origWidth,
				$origHeight
			);

			// Create a new image thumbnail.
			if ( $typeCode == 1 ) {
				imagegif( $tnImage, $imageSrc );
			} elseif ( $typeCode == 2 ) {
				imagejpeg( $tnImage, $imageSrc );
			} elseif ( $typeCode == 3 ) {
				imagepng( $tnImage, $imageSrc );
			}

			// Clean up.
			imagedestroy( $fullImage );
			imagedestroy( $tnImage );

			// Copy the thumb
			copy(
				$imageSrc,
				$this->avatarUploadDirectory . '/' . $imgDest . '.' . $ext
			);
		}
	}

	/**
	 * Create the thumbnails and delete old files
	 */
	/*public function performUpload( $comment, $pageText, $watch, $user ) {
		global $wgUploadDirectory, $wgAvatarKey, $wgMemc;

		$this->avatarUploadDirectory = $wgUploadDirectory . '/avatars';

		$imageInfo = getimagesize( $this->mTempPath );
		switch ( $imageInfo[2] ) {
			case 1:
				$ext = 'gif';
				break;
			case 2:
				$ext = 'jpg';
				break;
			case 3:
				$ext = 'png';
				break;
			default:
				return Status::newFatal( 'filetype-banned-type' );
		}

		$dest = $this->avatarUploadDirectory;

		$uid = $user->getId();
		$avatar = new wAvatar( $uid, 'l' );
		// If this is the user's first custom avatar, update statistics (in
		// case if we want to give out some points to the user for uploading
		// their first avatar)
		if ( strpos( $avatar->getAvatarImage(), 'default_' ) !== false ) {
			$stats = new UserStatsTrack( $uid, $user->getName() );
			$stats->incStatField( 'user_image' );
		}

		$this->createThumbnail( $this->mTempPath, $imageInfo, $wgAvatarKey . '_' . $uid . '_l', 75 );
		$this->createThumbnail( $this->mTempPath, $imageInfo, $wgAvatarKey . '_' . $uid . '_ml', 50 );
		$this->createThumbnail( $this->mTempPath, $imageInfo, $wgAvatarKey . '_' . $uid . '_m', 30 );
		$this->createThumbnail( $this->mTempPath, $imageInfo, $wgAvatarKey . '_' . $uid . '_s', 16 );

		if ( $ext != 'jpg' ) {
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.jpg' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.jpg' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.jpg' );
			}
		}
		if ( $ext != 'gif' ) {
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.gif' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.gif' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.gif' );
			}
		}
		if ( $ext != 'png' ) {
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_s.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_m.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_l.png' );
			}
			if ( is_file( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.png' ) ) {
				unlink( $this->avatarUploadDirectory . '/' . $wgAvatarKey . '_' . $uid . '_ml.png' );
			}
		}

		$key = wfMemcKey( 'user', 'profile', 'avatar', $uid, 's' );
		$data = $wgMemc->delete( $key );

		$key = wfMemcKey( 'user', 'profile', 'avatar', $uid, 'm' );
		$data = $wgMemc->delete( $key );

		$key = wfMemcKey( 'user', 'profile', 'avatar', $uid , 'l' );
		$data = $wgMemc->delete( $key );

		$key = wfMemcKey( 'user', 'profile', 'avatar', $uid, 'ml' );
		$data = $wgMemc->delete( $key );

		$this->mExtension = $ext;
		return Status::newGood();
	}*/

	/**
	 * Don't verify the upload, since it all dangerous stuff is killed by
	 * making thumbnails
	 */
	public function verifyUpload() {
		return array( 'status' => self::OK );
	}

	/**
	 * Only needed for the redirect; needs fixage
	 */
	public function getTitle() {
		return Title::makeTitle( NS_FILE, 'Avatar-placeholder' . uniqid() . '.jpg' );
	}

	/**
	 * We don't overwrite stuff, so don't care
	 */
	public function checkWarnings() {
		return array();
	}

}
