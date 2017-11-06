<?php
$joymewikiuser = new JoymeWikiUser();
$joymewikiuser->initData();
?>
<!--登陆 开始 -->
<div class="mask-login">
    <div class="mask-con">
        <div class="login-box login-bg fn-clear" id="login">
            <h1>账号登录</h1>
            <i class="close-icon">关闭</i>
            <div class="form-login">
                <!-- 改过 开始 -->
                <div class="login-test">
                    <div>
                        <label for="tel">账&nbsp;&nbsp;&nbsp;号：</label>
                        <input type="text" id="logintel" placeholder="手机号/邮箱" >
                    </div>
                    <div>
                        <label for="password">密&nbsp;&nbsp;&nbsp;码： </label>
                        <input type="password" id="loginpassword" maxlength="16">
                    </div>
                    <i class="test-error on" id="login_tip"></i>
                </div>
                <!-- 改过 结束 -->
                <div class="text-remeber fn-clear">
                    <input id="check-1" type="checkbox" name="check-1" checked="checked">
                    <label for="check-1"><i></i>记住我</label>
                    <a href="javascript:;" class="link-rem fn-right get-password">忘记密码</a>
                    <button class="login-btn btn-icon" id="user_login">登录</button>
                </div>
                <div class="third-login">
                    <p>或</p>
                    <a href="<?php echo $joymewikiuser->bindqqurl ?>" class="third-icon qq-icon"><i></i></a>
                    <a href="<?php echo $joymewikiuser->bindsinaweibourl ?>" class="third-icon sina-icon"><i></i></a>
                </div>
            </div>
            <a href="javascript:;" class="fn-right a-register register-mask">没有账号？去注册</a>
        </div>
    </div>
</div>
<!--登陆 结束 -->
<!--注册 开始 -->
<div class="mask-login">
    <div class="mask-con">
        <div class="register-box login-bg fn-clear">
            <h1>手机注册</h1>
            <i class="close-icon">关闭</i>
            <div class="form-login" id="register-form">
                <div class="login-test">
                    <div>
                        <label for="tel">手机号：</label>
                        <input type="text" id="regtel" placeholder="仅支持中国大陆" maxlength="11">
                        <i class="test-error on" id="regtel_tip"></i>
                    </div>
                    <div class="code-box">
                        <label for="code">验证码：</label>
                        <input type="text" id="regcode" maxlength="6" class="test-code">
                        <button class="sendRegMobileCode" data-sendtype="register">发送验证码</button>
                        <i class="test-error on" id="regcode_tip"></i>
                    </div>
                    <div>
                        <label for="password">密&nbsp;&nbsp;&nbsp;码： </label>
                        <input type="password" id="regpassword" maxlength="16">
                        <i class="test-error on" id="regpassword_tip"></i>
                    </div>
                    <div>
                        <label for="repassword">确认密码：</label>
                        <input type="password" id="regrepassword" maxlength="16">
                        <i class="test-error on" id="regrepassword_tip"></i>
                    </div>
                    <div>
                        <label for="name">昵称：</label>
                        <input type="text" id="regname" maxlength="20">
                        <i class="test-error on" id="regname_tip"></i>
                    </div>
                </div>
                <div class="register-btn">
                    <button class="login-btn btn-icon" id="user_register">注册</button>
                    <div>
                        <input type="checkbox" id="check-2" name="user_protocol" checked="checked">
                        <label for="check-2"><i></i>同意<a href="http://www.joyme.com/help/law" target="_blank">《用户协议》</a>和<a
                                href="http://www.joyme.com/help/law" target="_blank">《版权声明》</a></label>
                    </div>
                </div>
            </div>
            <a href="javascript:;" class="fn-right a-login login-mask">已有账号？登录</a>
        </div>
    </div>
</div>
<!--注册 结束 -->
<!-- 找回密码 开始 -->
<div class="mask-login">
    <div class="mask-con">
        <div class="get-password-box login-bg fn-clear">
            <h1>找回密码</h1>
            <i class="close-icon">关闭</i>
            <div class="form-login">
                <div class="login-test">
                    <div>
                        <label for="tel">手机号：</label>
                        <input type="text" id="rptel" placeholder="仅支持中国大陆" maxlength="11">
                        <i class="test-error on" id="rptel_tip"></i>
                    </div>
                    <div class="code-box">
                        <label for="code">验证码：</label>
                        <input type="text" id="rpcode" maxlength="6" class="test-code">
                        <button class="sendMobileCode" data-mobileid="rptel" data-sendtype="recoverpassword">发送验证码
                        </button>
                        <i class="test-error on" id="rpcode_tip"></i>
                    </div>
                    <div>
                        <label for="password">密&nbsp;&nbsp;&nbsp;码： </label>
                        <input type="password" id="rppassword" maxlength="16">
                        <i class="test-error on" id="rppassword_tip"></i>
                    </div>
                    <div>
                        <label for="repassword">确认密码：</label>
                        <input type="password" id="rprepassword" maxlength="16">
                        <i class="test-error on" id="rprepassword_tip"></i>
                    </div>
                </div>
                <div class="register-btn">
                    <button class="login-btn btn-icon" id="user_recoverpwd">确定</button>
                </div>
            </div>
            <a href="javascript:;" class="fn-right a-login login-mask">已有账号？登录</a>
        </div>
    </div>
</div>
<!-- 找回密码 结束 -->