/*global $, mw, useAjax*/

var ajaxpollTmp;

function ajaxpollInit(id){

	var uri = mw.util.wikiScript() + '?action=ajax';
	var func_name = "AJAXPoll::ajaxpollinfo";
	if ( uri.indexOf( '?' ) === -1 ) {
		uri = uri + '?rs=' + encodeURIComponent( func_name );
	} else {
		uri = uri + '&rs=' + encodeURIComponent( func_name );
	}
	uri = uri + '&rsargs[]=' + id;

	if(uri.indexOf(location.hostname) == -1 || window.location.href.indexOf('/wiki/') == -1){

		$.ajax({
			url:uri,
			type:'get',
			 async: false,
			 dataType:"jsonp",
			 jsonpCallback:"ajaxpollquerycallback"+id,
			 success: function (req) {
		            var resMsg = req[0];
		            $("#ajaxpoll-container-"+id)[0].innerHTML = resMsg;
			 },
			 error: function () {
	            console.log('请求失败，请重新试一下或者刷新页面后再试');
	        }
		});
	}
}


var setupEventHandlers = function () {

	'use strict';
	$( document ).on( 'mouseover', '.ajaxpoll-answer-vote', function () {
		var sp = $( this ).find( 'span' );
		ajaxpollTmp = sp.html();
		sp.text( sp.attr( 'title' ) );
		sp.attr( 'title', '' );
	} );

	$( document ).on( 'mouseout', '.ajaxpoll-answer-vote', function () {
		var sp = $( this ).find( 'span' );
		sp.attr( 'title', sp.text() );
		sp.text( ajaxpollTmp );
	} );

	/* attach click handler */
	$( document ).on( 'click', '.ajaxpoll-answer-name label', function ( event ) {
		var choice = $( this ).parent().parent(), poll, answer, token;
		event.preventDefault();
		event.stopPropagation();
		poll = choice.attr( 'poll' );
		answer = choice.attr( 'answer' );
		token = choice.parent().parent().find( 'input[name="ajaxPollToken"]' ).val();
		choice.find( '.ajaxpoll-hover-vote' ).addClass( 'ajaxpoll-checkevent' );
		choice.find( 'input' ).prop( 'checked', 'checked' );
		$( '#ajaxpoll-ajax-' + poll ).text(mw.message( 'ajaxpoll-submitting' ).text() ).css( 'display', 'inline-block' );
		if ( useAjax ) {
			//pp start
			var uri = mw.util.wikiScript() + '?action=ajax';
			var func_name = "AJAXPoll::submitVote";
			var args = [poll,answer,token];
			if ( uri.indexOf( '?' ) === -1 ) {
				uri = uri + '?rs=' + encodeURIComponent( func_name );
			} else {
				uri = uri + '&rs=' + encodeURIComponent( func_name );
			}
			for (var i = 0; i < args.length; i++ ) {
				uri = uri + '&rsargs[]=' + encodeURIComponent( args[i] );
			}
			//uri = uri + '&rsrnd=' + new Date().getTime();
			var post_data = null;
			//if(uri.indexOf(location.hostname) == -1){
				$.ajax({
					url:uri,
					type:'get',
					 data:post_data,
					 dataType:"jsonp",
					 async: false,
					 jsonpCallback:"ajaxpollquerycallback"+poll,
					 success: function (req) {
				            var resMsg = req[0];
				            $("#ajaxpoll-container-"+poll)[0].innerHTML = resMsg;
					 },
					 error: function () {
			            console.log('请求失败，请重新试一下或者刷新页面后再试');
			        }
				});
				setupEventHandlers();
			/*}else{
				$.get( mw.util.wikiScript(), {
					action: 'ajax',
					rs: 'AJAXPoll::submitVote',
					rsargs: [ poll, answer, token ]
				}, function ( newHTML ) {
					$( '#ajaxpoll-container-' + poll ).html( newHTML );
					setupEventHandlers();
				} );
			}*/
		} else {
			$( '#ajaxpoll-answer-id-' + poll ).submit();
		}
	} );

	$( document).on( 'mouseover','.ajaxpoll-answer-name:not(.ajaxpoll-answer-name-revoke) label', function () {
		$( this ).addClass( 'ajaxpoll-hover-vote' );
	} );
	$( document ).on( 'mouseout', '.ajaxpoll-answer-name:not(.ajaxpoll-answer-name-revoke) label',function () {
		$( this ).removeClass( 'ajaxpoll-hover-vote' );
	} );

	$( document ).on( 'mouseover', '.ajaxpoll-answer-name-revoke label', function () {
		$( this ).addClass( 'ajaxpoll-hover-revoke' );
	} );
	$( document ).on( 'mouseout', '.ajaxpoll-answer-name-revoke label', function () {
		$( this ).removeClass( 'ajaxpoll-hover-revoke' );
	} );
};

$(document).ready(function () {
	$('.ajaxpoll').each(function(i,v){
		if($(v).attr('id').indexOf('ajaxpoll-id-') != -1){
			ajaxpollInit($(v).attr('id').substr(12));
		}
	});
	setupEventHandlers();
});
