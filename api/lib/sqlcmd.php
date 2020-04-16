<?php

    // Fake 404 website, but can be recognize by 'X-Powered-By' in Response Header
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header($_SERVER["SERVER_PROTOCOL"] . " 404");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
</body></html>';
        exit;
    }

    function stringSymmetric(string $input) : string {
        $output = $input;
        return $output;
    }

    function sqlcmd_createUserTable() : string {
        return "CREATE TABLE user (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                    username VARCHAR(64) NOT NULL UNIQUE,
                    password VARCHAR(128) NOT NULL,
                    reg_date TIMESTAMP, -- NOT NULL,
                    avatar VARCHAR(40) NOT NULL DEFAULT 'https://i.imgur.com/9B9e2OY.png'
                )";
    }

    function sqlcmd_checkUserExist(string $username) : string {
        return "SELECT username FROM user 
                WHERE username = '$username'";
    }
    
    function sqlcmd_addUser(string $username, string $sha512_pwd) : string {
        return "INSERT INTO user (username, password) 
                VALUES ('$username', '$sha512_pwd')";
    }
    
    function sqlcmd_getUser(string $username, string $sha512_pwd) : string {
        return "SELECT username FROM user 
                WHERE username = '$username' && password = '$sha512_pwd'";
    }
    
    function sqlcmd_getAvatar(string $username) {
        return "SELECT avatar FROM user 
                WHERE username = '$username'";
    }
    
    function sqlcmd_updateAvatar(string $username, string $link) : string {
        return "UPDATE user SET avatar = '$link' 
                WHERE username = '$username'";
    }
    
    function sqlcmd_createCommentTable() : string {
        return "CREATE TABLE comment (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                    username VARCHAR(64) NOT NULL,
                    date TIMESTAMP NOT NULL,
                    content VARCHAR(1000) NOT NULL
                )";
    }

    function sqlcmd_addComment(string $username, $date, string $content) : string {
        return "INSERT INTO comment (username, date, content) 
                VALUES ('$username', '$date', '$content')";
    }
    
?>