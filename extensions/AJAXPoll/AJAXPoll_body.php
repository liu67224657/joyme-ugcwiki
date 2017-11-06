<?php
/**
 * AJAX Poll class
 * Created by Dariusz Siedlecki, based on the work by Eric David.
 * Licensed under the GFDL.
 *
 * @file
 * @ingroup Extensions
 * @author Dariusz Siedlecki <datrio@gmail.com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @author Thomas Gries
 * @maintainer Thomas Gries
 * @link http://www.mediawiki.org/wiki/Extension:AJAX_Poll Documentation
 */

class AJAXPoll {

	static $poll_type = false;

	static $entime = 'null';

	static $poll_data = array();
	/**
	* Register <poll> tag with the parser
	*
	* @param $parser Object: instance of Parser (not necessarily $wgParser)
	* @return Boolean: true
	*/
	static function onParserInit( $parser ) {
		$parser->setHook( 'poll', array( __CLASS__, 'AJAXPollRender' ) );
		return true;
	}

	# The callback function for converting the input text to HTML output
	static function AJAXPollRender( $input, $args = array(), Parser $parser, $frame ) {

		global $wgUser, $wgRequest, $wgUseAjax;
		//Determine whether have set the picture
		$preg="/\[\[File:(.*)\]\]/";

		$poll_img = null;

		@preg_match_all($preg,$input,$match);

		if(count($match)>0){
			$input = trim( strip_tags(@preg_replace($preg,'',$input)));
			$poll_img = serialize($match[1]);
		}
		if(array_key_exists( 'type' , $args )){
			if( $args['type'] == 'new' ){
				self::$poll_type = true;
			}
		}

		if(array_key_exists( 'endtime' , $args )){
			if( !empty($args['endtime']) && !empty($args['type'])){
				self::$entime = $args['endtime'];
			}
		}

		$parser->disableCache();
		$parser->addTrackingCategory( 'ajaxpoll-tracking-category' );
		$parser->getOutput()->addModules( 'ext.ajaxpoll' );
		$parser->getOutput()->addModuleStyles( 'ext.Ajaxpoll.New.css' );

		if ( $wgUser->getName() == '' ) {
			$userName = $wgRequest->getIP();
		} else {
			$userName = $wgUser->getName();
		}

		// ID of the poll
		if ( isset( $args["id"] ) ) {
			$id = $args["id"];
		} else {
			$id = strtoupper( md5( $input ) );
		}

		// get the input
		$input = $parser->recursiveTagParse( $input, $frame );
		$input = trim( strip_tags( $input ) );
		$lines = explode( "\n", trim( $input ) );

		// compatibility for non-ajax requests - just in case
		if ( !$wgUseAjax ) {
			$responseId = "ajaxpoll-post-id";
			$responseAnswer = "ajaxpoll-post-answer-$id";
			$responseToken = "ajaxPollToken";

			if ( isset( $_POST[$responseId] )
				&& isset( $_POST[$responseAnswer] )
				&& ( $_POST[$responseId] == $id )
				&& isset( $_POST[$responseToken] ) ) {
				self::submitVote( $id, intval( $_POST[$responseAnswer] ), $_POST[$responseToken] );
			}
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin( __METHOD__ );

		/**
		* Register poll in the database
		*/

		$row = $dbw->selectRow(
			array( 'ajaxpoll_info' ),
			array( 'COUNT(poll_id) AS count,poll_img' ),
			array( 'poll_id' => $id ),
			__METHOD__
		);

		$showResultsBeforeVoting = null;
		if ( array_key_exists( 'show-results-before-voting', $args ) ) {
			if ( strval( $args['show-results-before-voting'] ) !== '0' ) {
				$showResultsBeforeVoting = '1';
			} else {
				$showResultsBeforeVoting = '0';
			}
		}

		if( empty( $row->count ) ) {
			$dbw->insert(
				'ajaxpoll_info',
				array(
					'poll_id' => $id,
					'poll_show_results_before_voting' => $showResultsBeforeVoting,
					'poll_txt' => $input,
					'poll_img' =>$poll_img,
					'poll_date' => wfTimestampNow(),
				),
				__METHOD__
			);
		}elseif( !empty($row->poll_img) && $row->poll_img !=$poll_img ){
			$dbw->update(
				'ajaxpoll_info',
				array(
					'poll_img' => $poll_img,
				),
				array(
					'poll_id' => $id,
				),
				__METHOD__
			);
		} else {
			$dbw->update(
				'ajaxpoll_info',
				array(
					'poll_show_results_before_voting' => $showResultsBeforeVoting,
				),
				array(
					'poll_id' => $id,
				),
				__METHOD__
			);
		}

		$dbw->commit( __METHOD__ );

		switch( $lines[0] ) {
			case 'STATS':
				$ret = self::buildStats( $id, $userName );
				break;
			default:
				$ret = Html::rawElement( 'div',
					array(
						'id' => 'ajaxpoll-container-' . $id
					),
					self::buildHTML( $id, $userName, $lines )
				);
				break;
		}
		return $ret;
	}

	private static function buildStats( $id, $userName ) {

		$dbr = wfGetDB( DB_SLAVE );
		$dbr->begin( __METHOD__ );

		$res = $dbr->select(
			'ajaxpoll_vote',
			array(
				'COUNT(*)',
				'COUNT(DISTINCT poll_id)',
				'COUNT(DISTINCT poll_user)',
				'TIMEDIFF(NOW(), MAX(poll_date))'
			),
			array(),
			__METHOD__
		);
		$tab = $dbr->fetchRow( $res );

		$clock = explode( ':', $tab[3] );

		if ( $clock[0] == '00' && $clock[1] == '00' ) {
			$x = $clock[2];
			$y = 'second';
		} elseif( $clock[0] == '00' ) {
			$x = $clock[1];
			$y = 'minute';
		} else {
			if ( $clock[0] < 24 ) {
				$x = $clock[0];
				$y = 'hour';
			} else {
				$x = floor( $hr / 24 );
				$y = 'day';
			}
		}

		$clockago = $x . ' ' . $y . ( $x > 1 ? 's' : '' );

		$res = $dbr->select(
			'ajaxpoll_vote',
			'COUNT(*)',
			array( 'DATE_SUB(CURDATE(), INTERVAL 2 DAY) <= poll_date' ),
			__METHOD__
		);
		$tab2 = $dbr->fetchRow( $res );

		$dbr->commit( __METHOD__ );

		return "There are $tab[1] polls and $tab[0] votes given by $tab[2] different people.<br />
The last vote has been given $clockago ago.<br/>
During the last 48 hours, $tab2[0] votes have been given.";
	}
	
	public static function ajaxpollinfo($id){
		global $wgUser;
	
		if ( $wgUser->getName() == '' ) {
			$userName = wfGetIP();
		} else {
			$userName = $wgUser->getName();
		}
	
		return self::buildHTML( $id, $userName );
	}


	public static function submitVote( $id, $answer, $token ,$type = false , $endtime = null) {

		global $wgUser, $wgRequest;

		if($type){
			self::$poll_type = true;
		}

		if($endtime){
			self::$entime = $endtime;
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin( __METHOD__ );

		if ( $wgUser->getName() == '' ) {
			$userName = $wgRequest->getIP();
		} else {
			$userName = $wgUser->getName();
		}

		if ( !$wgUser->matchEditToken( $token, $id ) ) {
			$pollContainerText = 'ajaxpoll-error-csrf-wrong-token';
			return self::buildHTML( $id, $userName, '', $pollContainerText );
		}

		if ( !$wgUser->isAllowed( 'ajaxpoll-vote' ) || $wgUser->isAllowed( 'bot' ) ) {
			return self::buildHTML( $id, $userName );
		}

		if ( $answer != 0 ) {

			$answer = ++$answer;

			$q = $dbw->select(
				'ajaxpoll_vote',
				'COUNT(*) AS count',
				array(
					'poll_id' => $id,
					'poll_user' => $userName
				),
				__METHOD__
			);
			$row = $dbw->fetchRow( $q );

			if ( $row['count'] > 0 ) {

				$updateQuery = $dbw->update(
					'ajaxpoll_vote',
					array(
						'poll_answer' => $answer,
						'poll_date' => wfTimestampNow()
					),
					array(
						'poll_id' => $id,
						'poll_user' => $userName,
					),
					__METHOD__
				);
				$dbw->commit( __METHOD__ );
				$pollContainerText = ( $updateQuery ) ? 'ajaxpoll-vote-update' : 'ajaxpoll-vote-error';

			} else {

				$insertQuery = $dbw->insert(
					'ajaxpoll_vote',
					array(
						'poll_id' => $id,
						'poll_user' => $userName,
						'poll_ip' => $wgRequest->getIP(),
						'poll_answer' => $answer,
						'poll_date' => wfTimestampNow()
					),
					__METHOD__
				);
				$dbw->commit( __METHOD__ );
				$pollContainerText = ( $insertQuery ) ? 'ajaxpoll-vote-add' : 'ajaxpoll-vote-error';

			}

		} else { // revoking a vote

			$deleteQuery = $dbw->delete(
				'ajaxpoll_vote',
				array(
					'poll_id' => $id,
					'poll_user' => $userName,
				),
				__METHOD__
			);
			$dbw->commit( __METHOD__ );
			$pollContainerText = ( $deleteQuery ) ? 'ajaxpoll-vote-revoked' : 'ajaxpoll-vote-error';
		}
		return self::buildHTML( $id, $userName, '', $pollContainerText );
	}

	private static function escapeContent( $string ) {
		return htmlspecialchars( Sanitizer::decodeCharReferences( $string ), ENT_QUOTES );
	}

	private static function buildHTML( $id, $userName, $lines = '', $extra_from_ajax = '' ) {
		global $wgTitle, $wgUser, $wgLang, $wgUseAjax;

		$dbr = wfGetDB( DB_SLAVE );

		$q = $dbr->select(
			'ajaxpoll_info',
			array( 'poll_txt', 'poll_date', 'poll_show_results_before_voting','poll_img'  ),
			array( 'poll_id' => $id ),
			__METHOD__
		);
		$row = $dbr->fetchRow( $q );

		if(@$row['poll_img']){
			self::$poll_data = $row;;
		}
		if ( empty( $lines ) ) {
			$lines = explode( "\n", trim( $row['poll_txt'] ) );
		}

		if ( $row['poll_show_results_before_voting'] !== null ) {
			$showResultsBeforeVoting = ( $row['poll_show_results_before_voting'] === '1' );
		} else {
			$showResultsBeforeVoting = $wgUser->isAllowed( 'ajaxpoll-view-results-before-vote' );
		}

		$start_date = $row['poll_date'];

		$q = $dbr->select(
			'ajaxpoll_vote',
			array( 'poll_answer', 'COUNT(*)' ),
			array( 'poll_id' => $id ),
			__METHOD__,
			array( 'GROUP BY' => 'poll_answer' )
		);

		$poll_result = array();

		while ( $row = $q->fetchRow() ) {
			$poll_result[$row[0]] = $row[1];
		}

		$amountOfVotes = array_sum( $poll_result );

		// Did we vote?
		$userVoted = false;

		$q = $dbr->select(
			'ajaxpoll_vote',
			array( 'poll_answer', 'poll_date' ),
			array(
				'poll_id' => $id,
				'poll_user' => $userName
			),
			__METHOD__
		);

		if ( $row = $dbr->fetchRow( $q ) ) {
			$ts = wfTimestamp( TS_MW, $row[1] );
			$ourLastVoteDate = wfMessage(
				'ajaxpoll-your-vote',
				$lines[$row[0] - 1],
				$wgLang->timeanddate( $ts, true /* adjust? */ ),
				$wgLang->date( $ts, true /* adjust? */ ),
				$wgLang->time( $ts, true /* adjust? */ )
			)->escaped();
			$userVoted = true;
		}

		if( !empty( $extra_from_ajax ) ) {
			$style = 'display:inline-block';
			$ajaxMessage = wfMessage( $extra_from_ajax )->escaped();
		} else {
			$style = 'display:none';
			$ajaxMessage = '';
		}

		// Different messages depending whether the user has already voted
		// or has not voted, or is entitled to vote

		$canRevoke = false;

		if ( $wgUser->isAllowed( 'ajaxpoll-vote' ) ) {

			if ( isset( $row[0] ) ) {
				$message = $ourLastVoteDate;
				$canRevoke = true;
				$lines[] = wfMessage( 'ajaxpoll-revoke-vote' )->text();
			} else {
				if ( $showResultsBeforeVoting ) {
					$message = wfMessage( 'ajaxpoll-no-vote' )->text();
				} else {
					$message = wfMessage( 'ajaxpoll-no-vote-results-after-voting' )->text();
				}
			}
		} else {
			$message = wfMessage( 'ajaxpoll-vote-permission' )->text();
		}

		if ( !$wgUser->isAllowed( 'ajaxpoll-view-results' ) ) {

			$message .= "<br/>" . wfMessage( 'ajaxpoll-view-results-permission' )->text();

		} elseif ( !$userVoted
			&& !$wgUser->isAllowed( 'ajaxpoll-view-results-before-vote' )
			&& !$showResultsBeforeVoting ) {

			if ( $wgUser->isAllowed( 'ajaxpoll-vote' ) ) {
				$message .= "<br/>" . wfMessage( 'ajaxpoll-view-results-before-vote-permission' )->text();
			} else {
				$message .= "<br/>" . wfMessage( 'ajaxpoll-view-results-permission' )->text();
			}
		}

		if(!self::$poll_type){

			if ( is_object( $wgTitle ) ) {

				$ret = Html::element( 'script',
					array(),
					'var useAjax=' . ( !empty($wgUseAjax) ? "true" : "false" ) . ';'
				);

				$ret .= Html::openElement( 'div',
					array(
						'id' => 'ajaxpoll-id-' . $id,
						'class' => 'ajaxpoll'
					)
				);

				$ret .= Html::element( 'div',
					array(
						'id' => 'ajaxpoll-ajax-' . $id,
						'class' => 'ajaxpoll-ajax',
						'style' => $style
					),
					$ajaxMessage
				);

				$ret .= Html::rawElement( 'div',
					array( 'class' => 'ajaxpoll-question' ),
					self::escapeContent( $lines[0] )
				);

				$ret .= Html::rawElement( 'div',
					array( 'class' => 'ajaxpoll-misc' ),
					$message
				);

				$ret .= Html::rawElement( 'form',
					array(
						'method' => 'post',
						'action' => $wgTitle->getLocalURL(),
						'id' => 'ajaxpoll-answer-id-' . $id
					),
					Html::element( 'input',
						array(
							'type' => 'hidden',
							'name' => 'ajaxpoll-post-id',
							'value' => $id
						)
					)
				);

				for ( $i = 1; $i < count( $lines ); $i++ ) {

					$vote = !( $canRevoke && ( $i == count( $lines ) - 1 ) );

					// answers are counted from 1 ... n
					// last answer is pseudo-answer for "I want to revoke vote"
					// and becomes answer number 0

					$answer = ( $vote ) ? $i : 0;
					$xid = $id . "-" . $answer;

					if ( ( $amountOfVotes !== 0 ) && ( isset( $poll_result[$i + 1] ) ) ) {
						$pollResult = $poll_result[$i + 1];
						$percent = round( $pollResult * 100 / $amountOfVotes, 2 );
					} else {
						$pollResult = 0;
						$percent = 0;
					}

					// If AJAX is enabled, as it is by default in modern MWs, we can
					// just use sajax library function here for that AJAX-y feel.
					// If not, we'll have to submit the form old-school way...

					$border = ( $percent == 0 ) ? ' border:0;' : '';
					$isOurVote = ( isset( $row[0] ) && ( $row[0] - 1 == $i ) );

					$resultBar = '';

					if ( $wgUser->isAllowed( 'ajaxpoll-view-results' )
						&& ( $showResultsBeforeVoting || ( !$showResultsBeforeVoting && $userVoted ) )
						&& $vote ) {

						$resultBar = Html::rawElement( 'div',
							array(
								'class' => 'ajaxpoll-answer-vote' . ( $isOurVote ? ' ajaxpoll-our-vote' : '' )
							),
							Html::rawElement( 'span',
								array(
									'title' => wfMessage( 'ajaxpoll-percent-votes' )->numParams( $percent )->escaped()
								),
								self::escapeContent( $pollResult )
							) .
							Html::element( 'div',
								array(
									'style' => 'width:' . $percent . '%;' . $border
								)
							)
						);

					}

					if ( $wgUser->isAllowed( 'ajaxpoll-vote' ) ) {

						$ret .= Html::rawElement( 'form',array(),
								Html::rawElement( 'div',
							array(
								'id' => 'ajaxpoll-answer-' . $xid,
								'class' => 'ajaxpoll-answer',
								'poll' => $id,
								'answer' => $answer,
							),
							Html::rawElement( 'div',
								array(
									'class' => 'ajaxpoll-answer-name' . ( $vote ? ' ajaxpoll-answer-name-revoke' : '' )
								),
								Html::rawElement( 'label',
									array( 'for' => 'ajaxpoll-post-answer-' . $xid ),
									Html::element( 'input',
										array(
											'type' => 'radio',
											'id' => 'ajaxpoll-post-answer-' . $xid,
											'name' => 'ajaxpoll-post-answer-' . $id,
											'value' => $answer,
											'checked' => $isOurVote ? "true" : false,
										)
									) .
									self::escapeContent( $lines[$i] )
								)
							) .
							$resultBar
						));

					} else {

						$ret .= Html::rawElement( 'form',array(),
								Html::rawElement( 'div',
							array(
								'id' => 'ajaxpoll-answer-' . $xid,
								'class' => 'ajaxpoll-answer',
								'poll' => $id,
								'answer' => $answer
							),
							Html::rawElement( 'div',
								array(
									'class' => 'ajaxpoll-answer-name'
								),
								Html::rawElement( 'label',
									array(
										'for' => 'ajaxpoll-post-answer-' . $xid,
										'onclick' => '$("#ajaxpoll-ajax-"' . $xid . '").html("' . wfMessage( 'ajaxpoll-vote-permission' )->text() . '").css("display","block");'
									),
									Html::element( 'input',
										array(
											'disabled' => 'disabled',
											'type' => 'radio',
											'id' => 'ajaxpoll-post-answer-' . $xid,
											'name' => 'ajaxpoll-post-answer-' . $id,
											'value' => $answer
										)
									) .
									self::escapeContent( $lines[$i] )
								)
							),
							$resultBar
						));

					}

				}

				$ret .= Html::Hidden( 'ajaxPollToken', $wgUser->getEditToken( $id ) ) .
					Xml::closeElement( 'form' );

				// Display information about the poll (creation date, amount of votes)

				$pollSummary = wfMessage(
					'ajaxpoll-info',
					$amountOfVotes, // amount of votes
					$wgLang->timeanddate( wfTimestamp( TS_MW, $start_date ), true /* adjust? */ )
				)->text();

				$ret .= Html::rawElement( 'div',
					array(
						'id' => 'ajaxpoll-info-' . $id,
						'class' => 'ajaxpoll-info'
					),
					self::escapeContent( $pollSummary ) .
					Html::element( 'div',
						array( 'class' => 'ajaxpoll-id-info' ),
						'poll-id ' . $id
					)
				);

				$ret .= Html::closeElement( 'div' ) .
					Html::element( 'br' );

			} else {
				$ret = '';
			}
		}else{

			$data = unserialize(self::$poll_data['poll_img']);
			$poll_txts = explode( "\n", trim( self::$poll_data['poll_txt'] ) );
			$listHtml = '';
			$resultHtml = '';
			$click_ret = '';
			$resultBgClass = array(
				'xm-jd',
				'xm-jd xm-jd2',
				'xm-jd xm-jd3',
				'xm-jd xm-jd4'
			);

			$people = intval($amountOfVotes)>=99999?'99999+':$amountOfVotes;

			foreach($data as $k=>$v){

				$subscript = $k+1;
//				$isOurVote = ( isset( $row[0] ) && ( $row[0] - 1 == $subscript ) );
				$vote = !( $canRevoke && ( $subscript == count( $lines ) - 1 ) );
				$answer = ( $vote ) ? $subscript : 0;
//				$xid = $id . "-" . $answer;

				if ( ( $amountOfVotes !== 0 ) && ( isset( $poll_result[$subscript + 1] ) ) ) {
					$pollResult = $poll_result[$subscript + 1];
					$percent = round( $pollResult * 100 / $amountOfVotes, 2 );
				} else {
					$pollResult = 0;
					$percent = 0;
				}

				$rand = array_rand($resultBgClass);
				$class = $resultBgClass[$rand];

				if(!empty(self::$entime) && time()<strtotime(substr(self::$entime,0,10).' 23:59:59')){
					if($userVoted){
						if($subscript==$row[0]-1){
							$button_tip = '<button class="jieshu ajaxpoll_new_submit" value="0" id="ajaxpoll-post-answer-0">已投票|撤销</button>';
						}else{
							$button_tip = '<button class="jieshu ajaxpoll_new_submit" disabled="disabled">已投票</button>';
						}
					}else{
						$button_tip = '<button class="ajaxpoll_new_submit" value="'.$answer.'" id="ajaxpoll-post-answer-' . $subscript.'">投票</button>';
					}
				}else{
					$button_tip = '<button class="jieshu">已结束</button>';
				}

				$substr = self::escapeContent( $poll_txts[$subscript] );

				if(mb_strlen($substr)>=21){
					$substr = mb_substr( $substr,0,25).'...';
				}

				$listHtml.='<dl><dt><img data-bt="'.$subscript.'" src="'.DataSynchronization::imagePath($v,180,135).'" class="cktp-jg-img"/></dt><dd class="tp-con">'.$substr.'</dd><dd>'.$button_tip.'</dd><dd><em>'.self::escapeContent( $pollResult ).'</em>票（<span>'.wfMessage( 'ajaxpoll-percent-votes-end' )->numParams( $percent )->escaped().'</span>）</dd></dl>';

				$resultHtml .= '<span class="xm-bt">'.self::escapeContent( $poll_txts[$subscript] ).'</span><p class="xm-1"><span class="'.$class.'" style="width:'.wfMessage( 'ajaxpoll-percent-votes-end' )->numParams( $percent )->escaped().'"></span><span class="xm-tpl"><em>'.self::escapeContent( $pollResult ).'</em>&nbsp;&nbsp;<em>('.wfMessage( 'ajaxpoll-percent-votes-end' )->numParams( $percent )->escaped().')</em></span></p>';

				$click_ret .= '<div class="pl-fa-mc cktp-jg-tc-'.$subscript.'" >
									<div id="container-scroller">
										<span class="scroller-close-btn" id="cktp-jg-tc" data="'.$subscript.'"></span>
										<div class="image-scroller">
											<img src="'.DataSynchronization::getImagePath($v).'" alt="" class="feature-image"/>
											<div class="preview">
												<img src="'.DataSynchronization::imagePath($v,180,135).'" class="preview-url" alt="" height="180" width="135" />
											</div>
										</div>
									</div>
									</div><div class="web-pl-tc cktp-jg-web-tc-'.$subscript.'" data="'.$subscript.'">
									<div class="web-pl-con">
									 <div class="web-pl-img">
										<img src="'.DataSynchronization::imagePath($v,180,135).'" title="弹出图片title"/>
									<span class="close-btn-pl-m close-btn-cktpjg" data-bt="'.$subscript.'"></span>
									</div>
									</div>
								</div>';
			}



			$ret = $click_ret.'<div class="toupiao">
					<div class="h3-title"><h3>'.wfMessage('ajaxpoll_operation').'</h3></div>
					<div class="dsc"><p>'.self::escapeContent( $lines[0] ).'</p>
					<div class="tp-time">
						<span class="over-time">'.wfMessage('ajaxpoll_end_time') .self::$entime.'</span>
						<span class="active-person">'.wfMessage('ajaxpoll_participate_num').'<em>'.(string)$people.'</em>人</span>
						<span class="vote_result"><a>'.wfMessage('ajaxpoll_see_result').'</a></span>
					</div>
					<div class="tp-info">'.$listHtml.'
						<div class="fn-clear"></div>
					</div>
				</div>
			</div>
			<div class="tc-zz">
				<div class="tpjg-tc">
					<div class="tpjg-cont">
						<p class="chakan">查看投票结果<span class="vote-close-btn"></span></p>
						<div class="jg-jd">'.$resultHtml.'
						</div>
					</div>
				</div>
						</div>';
				$ret .= Html::Hidden( 'ajaxPollToken', $wgUser->getEditToken( $id ),array( 'id'=>'ajaxPollNewToken' ));
				$ret .= Html::Hidden( 'ajaxPollinfo',  $id ,array( 'id'=>'ajaxPollinfo' ));
				$ret .= Html::Hidden( 'ajaxPolltime',  self::$entime ,array( 'id'=>'ajaxPolltime' ));
			}
			if(!empty($_GET['callback']))
				$ret = 'ajaxpollquerycallback'.$id.'(['.json_encode($ret).'])';



			return $ret;
	}

	public static function onLoadExtensionSchemaUpdates( $updater = null ) {
		if ( $updater === null ) {
			// no < 1.17 support
		} else {
			// >= 1.17 support
			$db = $updater->getDB();

			$patchPath = dirname( __FILE__ ) . '/patches/';

			if ( $db->tableExists( 'poll_info' ) ) {

				# poll_info.poll_title field was dropped in AJAXPoll version 1.72
				$updater->dropExtensionField(
					'poll_info',
					'poll_title',
					$patchPath . 'drop-field--poll_info-poll_title.sql'
				);
				$updater->addExtensionTable(
					'ajaxpoll_info',
					$patchPath . 'rename-table--poll_info.sql'
				);

			} else {

				$updater->addExtensionTable(
					'ajaxpoll_info',
					$patchPath . 'create-table--ajaxpoll_info.sql'
				);

			}

			if ( $db->tableExists( 'ajaxpoll_info' ) ) {
				$updater->addExtensionField(
					'ajaxpoll_info',
					'poll_show_results_before_voting',
					$patchPath . 'add-field--ajaxpoll_info-poll_show_results_before_voting.sql'
				);
			}

			if ( $db->tableExists( 'poll_vote' ) ) {

				$updater->addExtensionTable(
					'poll_vote',
					$patchPath . 'rename-table--poll_vote.sql'
				);

			} else {

				$updater->addExtensionTable(
					'ajaxpoll_vote',
					$patchPath . 'create-table--ajaxpoll_vote.sql'
				);

			}

		}

		return true;
	}

} /* class AJAXPoll */


