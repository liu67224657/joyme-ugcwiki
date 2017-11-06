<?php
/**
 *Description:着迷wiki文章点赞
 *author:Islander
 *date:16:28 2016/6/24
**/

class ShortCommentsHooks {
	/**
	 * Registers the <shortcomments> tag with the Parser.
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'shortcomments', array( 'ShortCommentsHooks', 'displayShortComments' ) );
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
	public static function displayShortComments( $input, $args, $parser ) {
		global $wgOut;

		wfProfileIn( __METHOD__ );

		$parser->disableCache();
		// If an unclosed <shortcomments> tag is added to a page, the extension will
		// go to an infinite loop...this protects against that condition.
		$parser->setHook( 'shortcomments', array( 'ShortCommentsHooks', 'nonDisplayShortComments' ) );

		$title = $parser->getTitle();
		if ( $title->getArticleID() == 0 ) {
			return self::nonDisplayComments( $input, $args, $parser );
		}
		$wgOut->addModuleStyles( 'ext.shortcomments.css' );
		$wgOut->addModules( 'ext.shortcomments.js' );
		
		$shortcommentsPage = new ShortCommentsPage( $wgOut->getTitle()->getArticleID(), $wgOut->getContext() );
		$output = '<div class="dz-detail" id="ShortCommentsBox"><div class="nr-dp"><h3>内容短评</h3>';
		$output .= $shortcommentsPage->shortCommentsList();
		$output .= $shortcommentsPage->shortCommentsForm();
        $output .= '<div class="fn-clear"></div><p></p></div></div><div class="fn-clear"></div>';

		wfProfileOut( __METHOD__ );

		return $output;
	}

	public static function nonDisplayComments( $input, $args, $parser ) {
		$attr = array();

		foreach ( $args as $name => $value ) {
			$attr[] = htmlspecialchars( $name ) . '="' . htmlspecialchars( $value ) . '"';
		}

		$output = '&lt;shortcomments';
		if ( count( $attr ) > 0 ) {
			$output .= ' ' . implode( ' ', $attr );
		}

		if ( !is_null( $input ) ) {
			$output .= '&gt;' . htmlspecialchars( $input ) . '&lt;/shortcomments&gt;';
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