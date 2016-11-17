$(document).on('focusin', '.edit-article-item input', function () {
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

$(document).on('blur', '#article-title', function() {
    blurCheckTitle($(this), true);
});

$(document).on('click', '#edit-article-btn', function() {
    focusDeleteTip($(this));
    postArticle();
});

function focusDeleteTip(obj) {
    var focus_id = obj.attr('id');
    $('#' + focus_id + '-tip').hide(0).html('');
}

function blurCheckTitle(obj) {
    var title = obj.val();
    if (title.length == 0 || title == '请输入文章标题') {
        $('#article-title-tip').show(0).html('文章标题不能为空！');
        return false;
    } else if (charBytes(title) > 100) {
        $('#article-title-tip').show(0).html('文章标题长度不能超过100！');
        return false;
    }
    return true;
}

function postArticle() {
    if (!blurCheckTitle($("#article-title"))) {
        return false;
    }
    if (!gCheckCaptchaPassed && !blurCheckCaptcha($("#captcha"), false)) {
        return false;
    }

    var title = $("#article-title").val();
    var content = UE.getEditor('article-content').getContent();
    console.log(content);
    console.log(content.length);
    var source = $('#article-source').val();
    if (source == '请输入原文链接，选填') {
        source = '';
    }

    var author = $('#article-author').val();
    if (author == '请输入原文作者，选填') {
        author = '';
    }

    var captcha = $("#captcha").val();

    var orginal_tags = $("#article-tags").val();
    if (orginal_tags == "文章标签，逗号分隔。例如：RNN,机器翻译") {
        orginal_tags = "";
    }
    var tags = toSBC(orginal_tags).replace('，', ',').split(',');

    var req_dict = {
        'title' : title,
        'content' : content,
        'source' : source,
        'author' : author,
        'captcha' : captcha,
        'tags' : tags
    }

    $.ajax({
        type: "POST",
        url: gPostArticleAjaxUrl,
        data: req_dict,
        success: function(response) {
            if (response.success) {
                $('#edit-article-btn-tip').show(0).html("提交成功！正准备为你跳转到该文章页面");
                $('#edit-article-btn-tip').animate({opacity: "hide"}, 3000);
                setTimeout(function() {window.location.href = response['forward'];}, 3000);
            } else {
                $('#edit-article-btn-tip').show(0).html(response.msg);
                updateCaptcha();
                return false;
            }
        }
    });
}

function fetchArticle(id) {
    $.ajax({
        type: "POST",
        url: gFetchArticleAjaxUrl,
        data: {"id" : id},
        success: function(response) {
            if (response['success']) {
                $("#article-title").val(response['title']);
                UE.getEditor('article-content').setContent(response['content']);
                return true;
            }
            return false;
        }
    });
}
