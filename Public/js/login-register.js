$(document).on('focusin', '.user-form-item input', function () {
    focusDeleteTip($(this));
}); 

$(document).on("focus", "input[type='text']", function() {
    var val = $(this).val();
    if (val == this.defaultValue) {
        $(this).val("");
    }
}); 

$(document).on("blur", "input[type='text']", function() {
    var val = $(this).val();
    if (val == "") {
        $(this).val(this.defaultValue);
    }
});

$(document).on("focus", "input[type='password']", function() {
    var val = $(this).data('placeholder');
    if (val != '') {
        $(this).attr('placeholder', '');
    }
});

$(document).on("blur", "input[type='password']", function() {
    var val = $(this).data('placeholder');
    if (val != "") {
        $(this).attr('placeholder', val);
    }
});

$(document).on('blur', '#register-email', function() {
    blurCheckEmail($(this), true);
});

$(document).on('blur', '#register-password', function() {
    blurCheckPassword($(this));
});

$(document).on('click', '#register-btn', function() {
    focusDeleteTip($(this));
    register();
});

$(document).on('click', '#login-btn', function() {
    focusDeleteTip($(this));
    login();
});

checkEmailPassed = false;
checkPasswordPassed = false;
checkUsernamePassed = false;

function focusDeleteTip(obj) {
    var focus_id = obj.attr('id');
    $('#' + focus_id + '-tip').hide(0).html('');
}

function blurCheckEmail(obj, async) {
    var email = obj.val();
    if (email == '请输入邮箱') {
        $('#register-email-tip').show(0).html('邮箱不能为空！');
        checkEmailPassed = false;
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: gCheckEmailAjaxUrl,
            async: async,
            data: {
                email: email,
            },
            success: function(response) {
                if (!response.success) {
                    $('#register-email-tip').show(0).html(response.msg);
                    checkEmailPassed = false;
                } else {
                    checkEmailPassed = true;
                }
                return checkEmailPassed;
            }
        });

        if (async == false) {
            return checkEmailPassed;
        }
    }
}

function blurCheckPassword(obj) {
    var focus_id = obj.attr('id');
    var password = obj.val();
    if (password.length == 0) {
        checkPasswordPassed = false;
        $('#' + focus_id + '-tip').show(0).html('密码不能为空！');
        return false;
    } else if (password.length < 6) {
        checkPasswordPassed = false;
        $('#' + focus_id + '-tip').show(0).html('密码长度不能小于6！');
        return false;
    } else if (password.length > 32) {
        checkPasswordPassed = false;
        $('#' + focus_id + '-tip').show(0).html('密码长度不能大于32！');
        return false;
    } else {
        checkPasswordPassed = true;
        return true;
    }
}

function blurCheckUsername(obj) {
    var email = obj.val();
    if (email.length == 0 || email == '请输入账号') {
        $('#login-username-tip').show(0).html('账号不能为空！');
        checkUsernamePassed = false;
        return false;
    }
    checkUsernamePassed = true;
    return checkUsernamePassed;
}

function register() {
    if (!checkEmailPassed && !blurCheckEmail($("#register-email"), false)) {
        return false;
    }
    if (!checkPasswordPassed && !blurCheckPassword($("#register-password"))) {
        return false;
    }
    if (!gCheckCaptchaPassed && !blurCheckCaptcha($("#captcha"), false)) {
        return false;
    }

    var email = $("#register-email").val();
    var password = $("#register-password").val();
    var captcha = $("#captcha").val();

    $.ajax({
        type: "POST",
        url: gRegisterAjaxUrl,
        data: {
            email: email,
            password : password,
            captcha : captcha,
        },
        success: function(response) {
            if (!response.success) {
                $('#register-btn-tip').show(0).html(response.msg);
                updateCaptcha();
                return false;
            } else {
                window.location.href = $("#forward-url").val();
            }
        }
    });
}

function login() {
    if ($("#captcha").length > 0 
                && !gCheckCaptchaPassed && !blurCheckCaptcha($("#captcha"), false)) {
        return false;
    }

    if (!checkUsernamePassed && !blurCheckUsername($("#login-username"))) {
        return false;
    }

    if (!checkPasswordPassed && !blurCheckPassword($("#login-password"))) {
        return false;
    }

    var username = $("#login-username").val();
    var password = $("#login-password").val();
    var captcha = $("#captcha").val();

    $.ajax({
        type: "POST",
        url: gLoginAjaxUrl,
        data: {
            username: username,
            password: password,
            captcha: captcha,
        },
        success: function(response) {
            console.log(response);
            if (!response.success) {
                $('#login-btn-tip').show(0).html(response.msg);
                updateCaptcha();
                $('#login-btn-tip').animate({opacity: "hide"}, 3000);
                return false;
            } else {
                window.location.href = $("#forward-url").val();
            }
        }
    });
}

