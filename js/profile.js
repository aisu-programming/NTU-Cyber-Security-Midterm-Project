function updateAvatar () {

    post('api/profile.php', {
        action: 'updateAvatar',
        r: r
    }, function (response) {
        document.getElementById("avatar").src = response.link;
    }, function (response) {
        document.getElementById("avatar").src = response.link;
    });
}

function uploadImage() {
    
    var image = document.getElementById("upload-image").files[0];
    if (image == undefined) {
        alert('You didn\'t upload anything!');
        return;
    }

    var text = document.getElementById("upload-text");
    text.text = "上傳中";
    var spinner = document.getElementById("upload-spinner");
    spinner.style.display = "inherit";

    var data = new FormData();
    data.append('image', image);
    $.ajax({
        type: "POST",
        url: "https://api.imgur.com/3/image",
        dataType: 'json',
        timeout: 0,
        headers: {
            "Authorization": "Client-ID 7703aa82b49a940",
        },
        processData: false,
        mimeType: "multipart/form-data",
        contentType: false,
        data: data,

        success: function (response) {
            post('api/profile.php', {
                action: 'uploadImage',
                link: response.data.link,
                r: r
            }, function (response) {
                updateAvatar();
            });

            text.text = "上傳";
            spinner.style.display = 'none';
        },

        error: function (xhr) {
            var response = xhr.responseJSON;
            alert(response.data.error.message);

            text.text = "上傳";
            spinner.style.display = 'none';
        }
    });           
}