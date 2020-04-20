function getComments(page) {
    
    post('api/comment.php', {
        action: 'getComments',
        page: page,
        r: r
    }, function (response) {
        if (response.next) document.getElementById("next-page").classList.remove("disabled");
        listComment(response.comments);
    }, function (response) {
        alert(response.error);
        window.location.replace("/comment.php?page=1");
    });
}

function listComment(comments) {
    var group = document.getElementById("card-group");

    

    comments.forEach (function (comment) {
        comment = JSON.parse(comment);

        if (comment.id != 0) {
            var text =
                '<div class="card w-100" style="margin-bottom: 8px;" id="comment-' + comment.id + '">' +
                    '<div class="d-flex flex-row">' +
                        '<div class="flex-column flex-shrink-1" style="padding: 12px;">' +
                            '<img class="border" src="' + comment.avatar + '" style="background: #cccccc; height: 100px; width: 100px;" alt="Avatar">' +
                        '</div>' +
                        '<div class="flex-column flex-grow-1 w-50" style="padding: 12px; padding-left: 0px;">' +
                            '<div class="d-flex flex-row">' +
                                '<h4 class="flex-fill" id="comment-' + comment.id + '-username"></h4>';
            if (comment.editable) text +=
                                '<button type="button" class="btn btn-outline-danger flex-shrink-1" onclick="deleteComment(' + comment.id + ')">' +
                                    '<i class="fa fa-trash"></i></button>';
            text +=
                            '</div>' +
                            '<div class="flex-row" id="comment-' + comment.id + '-title"></div>' +
                            '<div class="flex-row" id="comment-' + comment.id + '-content"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            group.innerHTML += text;

            document.getElementById("comment-" + comment.id + "-username").innerText = comment.username;
            document.getElementById("comment-" + comment.id + "-title").innerText = '標題：' + comment.title;
            document.getElementById("comment-" + comment.id + "-content").innerText = '內容：' + comment.content;
        }
        else {
            var text =
                '<div class="card w-100" style="margin-bottom: 8px;">' +
                    '<div class="d-flex flex-row justify-content-center" style="padding: 12px;">' +
                        '<h4 style="margin: 0px;">本文已被刪除 ಥ__ಥ</h4>' +
                    '</div>' +
                '</div>';
            group.innerHTML += text;
        }
    });
}

function deleteComment(id) {
    
    post('api/comment.php', {
        action: 'deleteComment',
        id: id,
        r: r
    }, function (response) {
        alert(response.result);
        document.getElementById("comment-" + id).innerHTML = 
            '<div class="d-flex flex-row justify-content-center" style="padding: 8px;">' +
                '<h4>本文已被刪除 ಥ__ಥ</h4>' +
            '</div>';
    });
}

function postComment() {
    
    var title = document.getElementById("title").value;
    var content = document.getElementById("content").value;

    if (title.length > 30) {
        alert("The length of title should be 30 at most!");
        return;
    }
    else if (title.length == 0) {
        alert("Please input something in title!");
        return;
    }
    else if (content.length > 600) {
        alert("The length of content should 30 at most!");
        return;
    }
    else if (content.length == 0) {
        alert("Please input something in content!");
        return;
    }

    post('api/comment.php', {
        action: 'postComment',
        title: title,
        content: content,
        r: r
    }, function (response) {
        alert(response.result);
        window.location.replace("/comment.php?page=1");
    }, function (response) {
        alert(response.error);
        window.location.replace("/comment.php?page=1");
    });
}