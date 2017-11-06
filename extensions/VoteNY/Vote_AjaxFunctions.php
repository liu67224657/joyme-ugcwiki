<?php
/**
 * AJAX functions used by Vote extension.
 */

$wgAjaxExportList[] = 'wfVoteStars';
function wfVoteStars( $voteValue, $pageId ) {
	global $wgUser;

	if ( !$wgUser->isAllowed( 'voteny' ) ) {
		return '';
	}

	$vote = new VoteStars( $pageId );
	if ( $vote->UserAlreadyVoted() ) {
		$vote->delete();
	}
	$vote->insert( $voteValue );

	return $vote->display();
}

$wgAjaxExportList[] = 'wfVoteStarsDelete';
function wfVoteStarsDelete( $pageId ) {
	global $wgUser;

	if ( !$wgUser->isAllowed( 'voteny' ) ) {
		return '';
	}

	$vote = new VoteStars( $pageId );
	$vote->delete();

	return $vote->display();
}

$wgAjaxExportList[] = 'wfGetVoteStars';
function wfGetVoteStars( $pageId ) {

	$vote = new VoteStars( $pageId );
	$display_stars_rating = $vote->getAverageVote();
	
	if(intval($display_stars_rating*10) == 0){
		return '';
	}
	
	$output = '<span>评分:</span>';
	
	$output .= '<span class="xj">'.$vote->displayGetStarsRs( $pageId, $display_stars_rating, false ).'</span>';
	
	$output .= '<span>(' . $display_stars_rating . ')</span>';

	return $output;
}
