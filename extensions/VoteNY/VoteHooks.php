<?php
/**
 * All hooked functions used by VoteNY extension.
 *
 * @file
 * @ingroup Extensions
 */
class VoteHooks {

	/**
	 * Set up the <vote> parser hook.
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function registerParserHook( &$parser ) {
		$parser->setHook( 'vote', array( 'VoteHooks', 'renderVote' ) );
		return true;
	}

	/**
	 * Callback function for registerParserHook.
	 *
	 * @param string $input User-supplied input, unused
	 * @param array $args User-supplied arguments
	 * @param Parser $parser Instance of Parser, unused
	 * @return string HTML
	 */
	public static function renderVote( $input, $args, $parser ) {
		global $wgOut, $wgUser, $wgRequest;

		wfProfileIn( __METHOD__ );

		// Disable parser cache (sadly we have to do this, because the caching is
		// messing stuff up; we want to show an up-to-date rating instead of old
		// or totally wrong rating, i.e. another page's rating...)
		$parser->disableCache();

		// Add CSS & JS
		// In order for us to do this *here* instead of having to do this in
		// registerParserHook(), we must've disabled parser cache
		$parser->getOutput()->addModuleStyles( 'ext.voteNY.styles' );
		if ( $wgUser->isAllowed( 'voteny' ) ) {
			$parser->getOutput()->addModules( 'ext.voteNY.scripts' );
		}

		$output = null;
		
		$title = empty($args['title'])?'':$args['title'];
		$title = trim(strip_tags($title));
		
		if(empty($title) || mb_strlen($args['title'])>27){
			return 'vote title error';
		}
		
		$id = strtoupper( md5( $title ) );
		
		$vote = new VoteStars( $id );
		
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin( __METHOD__ );
		
		/**
		 * Register voteNY in the database
		*/
		
		$row = $dbw->selectRow(
				array( 'vote_info' ),
				array( 'COUNT(vote_id) AS count' ),
				array( 'vote_id' => $id ),
				__METHOD__
		);
		if( empty( $row->count ) ) {
			$voteDate = date( 'Y-m-d H:i:s' );
			$dbw->insert(
					'vote_info',
					array(
							'vote_id' => $id,
							'vote_title' => $title,
							'vote_date' => $voteDate,
							'vote_ip' => $wgRequest->getIP(),
					),
					__METHOD__
			);
		}
		
		$dbw->commit( __METHOD__ );
		
			
		$output = $vote->display();

		wfProfileOut( __METHOD__ );

		return $output;
	}

	


}
