jQuery(document).ready(function () {

    var usercenter = {
        validCode: true
    };

    function sendregverifycode(mobile, lsmresponse , sendtype) {
        if (!mobile) {
            if (sendtype == "upnewmobile") {
                mw.ugcwikiutil.msgDialog('手机号不能为空');
            }
            return false;
        } else {
            jQuery.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfUserLSMSiteVerifyRegSendCode',
                    rsargs: [mobile , lsmresponse]
                },
                function (data) {
                    LUOCAPTCHA.reset();
                    var res = jQuery.parseJSON(data);
                    if (res.rs != '1') {
                        if (sendtype == "upnewmobile") {
                            mw.ugcwikiutil.msgDialog(res.data);
                        }
                        return false;
                    }
                }
            );
        }
    }

    jQuery('.sendVerifyRegMobileCode').on('click', function () {
        var code = $(this);
        usercenter.sendtype = $(this).data('sendtype');
        if (usercenter.sendtype == 'upnewmobile') {
            usercenter.sendmobile = jQuery("#newuptel").val();
            if (!usercenter.sendmobile) {
                mw.ugcwikiutil.msgDialog('手机号不能为空');
                return false;
            }
            if (!mw.ugcwikiutil.checkmobile(usercenter.sendmobile)) {
                mw.ugcwikiutil.msgDialog('手机号格式不正确');
                return false;
            }
            usercenter.lsmresponse = jQuery("#lsmresponse").val();
            if (!usercenter.lsmresponse) {
                mw.ugcwikiutil.msgDialog('校验值不能为空');
                return false;
            }
        } else if (usercenter.sendtype == 'bindmobile') {
            usercenter.sendmobile = jQuery("#bmtel").val();
            if (!usercenter.sendmobile) {
                mw.ugcwikiutil.msgDialog('手机号不能为空');
                return false;
            }
            if (!mw.ugcwikiutil.checkmobile(usercenter.sendmobile)) {
                mw.ugcwikiutil.msgDialog('手机号格式不正确');
                return false;
            }
            usercenter.lsmresponse = jQuery("#lsmresponse").val();
            if (!usercenter.lsmresponse) {
                mw.ugcwikiutil.msgDialog('校验值不能为空');
                return false;
            }
        }

        code.attr('disabled', true);
        code.addClass('on');

        var time = 60;
        usercenter.validCode = true;
        if (usercenter.validCode) {
            usercenter.validCode = false;
            sendregverifycode(usercenter.sendmobile, usercenter.lsmresponse , usercenter.sendtype);
            mw.msgcodetime = setInterval(function () {
                time--;
                code.html("重新发送(" + time + ")");
                if (time == 0) {
                    clearInterval(mw.msgcodetime);
                    code.html("发送验证码");
                    usercenter.validCode = true;
                    code.removeClass('on');
                    code.attr('disabled', false);
                }
            }, 1000);
        }
        return false;
    });

    function sendverifycode(mobile, lsmresponse , sendtype) {
        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserLSMSiteVerifySendCode',
                rsargs: [mobile,lsmresponse]
            },
            function (data) {
                LUOCAPTCHA.reset();
                var res = jQuery.parseJSON(data);
                console.log(res);
                if (res.rs != '1') {
                    mw.ugcwikiutil.msgDialog(res.data);
                    return false;
                }
            }
        );
    }

    jQuery('.sendVerifyMobileCode').on('click', function () {
        var mobileid = $(this).data('mobileid');
        var sendmobile = 'isempty';
        if (mobileid) {
            sendmobile = jQuery('#' + mobileid).val();
        }

        usercenter.sendtype = $(this).data('sendtype');
        usercenter.lsmresponse = jQuery("#lsmresponse").val();
        if (!usercenter.lsmresponse) {
            mw.ugcwikiutil.msgDialog('校验值不能为空');
            return false;
        }

        var code = $(this);
        code.attr('disabled', true);
        code.addClass('on');

        var time = 60;
        usercenter.validCode = true;
        if (usercenter.validCode) {
            usercenter.validCode = false;
            sendverifycode(sendmobile, usercenter.lsmresponse , usercenter.sendtype);
            mw.msgcodetime = setInterval(function () {
                time--;
                code.html("重新发送(" + time + ")");
                if (time == 0) {
                    clearInterval(mw.msgcodetime);
                    code.html("发送验证码");
                    usercenter.validCode = true;
                    code.removeClass('on');
                    code.attr('disabled', false);
                }
            }, 1000);
        }
        return false;
    });

    mw.joymesiteuserfollow = function () {

        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        var that = $('#joymesiteuserfollow');
        if ($('#joymesiteuserfollow').hasClass('gz-done')) {
            mw.ugcwikiutil.confirmDialog('确认取消关注吗？',function (action) {
                if(action=="accept"){
                    $.post(
                        mediaWiki.util.wikiScript(), {
                            action: 'ajax',
                            rs: 'wfUserCancelSiteFollow',
                            rsargs: []
                        },
                        function (data) {
                            var res = jQuery.parseJSON(data);
                            console.log(res,res.rs == '1');
                            if (res.rs == '1') {
                                that.addClass("usersitefollow");
                                that.removeClass("gz-done");
                                $('#joyme_wiki_fillow_info').text(parseInt($('#joyme_wiki_fillow_info').text()) - 1)
                                mw.ugcwikiutil.closeDialog();
                            } else {
                                mw.ugcwikiutil.msgDialog(res.data);
                                return false;
                            }
                        }
                    );
                }
            });
        } else {
            $.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfUserSiteFollow',
                    rsargs: []
                },
                function (data) {
                    var res = jQuery.parseJSON(data);
                   

                    if (res.rs == '1') {
                        that.removeClass("usersitefollow");
                        that.addClass("gz-done");
                        $('#joyme_wiki_fillow_info').text(parseInt($('#joyme_wiki_fillow_info').text()) + 1)
                    } else {
                        mw.ugcwikiutil.msgDialog(res.data);
                        return false;
                    }
                }
            );
        }
    };

    mw.joymesiteuserfollow_ygz = function () {
        mw.ugcwikiutil.msgDialog('管理员不可取消关注');
        return false;
    };
    /*
    if (window.wgWikiname != 'home') {

        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfSiteFollowStatus',
                rsargs: []
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                var splits = res.data.split("|");
                if (res.rs == 1) {
                    $('#joyme_site_follow_status').append(' <a id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow_ygz()"></a>');
                } else if (res.rs == 3 || res.rs == 2) {
                    $('#joyme_site_follow_status').append(' <a id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow()"></a>');
                } else {
                    $('#joyme_site_follow_status').append(' <a id="joymesiteuserfollow" href="javascript:;" class="sf-gz usersitefollow" onclick="mw.joymesiteuserfollow()"></a>');
                }
                $('#joyme_wiki_word_info').html(splits[0]);
                $('#joyme_wiki_fillow_info').html(splits[1]);
            }
        );
    }*/
});