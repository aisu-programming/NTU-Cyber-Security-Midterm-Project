function login() {
        
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    post('api/login.php', {
        action: 'login',
        username: username,
        password: password,
        r: r
    }, function (response) {
        alert(response.result);
        check('login');
    });
}