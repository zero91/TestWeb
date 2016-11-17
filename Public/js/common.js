$(document).on('blur', '#captcha', function() {
    blurCheckCaptcha($(this), true);
});

// Global Variables
gCheckCaptchaPassed = false;

// 更新验证码
function updateCaptcha() {
    var img = gSiteUrl + "/User/captcha?tm=" + Math.random();
    $('#captcha-image').attr("src", img);
}

function blurCheckCaptcha(obj, async) {
    var captcha = obj.val();
    if (captcha == "请输入验证码" || captcha == "") {
        $('#captcha-tip').show(0).html('验证码不能为空！');
        gCheckCaptchaPassed = false;
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: gCheckCaptchaAjaxUrl,
            async: async,
            data: {
                captcha: captcha,
            },
            success: function(response) {
                if (!response.success) {
                    $('#captcha-tip').show(0).html(response.msg);
                    gCheckCaptchaPassed = false;
                } else {
                    gCheckCaptchaPassed = true;
                }
                return gCheckCaptchaPassed;
            }
        });
        if (async == false) {
            return gCheckCaptchaPassed;
        }
    }
}

// 计算含中文字符的长度
function charBytes(str) {
    var len = 0;
    for (var i = 0; i < str.length; ++i) {
        if (str.charCodeAt(i) > 127) {
            ++len;
        }
        ++len;
    }
    return len;
}

// 转全角字符
function toDBC(str) {
    var result = "";
    var len = str.length;
    for (var i = 0; i < len; ++i) {
        var c = str.charCodeAt(i);
        //全角与半角相差（除空格外）：65248(十进制)
        c = (c >= 0x0021 && c <= 0x007E) ? (c + 65248) : c;
        //处理空格
        c = (c == 0x0020) ? 0x03000 : c;
        result += String.fromCharCode(c);
    }
    return result;
}

// 转半角字符
function toSBC(str) {
    var result = "";
    var len = str.length;
    for (var i = 0; i < len; ++i) {
        var c = str.charCodeAt(i);
        //全角与半角相差（除空格外）：65248（十进制）
        c = (c >= 0xFF01 && c <= 0xFF5E) ? (c - 65248) : c;
        //处理空格
        c = (c == 0x03000) ? 0x0020 : c;
        result += String.fromCharCode(c);
    }
    return result;
}
