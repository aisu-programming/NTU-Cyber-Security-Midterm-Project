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
        catch (\Firebase\JWT\SignatureInvalidException $e) {
            unset($_COOKIE['JWT']);
            unset($_SESSION['username']);
            return "Error: " . $e->getMessage();
        }
    }

    function jwt_decode(string $jwt) : array {

        $configs = include($_SERVER['DOCUMENT_ROOT'] . "/api/config/config.php");

        JWT::$leeway = 60;
        $decoded = JWT::decode($jwt, $configs['key'], array('HS512'));

        return (array) $decoded;
    }

    function jwt_isExpire(string $jwt) : bool {

        try {
            $exp = jwt_decode($jwt)['exp'];
            if (time() > $exp) {
                // Clear the JWT cookie if it's expired
                unset($_COOKIE['JWT']);
                unset($_SESSION['username']);
                return true;
            }
            else return false;
        }
        catch (\Firebase\JWT\SignatureInvalidException $e) {
            // Clear the JWT cookie if there's an error
            unset($_COOKIE['JWT']);
            unset($_SESSION['username']);
            return true;
        }
    }

    function jwt_setUsername(string $jwt) : bool {

        if (jwt_isExpire($jwt)) {
            return false;
        }
        else {
            try {
                $username = jwt_decode($jwt)['username'];
                $_SESSION['username'] = $username;
                return true;
            }
            catch (\Firebase\JWT\SignatureInvalidException $e) {
                unset($_COOKIE['JWT']);
                unset($_SESSION['username']);
                return false;
            }
        }
    }

?>