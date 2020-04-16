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

    function stringEncode(string $input) : string {
        return base64_encode($input);
    }

    function stringDecode(string $input) : string {
        return base64_decode($input);
    }

    function sqlcmd_createUserTable() : string {
        return "CREATE TABLE user (
                    id INT(4) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                    username VARCHAR(40) NOT NULL UNIQUE,
                    password VARCHAR(128) NOT NULL,
                    reg_date TIMESTAMP, -- NOT NULL,
                    avatar VARCHAR(40) NOT NULL DEFAULT 'https://i.imgur.com/9B9e2OY.png'
                )";
    }

    function sqlcmd_checkUserExist(string $username) : string {

        $encode_username = stringEncode($username);

        return "SELECT username FROM user 
                WHERE username = '$encode_username'";
    }
    
    function sqlcmd_addUser(string $username, string $password) : string {

        $encode_username = stringEncode($username);
        $sha512_pwd = hash('sha512', $password);

        return "INSERT INTO user (username, password) 
                VALUES ('$encode_username', '$sha512_pwd')";
    }
    
    function sqlcmd_getUser(string $username, string $password) : string {

        $encode_username = stringEncode($username);
        $sha512_pwd = hash('sha512', $password);

        return "SELECT username FROM user 
                WHERE username = '$encode_username' && password = '$sha512_pwd'";
    }
    
    function sqlcmd_getAvatar(string $username) {

        $encode_username = stringEncode($username);

        return "SELECT avatar FROM user 
                WHERE username = '$encode_username'";
    }
    
    function sqlcmd_updateAvatar(string $username, string $link) : string {

        $encode_username = stringEncode($username);

        return "UPDATE user SET avatar = '$link' 
                WHERE username = '$encode_username'";
    }
    
    function sqlcmd_createCommentTable() : string {
        return "CREATE TABLE comment (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                    username VARCHAR(40) NOT NULL,
                    date TIMESTAMP, -- NOT NULL,
                    alive BOOLEAN NOT NULL DEFAULT TRUE,
                    content VARCHAR(240) NOT NULL
                )";
    }

    function sqlcmd_getComment(int $page) : string {

        $last_item = $page * 10;
        $first_item = $last_item - 9;

        return "SELECT * FROM comment 
                WHERE id >= $first_item AND id <= $last_item";
    }

    function sqlcmd_addComment(string $username, string $content) : string {

        $encode_username = stringEncode($username);
        $encode_content = stringEncode($content);

        return "INSERT INTO comment (username, content) 
                VALUES ('$encode_username', '$encode_content')";
    }
    
?>