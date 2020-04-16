function register() {
        
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var pwdcheck = document.getElementById("pwdcheck").value;

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
        checkRedirect('register');
    });
}