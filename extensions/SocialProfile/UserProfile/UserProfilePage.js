/**
 * JavaScript functions used by UserProfile
 */
var replaceID;
var UserProfilePage = {
    posted: 0,
    numReplaces: 0,
    replaceID: 0,
    replaceSrc: '',
    oldHtml: '',

    sendMessage: function () {
        var userTo = decodeURIComponent(mediaWiki.config.get('wgTitle')), //document.getElementById( 'user_name_to' ).value;
            encMsg = encodeURIComponent(document.getElementById('message').value),
            msgType = document.getElementById('message_type').value;
        if (document.getElementById('message').value && !UserProfilePage.posted) {
            UserProfilePage.posted = 1;
            jQuery.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfSendBoardMessage',
                    rsargs: [userTo, encMsg, msgType, 10]
                },
                function (data) {
                    jQuery('#user-page-board').html(data);
                    UserProfilePage.posted = 0;
                    jQuery('#message').text('');
                }
            );
        }
    },

    deleteMessage: function (id) {
        if (window.confirm('Are you sure you want to delete this message?')) {
            jQuery.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfDeleteBoardMessage',
                    rsargs: [id]
                },
                function () {
                    //window.location.reload();
                    // 1st parent = span.user-board-red
                    // 2nd parent = div.user-board-message-links
                    // 3rd parent = div.user-board-message = the container of a msg
                    jQuery('[data-message-id="' + id + '"]').parent().parent().parent().hide(100);
                }
            );
        }
    },

    showUploadFrame: function () {
        document.getElementById('upload-container').style.display = 'block';
        document.getElementById('upload-container').style.visibility = 'visible';
    },

    uploadError: function (message) {
        document.getElementById('mini-gallery-' + replaceID).innerHTML = UserProfilePage.oldHtml;
        document.getElementById('upload-frame-errors').innerHTML = message;
        document.getElementById('imageUpload-frame').src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75';

        document.getElementById('upload-container').style.display = 'block';
        document.getElementById('upload-container').style.visibility = 'visible';
    },

    textError: function (message) {
        document.getElementById('upload-frame-errors').innerHTML = message;
        document.getElementById('upload-frame-errors').style.display = 'block';
        document.getElementById('upload-frame-errors').style.visibility = 'visible';
    },

    completeImageUpload: function () {
        document.getElementById('upload-frame-errors').style.display = 'none';
        document.getElementById('upload-frame-errors').style.visibility = 'hidden';
        document.getElementById('upload-frame-errors').innerHTML = '';
        UserProfilePage.oldHtml = document.getElementById('mini-gallery-' + UserProfilePage.replaceID).innerHTML;

        for (var x = 7; x > 0; x--) {
            document.getElementById('mini-gallery-' + ( x )).innerHTML =
                document.getElementById('mini-gallery-' + ( x - 1 )).innerHTML.replace('slideShowLink(' + ( x - 1 ) + ')', 'slideShowLink(' + ( x ) + ')');
        }
        document.getElementById('mini-gallery-0').innerHTML =
            '<a><img height="75" width="75" src="' +
            mediaWiki.config.get('wgExtensionAssetsPath') +
            '/SocialProfile/images/ajax-loader-white.gif" alt="" /></a>';

        if (document.getElementById('no-pictures-containers')) {
            document.getElementById('no-pictures-containers').style.display = 'none';
            document.getElementById('no-pictures-containers').style.visibility = 'hidden';
        }
        document.getElementById('pictures-containers').style.display = 'block';
        document.getElementById('pictures-containers').style.visibility = 'visible';
    },

    uploadComplete: function (imgSrc, imgName) {
        UserProfilePage.replaceSrc = imgSrc;

        document.getElementById('upload-frame-errors').innerHTML = '';

        //document.getElementById( 'imageUpload-frame' ).onload = function() {
        // var idOffset = -1 - UserProfilePage.numReplaces;
        var __image_prefix;
        //$D.addClass( 'mini-gallery-0', 'mini-gallery' );
        //document.getElementById('mini-gallery-0').innerHTML = '<a href=\"javascript:slideShowLink(' + idOffset + ')\">' + UserProfilePage.replaceSrc + '</a>';
        document.getElementById('mini-gallery-0').innerHTML = '<a href=\"' + __image_prefix + imgName + '\">' + UserProfilePage.replaceSrc + '</a>';

        //UserProfilePage.replaceID = ( UserProfilePage.replaceID == 7 ) ? 0 : ( UserProfilePage.replaceID + 1 );
        UserProfilePage.numReplaces += 1;
        //}
        //if ( document.getElementById( 'imageUpload-frame' ).captureEvents ) document.getElementById( 'imageUpload-frame' ).captureEvents( Event.LOAD );

        document.getElementById('imageUpload-frame').src = 'index.php?title=Special:MiniAjaxUpload&wpThumbWidth=75&extra=' + UserProfilePage.numReplaces;
    },

    slideShowLink: function (id) {
        //window.location = 'index.php?title=Special:UserSlideShow&user=' + __slideshow_user + '&picture=' + ( numReplaces + id );
        window.location = 'Image:' + id;
    },

    doHover: function (divID) {
        document.getElementById(divID).style.backgroundColor = '#4B9AF6';
    },

    endHover: function (divID) {
        document.getElementById(divID).style.backgroundColor = '';
    }
};


jQuery(document).ready(function () {
    // "Send message" button on (other users') profile pages
    jQuery('div.user-page-message-box-button input[type="button"]').on('click', function () {
        UserProfilePage.sendMessage();
    });

    // Board messages' "Delete" link
    jQuery('span.user-board-red a').on('click', function () {
        UserProfilePage.deleteMessage(jQuery(this).data('message-id'));
    });

    var LoadWikiMore = {
        init: function (loadBox, loadmore, action, page, uid) {
            LoadWikiMore.loadComment(loadBox, loadmore, action, page, uid);
        },
        loadComment: function (className, loadmore, action, page, uid) {
            jQuery.post(
                mw.util.wikiScript(), {
                    action: 'ajax',
                    rs: action,
                    rsargs: [page, uid]
                },
                function (data) {
                    var res = jQuery.parseJSON(data);
                    if (res.rs >= '1') {
                        $('.' + className).append(res.data.html);
                        if (res.data.restcount == 0) {
                            $('#' + loadmore).remove();
                        } else {
                            $('#' + loadmore).data('page', res.rs);
                            $('#' + loadmore).attr('disabled', false);
                        }
                    } else {
                        mw.ugcwikiutil.msgDialog(res.data.html);
                        return false;
                    }
                }
            );
        }
    };

    function userOtherFollow(uid, fid, that) {

        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserUserFollowsResponse',
                rsargs: [uid, fid]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.success) {
                    that.html('已关注');
                    that.removeClass("user-other-follow");
                    var followCount = jQuery(".followcount").html();
                    followCount = parseInt(followCount);
                    jQuery(".followcount").html(followCount + 1);
                } else {
                    mw.ugcwikiutil.msgDialog(res.message);
                    return false;
                }
            }
        );

    }

    function userUserFollow(uid, fid) {
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserUserFollowsResponse',
                rsargs: [uid, fid]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.success) {
                    location.reload();
                } else {
                    mw.ugcwikiutil.msgDialog(res.message);
                    return false;
                }
            }
        );
    }

    jQuery('#managewikimore').on('click', function () {
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        $(this).attr('disabled', true);
        LoadWikiMore.init('manage-item', 'managewikimore', 'wfUserManageWikis', $(this).data('page'), $(this).data('uid'));
    });

    jQuery('#contributewikimore').on('click', function () {
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        $(this).attr('disabled', true);
        LoadWikiMore.init('contribute-item', 'contributewikimore', 'wfUserContributeWikis', $(this).data('page'), $(this).data('uid'));
    });

    jQuery('#followwikimore').on('click', function () {
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        $(this).attr('disabled', true);
        LoadWikiMore.init('follow-item', 'followwikimore', 'wfUserFollowWikis', $(this).data('page'), $(this).data('uid'));
    });

    jQuery('#useractivitymore').on('click', function () {
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        $(this).attr('disabled', true);
        LoadWikiMore.init('my-trends-list', 'useractivitymore', 'wfUserActivitys', $(this).data('page'), $(this).data('uid'));
    });

    jQuery('#friendactivitymore').on('click', function () {
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        $(this).attr('disabled', true);
        LoadWikiMore.init('friend-trends-list', 'friendactivitymore', 'wfFriendActivitys', $(this).data('page'), $(this).data('uid'));
    });

    $('body').on('click', '.user-other-follow', function () {
        var that = $(this);

        if (mw.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }

        var friend_id = that.attr("data-uid");
        userOtherFollow(
            mw.config.get('wgUserId'),
            friend_id,
            that
        );
    });
    /*$('body').on('click', '.user-user-follow', function () {
        var that = $(this);

        if (mw.config.get('wgUserName') == null) {
            mw.loginbox.login();
            return false;
        }

        var friend_id = that.attr("data-uid");
        userUserFollow(
            mw.config.get('wgUserId'),
            friend_id,
            that
        );
    });*/

    jQuery('.user-nologin').on('click', function () {
        // mw.loginbox.login();
        loginDiv();
        return false;
    });

});
