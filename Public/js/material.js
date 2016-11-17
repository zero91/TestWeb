$(document).on('focusin', '.add-material-item input', function () {
    focusDeleteTip($(this));
}); 

$(document).on('focusin', '.add-material-item textarea', function () {
    focusDeleteTip($(this));
}); 

$(document).on("focus", "input[type='text']", function() {
    var val = $(this).val();
    if (val == this.defaultValue) {
        $(this).val("");
    }
}); 

$(document).on("focus", "textarea", function() {
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

$(document).on("blur", "textarea", function() {
    var val = $(this).val();
    if (val == "") {
        $(this).val(this.defaultValue);
    }
});

$(document).on('blur', '#material-title', function() {
    blurCheckTitle($(this), true);
});

$(document).on('blur', '#material-url', function() {
    blurCheckUrl($(this), true);
});

$(document).on('blur', '#material-reason', function() {
    blurCheckReason($(this), true);
});

$(document).on('click', '#material-add-btn', function() {
    focusDeleteTip($(this));
    addMaterial();
});

function focusDeleteTip(obj) {
    var focus_id = obj.attr('id');
    $('#' + focus_id + '-tip').hide(0).html('');
}

function blurCheckTitle(obj) {
    var title = obj.val();
    if (title.length == 0 || title == '资料主题') {
        $('#material-title-tip').show(0).html('资料主题不能为空！');
        return false;
    } else if (charBytes(title) > 50) {
        $('#material-title-tip').show(0).html('文章标题长度不能超过50！');
        return false;
    }
    return true;
}

function blurCheckUrl(obj) {
    var url = obj.val();
    if (url.length == 0 || url == '访问链接') {
        $('#material-url-tip').show(0).html('访问链接不能为空！');
        return false;
    } else if (url.length > 500) {
        $('#material-url-tip').show(0).html('访问链接长度不能超过500！');
        return false;
    }
    return true;
}

function blurCheckReason(obj) {
    var reason = obj.val();
    if (reason.length == 0 || reason == '推荐理由') {
        $('#material-reason-tip').show(0).html('推荐理由不能为空！');
        return false;
    } else if (charBytes(reason) < 20) {
        $('#material-reason-tip').show(0).html('推荐理由不能少于10个汉字！');
        return false;
    }
    return true;
}

function addMaterial() {
    if (!blurCheckTitle($("#material-title"))) {
        return false;
    }

    if (!blurCheckUrl($("#material-url"))) {
        return false;
    }

    if (!blurCheckReason($("#material-reason"))) {
        return false;
    }

    var title = $("#material-title").val();
    var url = $("#material-url").val();
    var password = $('#material-password').val();
    if (password == '访问密码') {
        password = '';
    }

    var reason = $("#material-reason").val();

    var orginal_tags = $("#material-tags").val();
    if (orginal_tags == "资料标签，逗号分隔。例如：RNN,机器翻译") {
        orginal_tags = "";
    }
    var tags = toSBC(orginal_tags).replace('，', ',').split(',');

    var req_dict = {
        'title' : title,
        'url' : url,
        'password' : password,
        'reason' : reason,
        'tags' : tags
    }

    $.ajax({
        type: "POST",
        url: gAddMaterialAjaxUrl,
        data: req_dict,
        success: function(response) {
            if (response.success) {
                $('#material-add-btn-tip').show(0).html("提交成功！");
                $('#material-add-btn-tip').animate({opacity: "hide"}, 3000);
                setTimeout(function() {window.location.href = response['forward'];}, 3000);
            } else {
                $('#material-add-btn-tip').show(0).html(response.msg);
                return false;
            }
        }
    });
}
