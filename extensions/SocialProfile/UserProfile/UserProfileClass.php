<?php
/**
 * Class to access profile data for a user
 */
class UserProfile {
	/**
	 * @var Integer: the current user's user ID. Set in the constructor.
	 */
	public $user_id;

	/**
	 * @var String: the current user's user name. Set in the constructor.
	 */
	public $user_name;

	/** unused, remove me? */
	public $profile;

	/**
	 * @var Integer: used in getProfileComplete()
	 */
	public $profile_fields_count;

	/**
	 * @var Array: array of valid profile fields; used in getProfileComplete()
	 */
	public $profile_fields = array(
		'real_name',
		'location_city',
		'hometown_city',
		'birthday',
		'about',
		'places_lived',
		'websites',
		'occupation',
		'schools',
		'movies',
		'tv',
		'books',
		'magazines',
		'video_games',
		'snacks',
		'drinks',
		'custom_1',
		'custom_2',
		'custom_3',
		'custom_4',
		'email'
	);

	/**
	 * @var Array: unused, remove me?
	 */
	public $profile_missing = array();

	/**
	 * Constructor
	 * @private
	 */
	/* private */ function __construct( $username ) {
		$title1 = Title::newFromDBkey( $username );
		$this->user_name = $title1->getText();
		$this->user_id = User::idFromName( $this->user_name );
	}

	/**
	 * Deletes the memcached key for $user_id.
	 *
	 * @param $user_id Integer: user ID number
	 */
	static function clearCache( $user_id ) {
		global $wgMemc;

		$key = wfMemcKey( 'user', 'profile', 'info', $user_id );
		$wgMemc->delete( $key );
	}

	/**
	 * Loads social profile info for the current user.
	 * First tries fetching the info from memcached and if that fails, queries
	 * the database.
	 * Fetched info is cached in memcached.
	 */
	public function getProfile() {
		global $wgMemc;

		$user = User::newFromId( $this->user_id );
		$user->loadFromId();

		// Try cache first
		$key = wfMemcKey( 'user', 'profile', 'info', $this->user_id );
		$data = $wgMemc->get( $key );
		if ( $data ) {
			wfDebug( "Got user profile info for {$this->user_name} from cache\n" );
			$profile = $data;
		} else {
			wfDebug( "Got user profile info for {$this->user_name} from DB\n" );
			$dbr = wfGetDB( DB_SLAVE );
			$row = $dbr->selectRow(
				'user_profile',
				'*',
				array( 'up_user_id' => $this->user_id ),
				__METHOD__,
				array( 'LIMIT' => 5 )
			);

			if ( $row ) {
				$profile['user_id'] = $this->user_id;
			} else {
				$profile['user_page_type'] = 1;
				$profile['user_id'] = 0;
			}
			$showYOB = $user->getIntOption( 'showyearofbirth', !isset( $row->up_birthday ) ) == 1;
			$issetUpBirthday = isset( $row->up_birthday ) ? $row->up_birthday : '';
			$profile['location_city'] = isset( $row->up_location_city ) ? $row->up_location_city : '';
			$profile['location_state'] = isset( $row->up_location_state ) ? $row->up_location_state : '';
			$profile['location_country'] = isset( $row->up_location_country ) ? $row->up_location_country : '';
			$profile['hometown_city'] = isset( $row->up_hometown_city ) ? $row->up_hometown_city : '';
			$profile['hometown_state'] = isset( $row->up_hometown_state ) ?  $row->up_hometown_state : '';
			$profile['hometown_country'] = isset( $row->up_hometown_country ) ? $row->up_hometown_country : '';
			$profile['birthday'] = $this->formatBirthday( $issetUpBirthday, $showYOB );

			$profile['about'] = isset( $row->up_about ) ? $row->up_about : '';
			$profile['places_lived'] = isset( $row->up_places_lived ) ? $row->up_places_lived : '';
			$profile['websites'] = isset( $row->up_websites ) ? $row->up_websites : '';
			$profile['relationship'] = isset( $row->up_relationship ) ? $row->up_relationship : '';
			$profile['occupation'] = isset( $row->up_occupation ) ? $row->up_occupation : '';
			$profile['schools'] = isset( $row->up_schools ) ? $row->up_schools : '';
			$profile['movies'] = isset( $row->up_movies ) ? $row->up_movies : '';
			$profile['music'] = isset( $row->up_music ) ? $row->up_music : '';
			$profile['tv'] = isset( $row->up_tv ) ? $row->up_tv : '';
			$profile['books'] = isset( $row->up_books ) ? $row->up_books : '';
			$profile['magazines'] = isset( $row->up_magazines ) ? $row->up_magazines : '';
			$profile['video_games'] = isset( $row->up_video_games ) ? $row->up_video_games : '';
			$profile['snacks'] = isset( $row->up_snacks ) ? $row->up_snacks : '';
			$profile['drinks'] = isset( $row->up_drinks ) ? $row->up_drinks : '';
			$profile['custom_1'] = isset( $row->up_custom_1 ) ? $row->up_custom_1 : '';
			$profile['custom_2'] = isset( $row->up_custom_2 ) ? $row->up_custom_2 : '';
			$profile['custom_3'] = isset( $row->up_custom_3 ) ? $row->up_custom_3 : '';
			$profile['custom_4'] = isset( $row->up_custom_4 ) ? $row->up_custom_4 : '';
			$profile['custom_5'] = isset( $row->up_custom_5 ) ? $row->up_custom_5 : '';
			$profile['user_page_type'] = isset( $row->up_type ) ? $row->up_type : '';
			$wgMemc->set( $key, $profile );
		}

		$profile['real_name'] = $user->getRealName();
		$profile['email'] = $user->getEmail();

		return $profile;
	}

	/**
	 * Format the user's birthday.
	 *
	 * @param $birthday String: birthday in YYYY-MM-DD format
	 * @return String: formatted birthday
	 */
	function formatBirthday( $birthday, $showYear = true ) {
		$dob = explode( '-', $birthday );
		if ( count( $dob ) == 3 ) {
			$month = $dob[1];
			$day = $dob[2];
			if ( !$showYear ) {
				if ( $dob[1] == '00' && $dob[2] == '00' ) {
					return '';
				} else {
					return date( 'F jS', mktime( 0, 0, 0, $month, $day ) );
				}
			}
			$year = $dob[0];
			if ( $dob[0] == '00' && $dob[1] == '00' && $dob[2] == '00' ) {
				return '';
			} else {
				return date( 'F jS, Y', mktime( 0, 0, 0, $month, $day, $year ) );
			}
			//return $day . ' ' . $wgLang->getMonthNameGen( $month );
		}
		return $birthday;
	}

	/**
	 * Get the user's birthday year by exploding the given birthday in three
	 * parts and returning the first one.
	 *
	 * @param $birthday String: birthday in YYYY-MM-DD format
	 * @return String: birthyear or '00'
	 */
	function getBirthdayYear( $birthday ) {
		$dob = explode( '-', $birthday );
		if ( count( $dob ) == 3 ) {
			return $dob[0];
		}
		return '00';
	}

	/**
	 * How many % of this user's profile is complete?
	 * Currently unused, I think that this might've been used in some older
	 * ArmchairGM code, but this looks useful enough to be kept around.
	 *
	 * @return Integer
	 */
	public function getProfileComplete() {
		global $wgUser;

		$complete_count = 0;

		// Check all profile fields
		$profile = $this->getProfile();
		foreach ( $this->profile_fields as $field ) {
			if ( $profile[$field] ) {
				$complete_count++;
			}
			$this->profile_fields_count++;
		}

		// Check if the user has a non-default avatar
		$this->profile_fields_count++;
		$avatar = new wAvatar( $wgUser->getID(), 'l' );
		if ( strpos( $avatar->getAvatarImage(), 'default_' ) === false ) {
			$complete_count++;
		}

		return round( $complete_count / $this->profile_fields_count * 100 );
	}

	static function getEditProfileNav( $current_nav ) {
		$lines = explode( "\n", wfMessage( 'update_profile_nav' )->inContentLanguage()->text() );
		$output = '<div class="profile-tab-bar">';

		foreach ( $lines as $line ) {
			if ( strpos( $line, '*' ) !== 0 ) {
				continue;
			} else {
				$line = explode( '|' , trim( $line, '* ' ), 2 );
				$page = Title::newFromText( $line[0] );
				$link_text = $line[1];

				// Maybe it's the name of a system message? (bug #30030)
				$msgObj = wfMessage( $line[1] );
				if ( !$msgObj->isDisabled() ) {
					$link_text = $msgObj->parse();
				}

				$output .= '<div class="profile-tab' . ( ( $current_nav == $link_text ) ? '-on' : '' ) . '">';
				$output .= Linker::link( $page, $link_text );
				$output .= '</div>';
			}
		}

		$output .= '<div class="visualClear"></div></div>';

		return $output;
	}

	public function getUserWikis($user_id,$uw_status='',$limit=0,$offset=0){
        $where = array();
        $options = array();
        if (empty($user_id)) {
            return false;
        }else{
            $where['user_id'] = $user_id;
        }
        if($uw_status){
            $where['status'] = $uw_status;
        }

        if ( $limit > 0 ) {
            $limitvalue = 0;
            if ( $offset ) {
                $limitvalue = ($offset * $limit) - $limit;
            }
            $options['LIMIT'] = $limit;
            $options['OFFSET'] = $limitvalue;
        }
		$options['ORDER BY'] = 'usr_id DESC';

        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'user_site_relation',
            'user_id,site_id,status',
            $where,
            __METHOD__,
            $options
        );

        $wikis = array();
        if ($res) {
            foreach ($res as $row) {
                $wikis[] = array(
                    'user_id' => $row->user_id,
                    'site_id' => $row->site_id,
                    'status' => $row->status
                );
            }
        }

        return $wikis;
	}

    public function getUserWikisCount($user_id,$status=0){
        $where = array();
        if (empty($user_id)) {
            return false;
        }else{
            $where['user_id'] = $user_id;
        }
		if($status){
			$where['status'] = $status;
		}
        $dbr = wfGetDB(DB_SLAVE);
        return $dbr->selectRowCount(
            'user_site_relation',
            '*',
            $where
        );
    }

	public function displayManageWikis($wikis){

		$output = '';
		if (!empty($wikis)) {

			$site_ids = array_column($wikis,'site_id');
			if($site_ids){
				$joymesite = new JoymeSite();
				$siteinfos = $joymesite->getSiteInfo($site_ids);
				if($siteinfos){
					$site_names = array_column($siteinfos,'site_name','site_id');
					$site_keys = array_column($siteinfos,'site_key','site_id');
					$site_icons = array_column($siteinfos,'site_icon','site_id');
					$page_counts = array_column($siteinfos,'page_count','site_id');
					$edit_counts = array_column($siteinfos,'edit_count','site_id');
					$edituser_counts = array_column($siteinfos,'edituser_count','site_id');
					$yes_editcounts = array_column($siteinfos,'yes_editcount','site_id');
					$follow_usercounts = array_column($siteinfos,'follow_usercount','site_id');
				}
			}

			foreach ($wikis as $manageWiki) {

				if (isset($site_keys[$manageWiki['site_id']])
					&& $site_keys[$manageWiki['site_id']]
				) {
					$site_key = $site_keys[$manageWiki['site_id']];
				} else {
					$site_key = '';
				}

				$output .= '
                    <li class="col-sm-4">
                        <div class="manage-wiki">
                            <a href="/'.$site_key.'/首页" class="mg-wiki-main fn-clear col-sm-12" target="_blank">
                                 <b class="manager web-hide">管理员</b>
                                <div class="col-md-12">
                                     <cite>';
				if(isset($site_icons[$manageWiki['site_id']])
					&& $site_icons[$manageWiki['site_id']]
				){
					$output .= '<img src="'.$site_icons[$manageWiki['site_id']].'" alt="">';
				}else{
					$output .= '<img src="" alt="">';
				}

				$output .= '
							<i>管理员</i>
						</cite>
					</div>
					<div class="manager-text col-md-12">';
				//wiki名称
				if(isset($site_names[$manageWiki['site_id']])
					&& $site_names[$manageWiki['site_id']]
				){
					$output .= '<font>'.$site_names[$manageWiki['site_id']].'</font>';
				}else{
					$output .= '<font></font>';
				}
				//页面总数量
				if(isset($page_counts[$manageWiki['site_id']])
					&& $page_counts[$manageWiki['site_id']]
				){
					$output .= '<span>页面总数量：'.$page_counts[$manageWiki['site_id']].' </span>';
				}else{
					$output .= '<span>页面总数量：0 </span>';
				}
				//编辑总次数
				if(isset($edit_counts[$manageWiki['site_id']])
					&& $edit_counts[$manageWiki['site_id']]
				){
					$output .= '<span>编辑总次数：'.$edit_counts[$manageWiki['site_id']].'  </span>';
				}else{
					$output .= '<span>编辑总次数：0  </span>';
				}

				$output .= '
					</div>
				</a>
				<a href="javascript:;" class="web-hide add-edit">';

				if(isset($follow_usercounts[$manageWiki['site_id']])
					&& $follow_usercounts[$manageWiki['site_id']]
				){
					$output .= '<span>关注人数：'.$follow_usercounts[$manageWiki['site_id']].'  </span>';
				}else{
					$output .= '<span>关注人数：0 </span>';
				}
				if(isset($edituser_counts[$manageWiki['site_id']])
					&& $edituser_counts[$manageWiki['site_id']]
				){
					$output .= '<span>编辑人数：'.$edituser_counts[$manageWiki['site_id']].'  </span>';
				}else{
					$output .= '<span>编辑人数：0 </span>';
				}

				if(isset($yes_editcounts[$manageWiki['site_id']])
					&& $yes_editcounts[$manageWiki['site_id']]
				){
					$output .= '<span>昨日编辑：'.$yes_editcounts[$manageWiki['site_id']].'  </span>';
				}else{
					$output .= '<span>昨日编辑：0  </span>';
				}


				$output .= '
                                </a>
                                <i class="caret  count-icon web-hide"></i>
                            </div>
                        </li>';
			}
		}
		return $output;
	}

	public function displayContributeWikis($wikis){
		$output = '';
		if (!empty($wikis)) {
			$site_ids = array_column($wikis,'site_id');
			if($site_ids){
				$joymesite = new JoymeSite();
				$siteinfos = $joymesite->getSiteInfo($site_ids);
				if($siteinfos){
					$site_names = array_column($siteinfos,'site_name','site_id');
					$site_keys = array_column($siteinfos,'site_key','site_id');
					$site_icons = array_column($siteinfos,'site_icon','site_id');
					$yes_editcounts = array_column($siteinfos,'yes_editcount','site_id');
				}
				$joymewikiuser = new  JoymeWikiUser();
				$offercounts = $joymewikiuser->getUserSiteOfferCount($this->user_id,$site_ids);
				if($offercounts){
					$offer_counts = array_column($offercounts,'offer_count','site_id');
				}
			}

			foreach ($wikis as $contributeWiki) {
				if(isset($site_keys[$contributeWiki['site_id']])
					&& $site_keys[$contributeWiki['site_id']]
				){
					$site_key = $site_keys[$contributeWiki['site_id']];
				}else{
					$site_key = '';
				}

				$output .= '<li class="col-sm-4">
								<a href="/'.$site_key.'/首页" target="_blank">
									<cite>';
				if(isset($site_icons[$contributeWiki['site_id']])
					&& $site_icons[$contributeWiki['site_id']]
				){
					$output .= '<img src="'.$site_icons[$contributeWiki['site_id']].'" alt="">';
				}else{
					$output .= '<img src="" alt="">';
				}

				$output .= '
									</cite>
									<span>';
				//wiki名称
				if(isset($site_names[$contributeWiki['site_id']])
					&& $site_names[$contributeWiki['site_id']]
				){
					$output .= '<font>'.$site_names[$contributeWiki['site_id']].'</font>';
				}else{
					$output .= '<font></font>';
				}

				if(isset($offer_counts[$contributeWiki['site_id']])
					&& $offer_counts[$contributeWiki['site_id']]
				){
					$output .= '<b>贡献总数：'.$offer_counts[$contributeWiki['site_id']].'  </b>';
				}else{
					$output .= '<b>贡献总数：0  </b>';
				}

				if(isset($yes_editcounts[$contributeWiki['site_id']])
					&& $yes_editcounts[$contributeWiki['site_id']]
				){
					$output .= '<b>昨日编辑：'.$yes_editcounts[$contributeWiki['site_id']].'  </b>';
				}else{
					$output .= '<b>昨日编辑：0  </b>';
				}

				$output .= '
									</span>
								</a>
							</li>';
			}
		}
		return $output;
	}


	public function displayFollowWikis($wikis){
		$output = '';

		if (!empty($wikis)) {
			$site_ids = array_column($wikis,'site_id');
			if($site_ids){
				$joymesite = new JoymeSite();
				$siteinfos = $joymesite->getSiteInfo($site_ids);
				if($siteinfos){
					$site_names = array_column($siteinfos,'site_name','site_id');
					$site_keys = array_column($siteinfos,'site_key','site_id');
					$site_icons = array_column($siteinfos,'site_icon','site_id');
					$page_counts = array_column($siteinfos,'page_count','site_id');
					$edit_counts = array_column($siteinfos,'edit_count','site_id');
					$yes_editcounts = array_column($siteinfos,'yes_editcount','site_id');
				}
			}

			foreach ($wikis as $followWiki) {
				if(isset($site_keys[$followWiki['site_id']])
					&& $site_keys[$followWiki['site_id']]
				){
					$site_key = $site_keys[$followWiki['site_id']];
				}else{
					$site_key = '';
				}

				$output .= '<li class="col-sm-4">
								<a href="/'.$site_key.'/首页"  target="_blank">
									<cite>';
				if(isset($site_icons[$followWiki['site_id']])
					&& $site_icons[$followWiki['site_id']]
				){
					$output .= '<img src="'.$site_icons[$followWiki['site_id']].'" alt="">';
				}else{
					$output .= '<img src="" alt="">';
				}
				$output .= '	</cite>
									<span>';
				//wiki名称
				if(isset($site_names[$followWiki['site_id']])
					&& $site_names[$followWiki['site_id']]
				){
					$output .= '<font>'.$site_names[$followWiki['site_id']].'</font>';
				}else{
					$output .= '<font></font>';
				}
				//页面总数量
				if(isset($page_counts[$followWiki['site_id']])
					&& $page_counts[$followWiki['site_id']]
				){
					$output .= '<b>页面总数量：'.$page_counts[$followWiki['site_id']].' </b>';
				}else{
					$output .= '<b>页面总数量：0 </b>';
				}

				if(isset($yes_editcounts[$followWiki['site_id']])
					&& $yes_editcounts[$followWiki['site_id']]
				){
					$output .= '<b>昨日编辑：'.$yes_editcounts[$followWiki['site_id']].'  </b>';
				}else{
					$output .= '<b>昨日编辑：0  </b>';
				}

				$output .= '
									</span>
								</a>
							</li>';
			}
		}

		return $output;
	}
	
	public function displayUserActivitys($activitys){
		$output = '';
		if($activitys){
			foreach ($activitys as $item){
				$output .= '<li>
							<p>
								'.$item['content'].'
								<b class="time-stamp">' . date('Y年m月d日 H:i', $item['add_time']) . '</b>
							</p>
						</li>';
			}
		}

		return $output;
	}

	public function displayFriendActivitys($friendsactivitys,$usericons,$usernicks){
		$output = '';

		if($friendsactivitys){
			foreach ($friendsactivitys as $friendsactivity){
				$output .= '<li class="fn-clear">
							<div class="user-img">
								 <cite>';
				if(isset($usericons[$friendsactivity['user_id']])
					&&$usericons[$friendsactivity['user_id']]
					&&isset($usernicks[$friendsactivity['user_id']])
					&&$usernicks[$friendsactivity['user_id']]
				){
					$output .= '<a href="/home/用户:'.$usernicks[$friendsactivity['user_id']].'" target="_blank"><img src="'.$usericons[$friendsactivity['user_id']].'" alt="img"></a><span>'.$usernicks[$friendsactivity['user_id']].'</span>';
				}else{
					$output .= '<img src="" alt="img">';
				}
				$output .= '
								</cite>
							</div>
							<div class="col-sm-12 user-text">
								<p ><i class="user-name">';
				if(isset($usernicks[$friendsactivity['user_id']])
					&&$usernicks[$friendsactivity['user_id']]
				){
					$output .= $usernicks[$friendsactivity['user_id']];
				}
				$output .= '</i>'.$friendsactivity['content'].'
								<b class="time-stamp">'.date('Y年m月d日 H:i',$friendsactivity['add_time']).'</b></p>
							</div>
						</li>';

			}
		}
		return $output;
	}

	static public function showFormatNumber($num){
		if($num > 999999){
			return round($num/10000,1)."w";
		}else{
			return $num;
		}
	}

}
