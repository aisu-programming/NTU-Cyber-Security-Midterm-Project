function register() {
        
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var pwdcheck = document.getElementById("pwdcheck").value;

    if (username.length > 30) {
        alert("The length of username should be 30 at most!");
        return;
    }

    if (password != pwdcheck) {
        alert("Password not same!");
        return;
    }

    post('api/register.php', {
        action: 'register',
        username: username,
        password: password,
        r: r
    }, function (response) {
        alert('Register succeed!')
        check('register');
    });
}