<?php
/**
 * Description:初心者钩子
 * Author: gradydong
 * Date: 2016/12/22
 * Time: 11:17
 * Copyright: Joyme.com
 */
class JNovicesHooks {

    /**
     * Registers the <jnovices> tag with the Parser.
     *
     * @param Parser $parser
     * @return bool
     */
    public static function onParserFirstCallInit( Parser &$parser ) {
        $parser->setHook( 'jnovices', array( 'JNovicesHooks', 'displayJNovices' ) );
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
    public static function displayJNovices( $input, $args, $parser ) {
        global $wgOut;

        wfProfileIn( __METHOD__ );

        $parser->disableCache();
        // If an unclosed <jnovices> tag is added to a page, the extension will
        // go to an infinite loop...this protects against that condition.
        $parser->setHook( 'jnovices', array( 'JNovicesHooks', 'nonDisplayJNovices' ) );

        $title = $parser->getTitle();
        if ( $title->getArticleID() == 0 ) {
            return self::nonDisplayJNovices( $input, $args, $parser );
        }

        $wgOut->addModuleStyles( 'ext.jnovices.css' );
        $wgOut->addModules( 'ext.jnovices.js' );

        $output = '<div class="int-tj fn-clear pag-hor-20 show-action-box"><h3>初心者<cite class="change-icon fn-right jnovices-change"><i></i>换一换</cite></h3><ul class="int-tj-list" id="jnovices-lists"></ul><input type="hidden" name="jnovices-pagenum" id="jnovices-pagenum" value="1"></div>';

        wfProfileOut( __METHOD__ );

        return $output;
    }

    public static function nonDisplayJNovices( $input, $args, $parser ) {
        $attr = array();

        foreach ( $args as $name => $value ) {
            $attr[] = htmlspecialchars( $name ) . '="' . htmlspecialchars( $value ) . '"';
        }

        $output = '&lt;jnovices';
        if ( count( $attr ) > 0 ) {
            $output .= ' ' . implode( ' ', $attr );
        }

        if ( !is_null( $input ) ) {
            $output .= '&gt;' . htmlspecialchars( $input ) . '&lt;/jnovices&gt;';
        } else {
            $output .= ' /&gt;';
        }

        return $output;
    }
}