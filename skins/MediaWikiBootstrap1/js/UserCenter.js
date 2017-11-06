jQuery(document).ready(function () {

    var usercenter = {
        validCode: true
    };
/*

    jQuery('#user_login').on('click', function () {
        usercenter.logintel = jQuery('#logintel').val();
        if (!usercenter.logintel) {
            jQuery('#login_tip').html("*您还没有输入账号");
            return false;
        }

        usercenter.loginpassword = jQuery('#loginpassword').val();
        if (!usercenter.loginpassword) {
            jQuery('#login_tip').html("*您还没有输入密码");
            return false;
        }
        if (document.getElementById('check-1').checked) {
            usercenter.keeptime = 'yes';
        } else {
            usercenter.keeptime = 'no';
        }
        jQuery('#login_tip').empty();
        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserCenterUserLogin',
                rsargs: [usercenter.logintel, usercenter.loginpassword, usercenter.keeptime]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                console.log(res);
                if (res.rs == '1') {
                    location.reload();
                }
                else {
                    jQuery('#login_tip').html(res.data);
                    return false;
                }
            }
        );

    });
    jQuery('#regtel').on('blur', function () {
        usercenter.regtel = jQuery('#regtel').val();
        if (!usercenter.regtel) {
            jQuery('#regtel_tip').html("*您还没有输入手机号");
            jQuery('.sendMobileCode').attr('disabled', true);
            jQuery('.sendMobileCode').addClass('on');
            return false;
        }

        if (!mw.ugcwikiutil.checkmobile(usercenter.regtel)) {
            jQuery('#regtel_tip').html("*手机号格式不正确");
            jQuery('.sendMobileCode').attr('disabled', true);
            jQuery('.sendMobileCode').addClass('on');
            return false;
        }

        if (usercenter.validCode) {
            jQuery('.sendRegMobileCode').removeClass('on');
            jQuery('.sendRegMobileCode').attr('disabled', false);
        }
        jQuery('#regtel_tip').empty();
    });
    jQuery('#regcode').on('blur', function () {
        usercenter.regcode = jQuery('#regcode').val();
        if (!usercenter.regcode) {
            jQuery('#regcode_tip').html("*您还没有输入验证码");
            return false;
        }
        jQuery('#regcode_tip').empty();
    });
    jQuery('#regpassword').on('blur', function () {
        usercenter.regpassword = jQuery('#regpassword').val();
        usercenter.regrepassword = jQuery('#regrepassword').val();
        if (!usercenter.regpassword) {
            jQuery('#regpassword_tip').html("*您还没有输入密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.regpassword)) {
            jQuery('#regpassword_tip').html("*密码不能含有空格");
            return false;
        }
        if (usercenter.regpassword.length < 6) {
            jQuery('#regpassword_tip').html("*您输入的密码长度过短");
            return false;
        }
        if (usercenter.regrepassword) {
            if (usercenter.regpassword != usercenter.regrepassword) {
                jQuery('#regpassword_tip').html("*您两次输入密码不一致");
                return false;
            }
        }
        jQuery('#regpassword_tip').empty();
    });
    jQuery('#regrepassword').on('blur', function () {
        usercenter.regpassword = jQuery('#regpassword').val();
        usercenter.regrepassword = jQuery('#regrepassword').val();
        if (!usercenter.regrepassword) {
            jQuery('#regrepassword_tip').html("*您还没有输入确认密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.regrepassword)) {
            jQuery('#regrepassword_tip').html("*密码不能含有空格");
            return false;
        }
        if (usercenter.regpassword != usercenter.regrepassword) {
            jQuery('#regrepassword_tip').html("*您两次输入密码不一致");
            return false;
        }
        jQuery('#regrepassword_tip').empty();
    });
    jQuery('#regname').on('blur', function () {
        usercenter.regname = jQuery('#regname').val();
        if (!usercenter.regname) {
            jQuery('#regname_tip').html("*您还没有输入昵称");
            return false;
        }
        if (!mw.ugcwikiutil.checkusername(usercenter.regname)) {
            jQuery('#regname_tip').html("*昵称不能含有特殊字符");
            return false;
        }
        usercenter.regnamelength = mw.ugcwikiutil.getLength(usercenter.regname);
        if (usercenter.regnamelength < 4) {
            jQuery('#regname_tip').html("*您输入的昵称过短");
            return false;
        }
        if (usercenter.regnamelength > 20) {
            jQuery('#regname_tip').html("*您输入的昵称过长");
            return false;
        }

        jQuery('#regname_tip').empty();
    });
    jQuery('#user_register').on('click', function () {
        usercenter.regtel = jQuery('#regtel').val();
        if (!usercenter.regtel) {
            jQuery('#regtel_tip').html("*您还没有输入手机号");
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(usercenter.regtel)) {
            jQuery('#regtel_tip').html("*手机号格式不正确");
            return false;
        }

        usercenter.regcode = jQuery('#regcode').val();
        if (!usercenter.regcode) {
            jQuery('#regcode_tip').html("*您还没有输入验证码");
            return false;
        }

        usercenter.regpassword = jQuery('#regpassword').val();
        if (!usercenter.regpassword) {
            jQuery('#regpassword_tip').html("*您还没有输入密码");
            return false;
        }
        if (usercenter.regpassword.length < 6) {
            jQuery('#regpassword_tip').html("*您输入的密码长度过短");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.regpassword)) {
            jQuery('#regpassword_tip').html("*密码不能含有空格");
            return false;
        }
        usercenter.regrepassword = jQuery('#regrepassword').val();
        if (!usercenter.regrepassword) {
            jQuery('#regrepassword_tip').html("*您还没有输入确认密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.regrepassword)) {
            jQuery('#regrepassword_tip').html("*密码不能含有空格");
            return false;
        }
        if (usercenter.regpassword != usercenter.regrepassword) {
            jQuery('#regrepassword_tip').html("*您两次输入密码不一致");
            return false;
        }
        usercenter.regname = jQuery('#regname').val();
        if (!usercenter.regname) {
            jQuery('#regname_tip').html("*您还没有输入昵称");
            return false;
        }
        if (!mw.ugcwikiutil.checkusername(usercenter.regname)) {
            jQuery('#regname_tip').html("*昵称不能含有特殊字符");
            return false;
        }
        usercenter.regnamelength = mw.ugcwikiutil.getLength(usercenter.regname);
        if (usercenter.regnamelength < 4) {
            jQuery('#regname_tip').html("*您输入的昵称过短");
            return false;
        }
        if (usercenter.regnamelength > 20) {
            jQuery('#regname_tip').html("*您输入的昵称过长");
            return false;
        }

        if (!document.getElementById("check-2").checked) {
            jQuery('#regname_tip').html("*您还未选择同意用户协议，请选择");
            return false;
        }

        jQuery('#regtel_tip').empty();
        jQuery('#regcode_tip').empty();
        jQuery('#regpassword_tip').empty();
        jQuery('#regrepassword_tip').empty();
        jQuery('#regname_tip').empty();

        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserCenterUserRegister',
                rsargs: [usercenter.regtel, usercenter.regcode, usercenter.regpassword, usercenter.regrepassword, usercenter.regname]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    location.reload();
                } else {
                    jQuery('#regname_tip').html(res.data);
                    return false;
                }
            }
        );

    });

    jQuery('#rptel').on('blur', function () {
        usercenter.rptel = jQuery('#rptel').val();
        if (!usercenter.rptel) {
            jQuery('#rptel_tip').html("*您还没有输入手机号");
            jQuery('.sendMobileCode').attr('disabled', true);
            jQuery('.sendMobileCode').addClass('on');
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(usercenter.rptel)) {
            jQuery('#rptel_tip').html("*手机号格式不正确");
            jQuery('.sendMobileCode').attr('disabled', true);
            jQuery('.sendMobileCode').addClass('on');
            return false;
        }
        if (usercenter.validCode) {
            jQuery('.sendMobileCode').removeClass('on');
            jQuery('.sendMobileCode').attr('disabled', false);
        }
        jQuery('#rptel_tip').empty();
    });
    jQuery('#rpcode').on('blur', function () {
        usercenter.rpcode = jQuery('#rpcode').val();
        if (!usercenter.rpcode) {
            jQuery('#rpcode_tip').html("*您还没有输入验证码");
            return false;
        }
        jQuery('#rpcode_tip').empty();
    });
    jQuery('#rppassword').on('blur', function () {
        usercenter.rppassword = jQuery('#rppassword').val();
        if (!usercenter.rppassword) {
            jQuery('#rppassword_tip').html("*您还没有输入密码");
            return false;
        }
        if (usercenter.rppassword.length < 6) {
            jQuery('#rppassword_tip').html("*您输入的密码长度过短");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.rppassword)) {
            jQuery('#rppassword_tip').html("*密码不能含有空格");
            return false;
        }
        jQuery('#rppassword_tip').empty();
    });
    jQuery('#rprepassword').on('blur', function () {
        usercenter.rppassword = jQuery('#rppassword').val();
        usercenter.rprepassword = jQuery('#rprepassword').val();
        if (!usercenter.rprepassword) {
            jQuery('#rprepassword_tip').html("*您还没有输入确认密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.rprepassword)) {
            jQuery('#rprepassword_tip').html("*确认密码不能含有空格");
            return false;
        }
        if (usercenter.rppassword != usercenter.rprepassword) {
            jQuery('#rprepassword_tip').html("*您两次输入密码不一致");
            return false;
        }
        jQuery('#rprepassword_tip').empty();
    });


    jQuery('#user_recoverpwd').on('click', function () {
        usercenter.rptel = jQuery('#rptel').val();
        if (!usercenter.rptel) {
            jQuery('#rptel_tip').html("*您还没有输入手机号");
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(usercenter.rptel)) {
            jQuery('#rptel_tip').html("*手机号格式不正确");
            return false;
        }

        usercenter.rpcode = jQuery('#rpcode').val();
        if (!usercenter.rpcode) {
            jQuery('#rpcode_tip').html("*您还没有输入验证码");
            return false;
        }
        usercenter.rppassword = jQuery('#rppassword').val();
        if (!usercenter.rppassword) {
            jQuery('#rppassword_tip').html("*您还没有输入密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.rppassword)) {
            jQuery('#rppassword_tip').html("*密码不能含有空格");
            return false;
        }
        if (usercenter.rppassword.length < 6) {
            jQuery('#rppassword_tip').html("*您输入的密码长度过短");
            return false;
        }

        usercenter.rprepassword = jQuery('#rprepassword').val();
        if (!usercenter.rprepassword) {
            jQuery('#rprepassword_tip').html("*您还没有输入确认密码");
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(usercenter.rprepassword)) {
            jQuery('#rprepassword_tip').html("*确认密码不能含有空格");
            return false;
        }
        if (usercenter.rppassword != usercenter.rprepassword) {
            jQuery('#rprepassword_tip').html("*您两次输入密码不一致");
            return false;
        }
        jQuery('#rptel_tip').empty();
        jQuery('#rpcode_tip').empty();
        jQuery('#rppassword_tip').empty();
        jQuery('#regrepassword_tip').empty();

        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserCenterUserRecoverPassword',
                rsargs: [usercenter.rptel, usercenter.rppassword, usercenter.rprepassword, usercenter.rpcode]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    location.reload();
                } else {
                    jQuery('#rprepassword_tip').html(res.data);
                    return false;
                }
            }
        );

    });
*/


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


    /*function sendregcode(mobile, sendtype) {
        if (!mobile) {
            if (sendtype == "register") {
                jQuery('#regtel_tip').html('手机号不能为空');
            } else if (sendtype == "upnewmobile") {
                mw.ugcwikiutil.msgDialog('手机号不能为空');
            }
            return false;
        } else {
            jQuery.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfUserRegSendCode',
                    rsargs: [mobile]
                },
                function (data) {
                    var res = jQuery.parseJSON(data);
                    if (res.rs != '1') {
                        if (sendtype == "register") {
                            jQuery('#regtel_tip').html(res.data);
                        } else if (sendtype == "upnewmobile") {
                            mw.ugcwikiutil.msgDialog(res.data);
                        }
                        return false;
                    }
                }
            );
        }
    }

    jQuery('.sendRegMobileCode').on('click', function () {
        var code = $(this);
        usercenter.sendtype = $(this).data('sendtype');
        if (usercenter.sendtype == "register") {
            usercenter.sendmobile = jQuery("#regtel").val();
            if (!usercenter.sendmobile) {
                code.attr('disabled', true);
                code.addClass('on');
                jQuery('#regtel_tip').html('手机号不能为空');
                return false;
            }
            if (!mw.ugcwikiutil.checkmobile(usercenter.sendmobile)) {
                jQuery('#regtel_tip').html('手机号格式不正确');
                return false;
            }
        } else if (usercenter.sendtype == 'upnewmobile') {
            usercenter.sendmobile = jQuery("#newuptel").val();
            if (!usercenter.sendmobile) {
                mw.ugcwikiutil.msgDialog('手机号不能为空');
                return false;
            }
            if (!mw.ugcwikiutil.checkmobile(usercenter.sendmobile)) {
                mw.ugcwikiutil.msgDialog('手机号格式不正确');
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
        }

        code.attr('disabled', true);
        code.addClass('on');

        var time = 60;
        usercenter.validCode = true;
        if (usercenter.validCode) {
            usercenter.validCode = false;
            sendregcode(usercenter.sendmobile, usercenter.sendtype);
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

    function sendcode(mobile, sendtype) {
        jQuery.post(
            mediaWiki.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserCenterUserSendCode',
                rsargs: [mobile]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs != '1') {
                    if (sendtype == 'recoverpassword') {
                        jQuery('#rptel_tip').html(res.data);
                        jQuery('.sendMobileCode').attr('disabled', true);
                        jQuery('.sendMobileCode').addClass('on');
                        clearInterval(mw.msgcodetime);
                    } else {
                        mw.ugcwikiutil.msgDialog(res.data);
                    }
                    return false;
                }
            }
        );
    }

    jQuery('.sendMobileCode').on('click', function () {

        var mobileid = $(this).data('mobileid');
        var sendmobile = 'isempty';
        if (mobileid) {
            sendmobile = jQuery('#' + mobileid).val();
        }

        usercenter.sendtype = $(this).data('sendtype');
        if (usercenter.sendtype == 'recoverpassword' && !sendmobile) {
            if (!sendmobile) {
                jQuery('#rptel_tip').html('手机号不能为空');
                return false;
            } else if (!mw.ugcwikiutil.checkmobile(sendmobile)) {
                jQuery('#rptel_tip').html('手机号格式不正确');
                return false;
            }
        }

        var code = $(this);
        code.attr('disabled', true);
        code.addClass('on');

        var time = 60;
        usercenter.validCode = true;
        if (usercenter.validCode) {
            usercenter.validCode = false;
            sendcode(sendmobile, usercenter.sendtype);
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
    });*/


    mw.joymesiteuserfollow = function () {
    	
        if (mediaWiki.config.get('wgUserName') == null) {
            // mw.loginbox.login();
            loginDiv();
            return false;
        }
        var that = $("a[name='joymesiteuserfollow']");
        if (that.hasClass('gz-done')) {
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
                            if (res.rs == '1') {
                            	that.addClass("usersitefollow");
                                that.removeClass("gz-done");
                                if($('#joyme_wiki_fillow_info1').text().indexOf('K') == -1){
                                	$('.joyme_wiki_fillow_info').text(parseInt($('#joyme_wiki_fillow_info1').text()) - 1)
                                }
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
                         if($('#joyme_wiki_fillow_info1').text().indexOf('K') == -1){
                        	 $('.joyme_wiki_fillow_info').text(parseInt($('#joyme_wiki_fillow_info1').text()) + 1);
                         }
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

    //新需求，转换小数
    mw.transformnum = function (num) {
        if (num) {
            if (num >= 1000) {
                var m = num.length;
                var j = num.substring(m - 3, m);
                var s = num.replace(j, '.') + num.substring(m - 3, m);
                var q = parseFloat(s).toFixed(1);
                return q.toString() + 'k';
            }
            return num;
        }
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
                
                var wiki_word, wiki_fillow, wiki_edit;
                if (splits[0] >= 1000) {
                    var m = splits[0].length;
                    var j = splits[0].substring(m - 3, m);
                    var s = splits[0].replace(j, '.') + splits[0].substring(m - 3, m);
                    var q = parseFloat(s).toFixed(1);
                    wiki_word = q.toString() + ' K';
                } else {
                    wiki_word = splits[0];
                }

                $('.joyme_wiki_word_info').html(wiki_word);

                if (splits[1] >= 1000) {
                    var m = splits[1].length;
                    var j = splits[1].substring(m - 3, m);
                    var s = splits[1].replace(j, '.') + splits[1].substring(m - 3, m);
                    var q = parseFloat(s).toFixed(1);
                    wiki_fillow = q.toString() + ' K';
                } else {
                    wiki_fillow = splits[1];
                }
                if (res.rs == 1) {
                    $('#joyme_site_follow_status').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow_ygz()"></a>');
                    $('.focus-box').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow_ygz()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1"></em> 关注</a>');
                } else if (res.rs == 3 || res.rs == 2) {
                    $('#joyme_site_follow_status').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow()"></a>');
                    $('.focus-box').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz gz-done" onclick="mw.joymesiteuserfollow()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1"></em> 关注</a>');
                } else {
                    $('#joyme_site_follow_status').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz" onclick="mw.joymesiteuserfollow()"></a>');
                    $('.focus-box').append(' <a name="joymesiteuserfollow" id="joymesiteuserfollow" href="javascript:;" class="sf-gz" onclick="mw.joymesiteuserfollow()"></a><a class="gz"><em class="joyme_wiki_fillow_info" id="joyme_wiki_fillow_info1"></em> 关注</a>');
                }

                $('.joyme_wiki_fillow_info').html(wiki_fillow);

                if (splits[2] >= 1000) {
                    var m = splits[2].length;
                    var j = splits[2].substring(m - 3, m);
                    var s = splits[2].replace(j, '.') + splits[2].substring(m - 3, m);
                    var q = parseFloat(s).toFixed(1);
                    wiki_edit = q.toString() + ' K';
                } else {
                    wiki_edit = splits[2];
                }

                $('.joyme_wiki_edit_info').html(wiki_edit);
            }
        );
    }*/
});
