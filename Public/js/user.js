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

$(document).on('blur', '#cur-password', function() {
    blurCheckPassword($(this));
});

$(document).on('blur', '#new-password', function() {
    blurCheckPassword($(this));
});

$(document).on('blur', '#renew-password', function() {
    blurCheckNewPassword($(this));
});

$(document).on('click', '#update-password-btn', function() {
    focusDeleteTip($(this));
    updatePassword();
});

function focusDeleteTip(obj) {
    var focus_id = obj.attr('id');
    $('#' + focus_id + '-tip').hide(0).html('');
}

function blurCheckPassword(obj) {
    var focus_id = obj.attr('id');
    var password = obj.val();
    if (password.length == 0) {
        $('#' + focus_id + '-tip').show(0).html('密码不能为空！');
        return false;
    } else if (password.length < 6) {
        $('#' + focus_id + '-tip').show(0).html('密码长度不能小于6！');
        return false;
    } else if (password.length > 32) {
        $('#' + focus_id + '-tip').show(0).html('密码长度不能大于32！');
        return false;
    } else {
        return true;
    }
}

function blurCheckNewPassword(obj) {
    var focus_id = obj.attr('id');
    var password = obj.val();

    if ($("#new-password").val() != password) {
        $('#' + focus_id + '-tip').show(0).html('两次新密码不一致！');
        return false;
    } else {
        return true;
    }
}

function updatePassword() {
    if ($("#captcha").length > 0 
                && !gCheckCaptchaPassed && !blurCheckCaptcha($("#captcha"), false)) {
        return false;
    }

    if (!blurCheckPassword($("#cur-password")) || !blurCheckPassword($("#new-password"))
                        || !blurCheckNewPassword($("#renew-password"))) {
        return false;
    }

    var cur_password = $("#cur-password").val();
    var new_password = $("#new-password").val();
    var captcha = $("#captcha").val();

    $.ajax({
        type: "POST",
        url: gUpdatePasswordAjaxUrl,
        data: {
            new_password: new_password,
            cur_password: cur_password,
            captcha: captcha,
        },
        success: function(response) {
            if (response.success) {
                $('#update-password-btn-tip').show(0).html("更改成功！");
                $('#update-password-btn-tip').animate({opacity: "hide"}, 3000);
                setTimeout(function() {window.location.href = response['forward'];}, 3000);
            } else {
                $('#update-password-btn-tip').show(0).html(response.msg);
                updateCaptcha();
                $('#update-password-btn-tip').animate({opacity: "hide"}, 3000);
                return false;
            }
        }
    });
}

