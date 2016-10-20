// 更新验证码
function updateCaptcha() {
    var img = gSiteUrl + "/User/captcha?tm=" + Math.random();
    $('#captcha-image').attr("src", img);
}
