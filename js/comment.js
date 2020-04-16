function getComment(page) {
    
    post('api/comment.php', {
        action: 'getComment',
        page: page,
        r: r
    }, function (response) {
        return response.result;
    }, function (response) {
        alert(response.error);
        window.location.replace("/comment.php?page=1");
    });
}