<?php

/**
 * Main entry point for the Semantic Result Formats (SRF) extension.
 * https://www.semantic-mediawiki.org/wiki/Semantic_Result_Formats
 *
 * @licence GNU GPL v2 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SRF_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SRF_VERSION', '2.3' );

if ( version_compare( $GLOBALS['wgVersion'], '1.19c', '<' ) ) {
	throw new Exception( 'This version of Semantic Result Formats requires MediaWiki 1.19 or above; use SRF 1.7.x or SRF 1.6.x for older versions.' );
}

if ( !defined( 'SMW_VERSION' ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

if ( ! defined( 'SMW_VERSION' ) ) {
	throw new Exception( 'You need to have Semantic MediaWiki installed in order to use Semantic Result Formats' );
}

$GLOBALS['wgMessagesDirs']['SemanticResultFormats'] = __DIR__ . '/i18n';
$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormats'] = __DIR__ . '/SemanticResultFormats.i18n.php';
$GLOBALS['wgExtensionMessagesFiles']['SemanticResultFormatsMagic'] = __DIR__ . '/SemanticResultFormats.i18n.magic.php';

$GLOBALS['srfgIP'] = __DIR__;

// Require the settings file.
require __DIR__ . '/SemanticResultFormats.settings.php';

// Resource definitions
$GLOBALS['wgResourceModules'] = array_merge( $GLOBALS['wgResourceModules'], include( __DIR__ . "/Resources.php" ) );

$GLOBALS['wgExtensionCredits']['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Semantic Result Formats',
	'version' => SRF_VERSION,
	// At least 14 people have contributed formats to this extension, so
	// it would be prohibitive to list them all in the credits. Instead,
	// the current rule is to list anyone who has created, or contributed
	// significantly to, at least three formats, or the overall extension.
	'author' => array(
		'James Hong Kong',
		'Stephan Gambke',
		'[https://www.mediawiki.org/wiki/User:Jeroen_De_Dauw Jeroen De Dauw]',
		'Yaron Koren',
		'...'
	),
	'url' => 'https://semantic-mediawiki.org/wiki/Semantic_Result_Formats',
	'descriptionmsg' => 'srf-desc',
	'license-name'   => 'GPL-2.0+'
);

$formatDir = __DIR__ . '/formats/';

global $wgAutoloadClasses;

$wgAutoloadClasses += array(
		'SRFArray' => __DIR__ . '/formats/array/SRF_Array.php',
		'SRFBibTeX' => __DIR__ . '/formats/bibtex/SRF_BibTeX.php',
		'SRFBoilerplate' => __DIR__ . '/formats/boilerplate/SRF_Boilerplate.php',
		'SRFCHistoricalDate' => __DIR__ . '/formats/calendar/SRFC_HistoricalDate.php',
		'SRFCalendar' => __DIR__ . '/formats/calendar/SRF_Calendar.php',
		'SRFD3Chart' => __DIR__ . '/formats/d3/SRF_D3Chart.php',
		'SRFDygraphs' => __DIR__ . '/formats/dygraphs/SRF_Dygraphs.php',
		'SRFExhibit' => __DIR__ . '/formats/Exhibit/SRF_Exhibit.php',
		'SRFFiltered' => __DIR__ . '/formats/Filtered/SRF_Filtered.php',
		'SRFGoogleBar' => __DIR__ . '/formats/googlecharts/SRF_GoogleBar.php',
		'SRFGooglePie' => __DIR__ . '/formats/googlecharts/SRF_GooglePie.php',
		'SRFGraph' => __DIR__ . '/formats/graphviz/SRF_Graph.php',
		'SRFHash' => __DIR__ . '/formats/array/SRF_Hash.php',
		'SRFHooks' => __DIR__ . '/SemanticResultFormats.hooks.php',
		'SRFIncoming' => __DIR__ . '/formats/incoming/SRF_Incoming.php',
		'SRFJitGraph' => __DIR__ . '/formats/JitGraph/SRF_JitGraph.php',
		'SRFListWidget' => __DIR__ . '/formats/widget/SRF_ListWidget.php',
		'SRFMath' => __DIR__ . '/formats/math/SRF_Math.php',
		'SRFOutline' => __DIR__ . '/formats/outline/SRF_Outline.php',
		'SRFOutlineItem' => __DIR__ . '/formats/outline/SRF_Outline.php',
		'SRFOutlineTree' => __DIR__ . '/formats/outline/SRF_Outline.php',
		'SRFPageWidget' => __DIR__ . '/formats/widget/SRF_PageWidget.php',
		'SRFParserFunctions' => __DIR__ . '/SemanticResultFormats.parser.php',
		'SRFPloticus' => __DIR__ . '/formats/ploticus/SRF_Ploticus.php',
		'SRFPloticusVBar' => __DIR__ . '/formats/ploticus/SRF_PloticusVBar.php',
		'SRFProcess' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SRFSlideShow' => __DIR__ . '/formats/slideshow/SRF_SlideShow.php',
		'SRFSlideShowApi' => __DIR__ . '/formats/slideshow/SRF_SlideShowApi.php',
		'SRFSparkline' => __DIR__ . '/formats/sparkline/SRF_Sparkline.php',
		'SRFTime' => __DIR__ . '/formats/time/SRF_Time.php',
		'SRFTimeline' => __DIR__ . '/formats/timeline/SRF_Timeline.php',
		'SRFTimeseries' => __DIR__ . '/formats/timeseries/SRF_Timeseries.php',
		'SRFTree' => __DIR__ . '/formats/tree/SRF_Tree.php',
		'SRFTreeElement' => __DIR__ . '/formats/tree/SRF_Tree.php',
		'SRFUtils' => __DIR__ . '/SemanticResultFormats.utils.php',
		'SRFValueRank' => __DIR__ . '/formats/valuerank/SRF_ValueRank.php',
		'SRF\\DataTables' => __DIR__ . '/formats/datatables/DataTables.php',
		'SRF\\EventCalendar' => __DIR__ . '/formats/calendar/EventCalendar.php',
		'SRF\\Gallery' => __DIR__ . '/formats/gallery/Gallery.php',
		'SRF\\MediaPlayer' => __DIR__ . '/formats/media/MediaPlayer.php',
		'SRF\\SRFExcel' => __DIR__ . '/formats/excel/SRF_Excel.php',
		'SRF\\TagCloud' => __DIR__ . '/formats/tagcloud/TagCloud.php',
		'SRF_FF_Distance' => __DIR__ . '/formats/Filtered/filters/SRF_FF_Distance.php',
		'SRF_FF_Value' => __DIR__ . '/formats/Filtered/filters/SRF_FF_Value.php',
		'SRF_FV_Calendar' => __DIR__ . '/formats/Filtered/views/SRF_FV_Calendar.php',
		'SRF_FV_List' => __DIR__ . '/formats/Filtered/views/SRF_FV_List.php',
		'SRF_FV_Table' => __DIR__ . '/formats/Filtered/views/SRF_FV_Table.php',
		'SRF_Filtered_Filter' => __DIR__ . '/formats/Filtered/filters/SRF_Filtered_Filter.php',
		'SRF_Filtered_Item' => __DIR__ . '/formats/Filtered/SRF_Filtered_Item.php',
		'SRF_Filtered_View' => __DIR__ . '/formats/Filtered/views/SRF_Filtered_View.php',
		'SRFiCalendar' => __DIR__ . '/formats/icalendar/SRF_iCalendar.php',
		'SRFjqPlot' => __DIR__ . '/formats/jqplot/SRF_jqPlot.php',
		'SRFjqPlotChart' => __DIR__ . '/formats/jqplot/SRF_jqPlotChart.php',
		'SRFjqPlotSeries' => __DIR__ . '/formats/jqplot/SRF_jqPlotSeries.php',
		'SRFvCard' => __DIR__ . '/formats/vcard/SRF_vCard.php',
		'SRFvCardAddress' => __DIR__ . '/formats/vcard/SRF_vCard.php',
		'SRFvCardEmail' => __DIR__ . '/formats/vcard/SRF_vCard.php',
		'SRFvCardEntry' => __DIR__ . '/formats/vcard/SRF_vCard.php',
		'SRFvCardTel' => __DIR__ . '/formats/vcard/SRF_vCard.php',
		'SequentialEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SplitConditionalOrEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SplitEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SplitExclusiveOrEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SplitParallelEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessEdge' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessElement' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessGraph' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessNode' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessRessource' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'ProcessRole' => __DIR__ . '/formats/graphviz/SRF_Process.php',
		'SMWBibTeXEntry' => __DIR__ . '/formats/bibtex/SRF_BibTeX.php',
    	
);

unset( $formatDir );

global $wgHooks;

// Admin Links hook needs to be called in a delayed way so that it
// will always be called after SMW's Admin Links addition; as of
// SMW 1.9, SMW delays calling all its hook functions.
$wgExtensionFunctions[] = function() {
	$GLOBALS['wgHooks']['AdminLinks'][] = 'SRFHooks::addToAdminLinks';
};

$wgHooks['ParserFirstCallInit'][] = 'SRFParserFunctions::registerFunctions';
$wgHooks['UnitTestsList'][] = 'SRFHooks::registerUnitTests';

$wgHooks['ResourceLoaderTestModules'][] = 'SRFHooks::registerQUnitTests';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'SRFHooks::onResourceLoaderGetConfigVars';

// register API modules
$GLOBALS['wgAPIModules']['ext.srf.slideshow.show'] = 'SRFSlideShowApi';

// User preference
$GLOBALS['wgHooks']['GetPreferences'][] = 'SRFHooks::onGetPreferences';

/**
 * Autoload the query printer classes and associate them with their formats in the $smwgResultFormats array.
 *
 * @since 1.5.2
 */
$GLOBALS['wgExtensionFunctions'][] = function() {
	global $srfgFormats, $smwgResultFormats, $smwgResultAliases;

	$GLOBALS['srfgScriptPath'] = ( $GLOBALS['wgExtensionAssetsPath'] === false ?
			$GLOBALS['wgScriptPath'] . '/extensions' : $GLOBALS['wgExtensionAssetsPath'] ) . '/SemanticResultFormats';

	$formatClasses = array(
		// Assign the Boilerplate class to a format identifier
		// 'boilerplate' => 'SRFBoilerplate',
		'timeline' => 'SRFTimeline',
		'eventline' => 'SRFTimeline',
		'vcard' => 'SRFvCard',
		'icalendar' => 'SRFiCalendar',
		'bibtex' => 'SRFBibTeX',
		'calendar' => 'SRFCalendar',
		'eventcalendar' => 'SRF\EventCalendar',
		'outline' => 'SRFOutline',
		'sum' => 'SRFMath',
		'product' => 'SRFMath',
		'average' => 'SRFMath',
		'min' => 'SRFMath',
		'max' => 'SRFMath',
		'median' => 'SRFMath',
		'exhibit' => 'SRFExhibit',
		'googlebar' => 'SRFGoogleBar',
		'googlepie' => 'SRFGooglePie',
		'jitgraph' => 'SRFJitGraph',
		'jqplotchart' => 'SRFjqPlotChart',
		'jqplotseries' => 'SRFjqPlotSeries',
		'graph' => 'SRFGraph',
		'process' => 'SRFProcess',
		'ploticusvbar' => 'SRFPloticusVBar',
		'gallery' => 'SRF\Gallery',
		'tagcloud' => 'SRF\TagCloud',
		'valuerank' => 'SRFValueRank',
		'array' => 'SRFArray',
		'hash' => 'SRFHash',
		'd3chart' => 'SRFD3Chart',
		'tree' => 'SRFTree',
		'ultree' => 'SRFTree',
		'oltree' => 'SRFTree',
		'filtered' => 'SRFFiltered',
		'latest' => 'SRFTime',
		'earliest' => 'SRFTime',
		'slideshow' => 'SRFSlideShow',
		'timeseries' => 'SRFTimeseries',
		'sparkline' => 'SRFSparkline',
		'listwidget' => 'SRFListWidget',
		'pagewidget' => 'SRFPageWidget',
		'dygraphs' => 'SRFDygraphs',
		'incoming' => 'SRFIncoming',
		'media' => 'SRF\MediaPlayer',
		'excel' => 'SRF\SRFExcel',
		'datatables' => 'SRF\DataTables'
	);

	$formatAliases = array(
		'tagcloud'   => array( 'tag cloud' ),
		'datatables'   => array( 'datatable' ),
		'valuerank'  => array( 'value rank' ),
		'd3chart'    => array( 'd3 chart' ),
		'timeseries' => array ( 'time series' ),
		'jqplotchart' => array( 'jqplot chart', 'jqplotpie', 'jqplotbar' ),
		'jqplotseries' => array( 'jqplot series' ),
	);

	foreach ( $srfgFormats as $format ) {
		if ( array_key_exists( $format, $formatClasses ) ) {
			$smwgResultFormats[$format] = $formatClasses[$format];

			if ( isset( $smwgResultAliases ) && array_key_exists( $format, $formatAliases ) ) {
				$smwgResultAliases[$format] = $formatAliases[$format];
			}
		}
		else {
			wfDebug( "There is no result format class associated with the '$format' format." );
		}
	}
};
