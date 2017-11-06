/**
 * Created by kexuedong on 2016/7/19.
 */
jQuery(document).ready(function () {
    var useraccount = {};

    jQuery('.unbindthird').click(function () {
        useraccount.thirdtype = $(this).data('type');
        mw.ugcwikiutil.confirmDialog('请再次确认是否解绑？',function (action) {
            if(action=="accept"){
                jQuery.post(
                    mw.util.wikiScript(), {
                        action: 'ajax',
                        rs: 'wfUserUnBindThirdAccount',
                        rsargs: [useraccount.thirdtype]
                    },
                    function (data) {
                        var res = jQuery.parseJSON(data);
                        if (res.rs == '1') {
                            mw.ugcwikiutil.ensureDialog("解绑成功",function (action) {
                                if(action=="accept"){
                                    location.reload();
                                }
                            });
                        } else {
                            mw.ugcwikiutil.msgDialog(res.data);
                            return false;
                        }
                    }
                );
            }
        });
    });

    jQuery('.nounbindthird').click(function () {
        mw.ugcwikiutil.msgDialog("不能解绑");
        return false;
    });


    jQuery('#bmtel').on('blur', function () {
        useraccount.bmtel = jQuery('#bmtel').val();
        if (!useraccount.bmtel) {
            mw.ugcwikiutil.msgDialog('您还没有输入手机号');
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(useraccount.bmtel)) {
            mw.ugcwikiutil.msgDialog('手机号格式不正确');
            return false;
        }
    });
    jQuery('#bmmobilecode').on('blur', function () {
        useraccount.bmmobilecode = jQuery('#bmmobilecode').val();
        if (!useraccount.bmmobilecode) {
            mw.ugcwikiutil.msgDialog('验证码不能为空');
            return false;
        }
    });

    jQuery('#bmpassword').on('blur', function () {
        useraccount.bmpassword = jQuery('#bmpassword').val();
        if (!useraccount.bmpassword) {
            mw.ugcwikiutil.msgDialog('密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.bmpassword)) {
            mw.ugcwikiutil.msgDialog('密码不能含有空格');
            return false;
        }
        if (useraccount.bmpassword.length < 6) {
            mw.ugcwikiutil.msgDialog('密码长度不能小于6位');
            return false;
        }
    });

    jQuery('#bmrepassword').on('blur', function () {
        useraccount.bmpassword = jQuery('#bmpassword').val();
        useraccount.bmrepassword = jQuery('#bmrepassword').val();
        if (!useraccount.bmrepassword) {
            mw.ugcwikiutil.msgDialog('确认密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.bmrepassword)) {
            mw.ugcwikiutil.msgDialog('确认密码不能含有空格');
            return false;
        }
        if (useraccount.bmrepassword.length < 6) {
            mw.ugcwikiutil.msgDialog('确认密码长度不能小于6位');
            return false;
        }
        if (useraccount.bmpassword != useraccount.bmrepassword) {
            mw.ugcwikiutil.msgDialog('两次输入的密码不一致');
            return false;
        }
    });

    jQuery("#bindmobile").click(function () {
        useraccount.bmtel = jQuery('#bmtel').val();
        if (!useraccount.bmtel) {
            mw.ugcwikiutil.msgDialog('您还没有输入手机号');
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(useraccount.bmtel)) {
            mw.ugcwikiutil.msgDialog('手机号格式不正确');
            return false;
        }
        useraccount.bmmobilecode = jQuery('#bmmobilecode').val();
        if (!useraccount.bmmobilecode) {
            mw.ugcwikiutil.msgDialog('验证码不能为空');
            return false;
        }

        useraccount.bmpassword = jQuery('#bmpassword').val();
        if (!useraccount.bmpassword) {
            mw.ugcwikiutil.msgDialog('密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.bmpassword)) {
            mw.ugcwikiutil.msgDialog('密码不能含有空格');
            return false;
        }
        if (useraccount.bmpassword.length < 6) {
            mw.ugcwikiutil.msgDialog('密码长度不能小于6位');
            return false;
        }
        useraccount.bmrepassword = jQuery('#bmrepassword').val();
        if (!useraccount.bmrepassword) {
            mw.ugcwikiutil.msgDialog('确认密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.bmrepassword)) {
            mw.ugcwikiutil.msgDialog('确认密码不能含有空格');
            return false;
        }
        if (useraccount.bmrepassword.length < 6) {
            mw.ugcwikiutil.msgDialog('确认密码长度不能小于6位');
            return false;
        }
        if (useraccount.bmpassword != useraccount.bmrepassword) {
            mw.ugcwikiutil.msgDialog('两次输入的密码不一致');
            return false;
        }
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserBindMobile',
                rsargs: [useraccount.bmtel, useraccount.bmpassword, useraccount.bmrepassword, useraccount.bmmobilecode]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    mw.ugcwikiutil.msgDialog(res.data);
                    window.location.href = "/home/特殊:账号安全";
                } else {
                    if (res.data.code == '10123' || res.data.code == '10124' || res.data.code == '10204' || res.data.code == '10206') {
                        jQuery('#bmmobilecode').val("");
                        mw.ugcwikiutil.msgDialog(res.data.msg);
                    }
                    else {
                        mw.ugcwikiutil.msgDialog(res.data);
                    }
                    return false;
                }
            }
        );
    });

    jQuery('#oldpwd').on('blur', function () {
        useraccount.oldpwd = jQuery('#oldpwd').val();
        if (!useraccount.oldpwd) {
            mw.ugcwikiutil.msgDialog('旧密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.oldpwd)) {
            mw.ugcwikiutil.msgDialog('旧密码不能含有空格');
            return false;
        }
        if (useraccount.oldpwd.length < 6) {
            mw.ugcwikiutil.msgDialog('旧密码长度不能小于6位');
            return false;
        }
    });
    jQuery('#pwd').on('blur', function () {
        useraccount.pwd = jQuery('#pwd').val();
        if (!useraccount.pwd) {
            mw.ugcwikiutil.msgDialog('新密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.pwd)) {
            mw.ugcwikiutil.msgDialog('新密码不能含有空格');
            return false;
        }
        if (useraccount.pwd.length < 6) {
            mw.ugcwikiutil.msgDialog('密码长度不能小于6位');
            return false;
        }
    });
    jQuery('#repeatpwd').on('blur', function () {
        useraccount.pwd = jQuery('#pwd').val();
        useraccount.repeatpwd = jQuery('#repeatpwd').val();
        if (!useraccount.repeatpwd) {
            mw.ugcwikiutil.msgDialog('确认密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.repeatpwd)) {
            mw.ugcwikiutil.msgDialog('确认密码不能含有空格');
            return false;
        }
        if (useraccount.repeatpwd.length < 6) {
            mw.ugcwikiutil.msgDialog('确认密码长度不能小于6位');
            return false;
        }
        if (useraccount.pwd != useraccount.repeatpwd) {
            mw.ugcwikiutil.msgDialog('两次输入的密码不一致');
            return false;
        }
    });

    jQuery('#modifypassword').click(function () {
        useraccount.oldpwd = jQuery('#oldpwd').val();
        if (!useraccount.oldpwd) {
            mw.ugcwikiutil.msgDialog('旧密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.oldpwd)) {
            mw.ugcwikiutil.msgDialog('旧密码不能含有空格');
            return false;
        }
        if (useraccount.oldpwd.length < 6) {
            mw.ugcwikiutil.msgDialog('旧密码长度不能小于6位');
            return false;
        }
        useraccount.pwd = jQuery('#pwd').val();
        if (!useraccount.pwd) {
            mw.ugcwikiutil.msgDialog('新密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.pwd)) {
            mw.ugcwikiutil.msgDialog('新密码不能含有空格');
            return false;
        }
        if (useraccount.pwd.length < 6) {
            mw.ugcwikiutil.msgDialog('密码长度不能小于6位');
            return false;
        }
        useraccount.repeatpwd = jQuery('#repeatpwd').val();
        if (!useraccount.repeatpwd) {
            mw.ugcwikiutil.msgDialog('确认密码不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkpassword(useraccount.repeatpwd)) {
            mw.ugcwikiutil.msgDialog('确认密码不能含有空格');
            return false;
        }
        if (useraccount.repeatpwd.length < 6) {
            mw.ugcwikiutil.msgDialog('确认密码长度不能小于6位');
            return false;
        }
        if (useraccount.pwd != useraccount.repeatpwd) {
            mw.ugcwikiutil.msgDialog('两次输入的密码不一致');
            return false;
        }

        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserModifyPassword',
                rsargs: [useraccount.oldpwd, useraccount.pwd, useraccount.repeatpwd]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    mw.ugcwikiutil.msgDialog(res.data);
                    location.href = "/home/特殊:账号安全";
                } else {
                    mw.ugcwikiutil.msgDialog(res.data);
                    return false;
                }
            }
        );
    });


    jQuery('#oldmobilecode').on('blur', function () {
        useraccount.oldmobilecode = jQuery('#oldmobilecode').val();
        if (!useraccount.oldmobilecode) {
            mw.ugcwikiutil.msgDialog('您还没有输入验证码');
            return false;
        }
    });

    jQuery('#umstep1').click(function () {
        useraccount.oldmobilecode = jQuery('#oldmobilecode').val();
        if (!useraccount.oldmobilecode) {
            mw.ugcwikiutil.msgDialog('您还没有输入验证码');
            return false;
        }
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserModifyMobile',
                rsargs: ["isempty", useraccount.oldmobilecode, '1']
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    window.location.href = mediaWiki.config.get('wgServer') + "/home/index.php?title=%E7%89%B9%E6%AE%8A:%E6%8D%A2%E7%BB%91%E6%89%8B%E6%9C%BA&step=2&token=" + res.data;
                } else {
                    mw.ugcwikiutil.msgDialog(res.data);
                    return false;
                }
            }
        );
    });

    jQuery('#newuptel').on('blur', function () {
        useraccount.newuptel = jQuery('#newuptel').val();
        if (!useraccount.newuptel) {
            mw.ugcwikiutil.msgDialog('手机号不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(useraccount.newuptel)) {
            mw.ugcwikiutil.msgDialog('手机号格式不正确');
            return false;
        }
    });

    jQuery('#newupmobilecode').on('blur', function () {
        useraccount.newupmobilecode = jQuery('#newupmobilecode').val();
        if (!useraccount.newupmobilecode) {
            mw.ugcwikiutil.msgDialog('您还没有输入验证码');
            return false;
        }
    });

    jQuery('#umstep2').click(function () {
        useraccount.newuptel = jQuery('#newuptel').val();
        if (!useraccount.newuptel) {
            mw.ugcwikiutil.msgDialog('手机号不能为空');
            return false;
        }
        if (!mw.ugcwikiutil.checkmobile(useraccount.newuptel)) {
            mw.ugcwikiutil.msgDialog('手机号格式不正确');
            return false;
        }

        useraccount.newupmobilecode = jQuery('#newupmobilecode').val();
        if (!useraccount.newupmobilecode) {
            mw.ugcwikiutil.msgDialog('您还没有输入验证码');
            return false;
        }

        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserModifyMobile',
                rsargs: [useraccount.newuptel, useraccount.newupmobilecode, '2']
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    window.location.href = mediaWiki.config.get('wgServer') + "/home/index.php?title=%E7%89%B9%E6%AE%8A:%E6%8D%A2%E7%BB%91%E6%89%8B%E6%9C%BA&step=3&token=" + res.data;
                } else {
                    mw.ugcwikiutil.msgDialog(res.data);
                    return false;
                }
            }
        );
    });

    jQuery('.userinfosave').click(function () {
        useraccount.brief = jQuery('#brief').val();
        useraccount.sex = jQuery('#sex').val();
        useraccount.birthday = jQuery('#birthday').val();
        useraccount.proviceid = jQuery('#proviceid').val();
        useraccount.interest = jQuery('#interest').val();
        console.log(useraccount.brief);
        jQuery.post(
            mw.util.wikiScript(), {
                action: 'ajax',
                rs: 'wfUserModifyInfo',
                rsargs: [useraccount.sex, useraccount.proviceid, useraccount.brief, useraccount.birthday, useraccount.interest]
            },
            function (data) {
                var res = jQuery.parseJSON(data);
                if (res.rs == '1') {
                    window.location.reload();
                } else {
                    mw.ugcwikiutil.msgDialog(res.data);
                    return false;
                }
            }
        );
    });

    var W = $(window).width();
    if (W < 992) {
        $("input[type='checkbox']").on('click', function () {
            useraccount.up_property = $(this).attr('name');
            if (document.getElementById($(this).attr('name')).checked) {
                useraccount.up_value = 1;
            } else {
                useraccount.up_value = 0;
            }
            jQuery.post(
                mediaWiki.util.wikiScript(), {
                    action: 'ajax',
                    rs: 'wfUserUpdateRemindSet',
                    rsargs: [useraccount.up_property, useraccount.up_value]
                },
                function (data) {
                    var res = jQuery.parseJSON(data);
                    if (res.rs != '1') {
                        mw.ugcwikiutil.msgDialog(res.data);
                        return false;
                    }
                }
            );
        });
    }

    $('#datetimepicker10').datepicker({
        language: "ch",           //语言选择中文
        format: 'yyyy-mm-dd',      //格式化日期
        orientation: "bottom left"
    });

});
