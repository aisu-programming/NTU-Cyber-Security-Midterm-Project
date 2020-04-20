<?php

    // Fake 404 website, but can be recognize by 'X-Powered-By' in Response Header
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    require $_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php";
    use \Firebase\JWT\JWT;

    function jwt_create(string $username) : string {

        $configs = include($_SERVER['DOCUMENT_ROOT'] . "/api/config/config.php");

        $payload = array(
            'iss' => $configs['isser'],
            'iat' => $_SERVER['REQUEST_TIME'],
            'exp' => $_SERVER['REQUEST_TIME'] + $configs['exp'],
            'username' => $username
        );

        try {
            $jwt = JWT::encode($payload, $configs['key'], 'HS512');
            setcookie("JWT", $jwt, time() + $configs['exp'], '/');
            $_SESSION['username'] = $username;
            return $jwt;
        }
        catch (UnexpectedValueException $e) {
            unset($_COOKIE['JWT']);
            unset($_SESSION['username']);
            return "Error: " . $e->getMessage();
        }
    }

    function jwt_decode() : array {

        $configs = include($_SERVER['DOCUMENT_ROOT'] . "/api/config/config.php");

        JWT::$leeway = 60;
        $decoded = JWT::decode($_COOKIE['JWT'], $configs['key'], array('HS512'));

        return (array) $decoded;
    }

    function jwt_setUsername() : bool {

        try {
            if (isset($_SESSION['username'])) unset($_SESSION['username']);
            $_SESSION['username'] = jwt_decode()['username'];
            return true;
        }
        catch (Exception $e) {
            // Clear both JWT cookie and SESSION if there's an error
            unset($_COOKIE['JWT']);
            unset($_SESSION['username']);
            return false;
        }
    }

?>