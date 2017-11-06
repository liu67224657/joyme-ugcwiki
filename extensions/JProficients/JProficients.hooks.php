<?php
/**
 * Description:JProficients钩子
 * Author: gradydong
 * Date: 2016/12/22
 * Time: 11:17
 * Copyright: Joyme.com
 */
class JProficientsHooks {

    /**
     * Registers the <jproficients> tag with the Parser.
     *
     * @param Parser $parser
     * @return bool
     */
    public static function onParserFirstCallInit( Parser &$parser ) {
        $parser->setHook( 'jproficients', array( 'JProficientsHooks', 'displayJProficients' ) );
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
    public static function displayJProficients( $input, $args, $parser ) {
        global $wgOut;

        wfProfileIn( __METHOD__ );

        $parser->disableCache();
        // If an unclosed <jproficients> tag is added to a page, the extension will
        // go to an infinite loop...this protects against that condition.
        $parser->setHook( 'jproficients', array( 'JProficientsHooks', 'nonDisplayJProficients' ) );

        $title = $parser->getTitle();
        if ( $title->getArticleID() == 0 ) {
            return self::nonDisplayJProficients( $input, $args, $parser );
        }

        $wgOut->addModuleStyles( 'ext.jproficients.css' );
        $wgOut->addModules( 'ext.jproficients.js' );

        $output = '<div class="int-tj fn-clear pag-hor-20 show-action-box"><h3>认证大神<cite class="change-icon fn-right jproficients-change"><i></i>换一换</cite></h3><ul class="int-tj-list" id="jproficients-lists"></ul><input type="hidden" name="jproficients-pagenum" id="jproficients-pagenum" value="1"></div>';

        wfProfileOut( __METHOD__ );

        return $output;
    }

    public static function nonDisplayJProficients( $input, $args, $parser ) {
        $attr = array();

        foreach ( $args as $name => $value ) {
            $attr[] = htmlspecialchars( $name ) . '="' . htmlspecialchars( $value ) . '"';
        }

        $output = '&lt;jproficients';
        if ( count( $attr ) > 0 ) {
            $output .= ' ' . implode( ' ', $attr );
        }

        if ( !is_null( $input ) ) {
            $output .= '&gt;' . htmlspecialchars( $input ) . '&lt;/jproficients&gt;';
        } else {
            $output .= ' /&gt;';
        }

        return $output;
    }
}