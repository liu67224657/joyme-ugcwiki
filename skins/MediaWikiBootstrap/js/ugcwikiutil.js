/**
 * Created by kexuedong on 2016/8/3.
 */
( function ( mw, $ ) {

    mw.loginbox = {

        clearerrortip : function () {
            jQuery('#login_tip').empty();
            jQuery('#regtel_tip').empty();
            jQuery('#regcode_tip').empty();
            jQuery('#regpassword_tip').empty();
            jQuery('#regrepassword_tip').empty();
            jQuery('#regname_tip').empty();
            jQuery('#rptel_tip').empty();
            jQuery('#rpcode_tip').empty();
            jQuery('#rppassword_tip').empty();
            jQuery('#rprepassword_tip').empty();
        },

        clearvalue : function () {
            jQuery('#logintel').val("");
            jQuery('#loginpassword').val("");
            jQuery('#regtel').val("");
            jQuery('#regcode').val("");
            jQuery('#regpassword').val("");
            jQuery('#regrepassword').val("");
            jQuery('#regname').val("");
            jQuery('#rptel').val("");
            jQuery('#rpcode').val("");
            jQuery('#rppassword').val("");
            jQuery('#rprepassword').val("");
        },

        recoverycodebox : function () {
            jQuery( '.sendRegMobileCode' ).html("发送验证码");
            jQuery( '.sendRegMobileCode' ).removeClass('on');
            jQuery( '.sendRegMobileCode' ).attr('disabled',false);
            jQuery( '.sendMobileCode' ).html("发送验证码");
            jQuery( '.sendMobileCode' ).removeClass('on');
            jQuery( '.sendMobileCode' ).attr('disabled',false);
        },

        openbox : function (config) {
            var oEle =$('.'+config),
                oEleBox=$('.'+config+'-box'),
                oMask =$(oEleBox.parents('.mask-login'));
            $('.mask-login').hide();
            mw.loginbox.clearerrortip();
            mw.loginbox.clearvalue();
            clearInterval(mw.msgcodetime);
            oEleBox.show();
            oMask.show();
        },

        login : function () {
            mw.loginbox.openbox('login');
            mw.loginbox.recoverycodebox();
        },

        register : function () {
            mw.loginbox.openbox('register');
            mw.loginbox.recoverycodebox();
        },

        getpassword : function () {
            mw.loginbox.openbox('get-password');
            mw.loginbox.recoverycodebox();
        },
        
        closebox : function () {
            $('.mask-login').hide();
            $('.login-bg').hide();
            // $('body').animate({scrollTop:0},500);
            mw.loginbox.clearerrortip();
            mw.loginbox.clearvalue();
            clearInterval(mw.msgcodetime);
        }
    };

    mw.ugcwikiutil = {
        
        checkmobile : function (mobile) {
            reg = /^0?(13|14|15|17|18)[0-9]{9}$/;
            if(!reg.test(mobile)){
                return false;
            }
            return true;
        },
        
        checkusername : function (username) {
            reg=/^([A-Za-z0-9]|[\u4E00-\u9FA5])*$/;
            if(!reg.test(username)){
                return false;
            }
            return true;
        },
        
        checkpassword : function (password) {
            reg =/\s/;
            if(reg.test(password)){
                return false;
            }
            return true;
        },

        getLength : function(str) {
            ///<summary>获得字符串实际长度，中文2，英文1</summary>
            ///<param name="str">要获得长度的字符串</param>
            var realLength = 0, len = str.length, charCode = -1;
            for (var i = 0; i < len; i++) {
                charCode = str.charCodeAt(i);
                if (charCode >= 0 && charCode <= 128) realLength += 1;
                else realLength += 2;
            }
            return realLength;
        },

        openDialog : function (option) {
            // 获取Dialog对象
            mw.joymedialoghtml = '' +
                '<div class="joyme-dialog-popup-mc">' +
                '<div class="joyme-dialog-wiki-popup">' +
                '<div class="joyme-dialog-warning"><p><span>!</span><b>提示</b></p></div>' +
                '<div class="joyme-dialog-warn-con">' ;
                if(option.message){

                    mw.joymedialoghtml += '<div class="joyme-dialog-warn-text"><p>'+ option.message +'</p></div>';
                }else if(option.contenthtml){
                    mw.joymedialoghtml += option.contenthtml;
                }
                if(option.dialogtype == "autoclose"){
                    mw.joymedialoghtml += '<div class="joyme-dialog-warn-time"><span class="joyme-dialog-close-time">'+ option.closetime+'秒后自动关闭</span></div>';
                }else {
                    mw.joymedialoghtml += '<div class="joyme-dialog-warn-time"><span class="joyme-dialog-close-time"></span></div>';
                }
            mw.joymedialoghtml += '' +
                '<div class="joyme-dialog-warn-close">' +
                '    <p class="joyme-dialog-close-btn"><a class="joyme-dialog-confirm">确定</a>';
            if(option.dialogtype == "confirm"){
                mw.joymedialoghtml += '<a class="joyme-dialog-cancel">取消</a>';
            }
            mw.joymedialoghtml += '</p>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $( 'body' ).append(mw.joymedialoghtml);
            if(option.openedCb && typeof option.openedCb == "function"){
                option.openedCb();
            }
        },

        clickCloseDialog : function (option) {
            $('.joyme-dialog-confirm').unbind("click").click(function(){
                if(option.dialogtype == "autoclose"){
                    clearInterval(mw.autotime);
                }
                $('.joyme-dialog-popup-mc').css("display","none");
            });
            $('.joyme-dialog-cancel').unbind("click").click(function(){
                if(option.dialogtype == "autoclose"){
                    clearInterval(mw.autotime);
                }
                $('.joyme-dialog-popup-mc').css("display","none");
            });
        },

        closeDialog : function () {
            clearInterval(mw.autotime);
            $('.joyme-dialog-popup-mc').css("display","none");
        },
        
        autoCloseDialog : function (msg) {
            mw.ugcwikiutil.closeDialog();
            mw.closetime = 3;
            mw.ugcwikiutil.openDialog({
                'message' : msg,
                'dialogtype' : 'autoclose',
                'closetime' : mw.closetime-1
            });
            mw.autotime = setInterval(function () {
                mw.closetime--;
                $('.joyme-dialog-close-time').html(mw.closetime+"秒后自动关闭");
                if(mw.closetime == 0){
                    mw.ugcwikiutil.closeDialog();
                }
            }, 1000);
            mw.ugcwikiutil.clickCloseDialog({
                'dialogtype' : 'autoclose'
            });
        },

        msgDialog : function (msg) {
            mw.ugcwikiutil.closeDialog();
            mw.ugcwikiutil.openDialog({
                'message' : msg
            });
            mw.ugcwikiutil.clickCloseDialog({
                'dialogtype' : 'message'
            });
        },

        confirmDialog : function (opt,callback) {
            mw.ugcwikiutil.closeDialog();
            if(typeof opt == "string"){
                mw.ugcwikiutil.openDialog({
                    'message' : opt,
                    'dialogtype' : 'confirm'
                })
            }else{

                mw.ugcwikiutil.openDialog({
                    'message' : opt.msg,
                    "contenthtml": opt.contenthtml,
                    'openedCb':opt.openedCb,
                    'dialogtype' : 'confirm'
                });
            }
            $('.joyme-dialog-confirm').unbind("click").click(function(){
                callback.call(this,"accept");
                mw.ugcwikiutil.closeDialog();
            });
            $('.joyme-dialog-cancel').unbind("click").click(function(){
                mw.ugcwikiutil.closeDialog();
            });
        },

        ensureDialog : function (msg,callback) {
            mw.ugcwikiutil.closeDialog();
            mw.ugcwikiutil.openDialog({
                'message' : msg,
                'dialogtype' : 'ensure'
            });
            $('.joyme-dialog-confirm').unbind("click").click(function(){
                callback.call(this,"accept");
                mw.ugcwikiutil.closeDialog();
            });
            $('.joyme-dialog-cancel').unbind("click").click(function(){
                mw.ugcwikiutil.closeDialog();
            });
        }
        
    };

}( mediaWiki, jQuery ) );