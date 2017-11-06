<?php
/**
 *Description:着迷wiki文章点赞
 *author:Islander
 *date:16:28 2016/6/24
**/

class ClickLikeHooks {
	/**
	 * Registers the <clicklike> tag with the Parser.
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'clicklike', array( 'ClickLikeHooks', 'displayClickLike' ) );
		return true;
	}

	/**
	 * Callback function for onParserFirstCallInit().
	 *
	 * @param $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string HTML
	 */
	public static function displayClickLike( $input, $args, $parser ) {
		global $wgOut, $wgCommentsSortDescending;

		wfProfileIn( __METHOD__ );

		$parser->disableCache();
		// If an unclosed <clicklike> tag is added to a page, the extension will
		// go to an infinite loop...this protects against that condition.
		$parser->setHook( 'clicklike', array( 'ClickLikeHooks', 'nonDisplayClickLike' ) );

		$title = $parser->getTitle();
		if ( $title->getArticleID() == 0 ) {
			return self::nonDisplayComments( $input, $args, $parser );
		}
		$wgOut->addModuleStyles( 'ext.clicklike.css' );
		$wgOut->addModules( 'ext.clicklike.js' );
		$wgOut->addJsConfigVars( array( 'clicklike' => 1 ) );
		
		$clicklikePage = new ClickLikePage( $wgOut->getTitle()->getArticleID(), $wgOut->getContext() );
		$output = '<div class="dz-icon" id="ClickLike"><span class="dz-num">'.$clicklikePage->displayNum().'</span><b class="dz">点赞</b></div>';
		// $output = '<div id="ClickLike">';
		// $output .= '<button>赞</button>';
		// if ( !wfReadOnly() ) {
			// $output .= '<span>'.$clicklikePage->displayNum().'</span>';
		// } else {
			// $output .= wfMessage( 'clicklike-db-locked' )->parse();
		// }
		// $output .= $clicklikePage->displayData();
		// $output .= '</div>';

		wfProfileOut( __METHOD__ );

		return $output;
	}

	public static function nonDisplayComments( $input, $args, $parser ) {
		$attr = array();

		foreach ( $args as $name => $value ) {
			$attr[] = htmlspecialchars( $name ) . '="' . htmlspecialchars( $value ) . '"';
		}

		$output = '&lt;clicklike';
		if ( count( $attr ) > 0 ) {
			$output .= ' ' . implode( ' ', $attr );
		}

		if ( !is_null( $input ) ) {
			$output .= '&gt;' . htmlspecialchars( $input ) . '&lt;/clicklike&gt;';
		} else {
			$output .= ' /&gt;';
		}

		return $output;
	}

	/**
	 * Adds the three new required database tables into the database when the
	 * user runs /maintenance/update.php (the core database updater script).
	 * 暂时不需要，暂时不需要，暂时不需要
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$dir = __DIR__ . '/sql';

		$dbType = $updater->getDB()->getType();
		// For non-MySQL/MariaDB/SQLite DBMSes, use the appropriately named file
		if ( !in_array( $dbType, array( 'mysql', 'sqlite' ) ) ) {
			$filename = "comments.{$dbType}.sql";
		} else {
			$filename = 'comments.sql';
		}

		$updater->addExtensionUpdate( array( 'addTable', 'Comments', "{$dir}/{$filename}", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'Comments_Vote', "{$dir}/{$filename}", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'Comments_block', "{$dir}/{$filename}", true ) );

		return true;
	}

	/**
	 * For integration with the Renameuser extension.
	 *
	 * @param RenameuserSQL $renameUserSQL
	 * @return bool
	 */
	// public static function onRenameUserSQL( $renameUserSQL ) {
		// $renameUserSQL->tables['Comments'] = array( 'Comment_Username', 'Comment_user_id' );
		// $renameUserSQL->tables['Comments_Vote'] = array( 'Comment_Vote_Username', 'Comment_Vote_user_id' );
		// $renameUserSQL->tables['Comments_block'] = array( 'cb_user_name', 'cb_user_id' );
		// $renameUserSQL->tables['Comments_block'] = array( 'cb_user_name_blocked', 'cb_user_id_blocked' );
		// return true;
	// }
}