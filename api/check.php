<?php

    declare(strict_types=1);
    $configs = include($_SERVER['DOCUMENT_ROOT'] . "/api/config/config.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/api/lib/sqlcmd.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/api/lib/jwt.php");

    function is_invalid(string $argsName) : bool {
        if (!isset($_POST[$argsName]) || $_POST[$argsName] === '') return true;
        else return false;
    }

    // Prevent users from visiting this URL by methods except POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header($_SERVER['SERVER_PROTOCOL'] . " 403");
        if ($configs['debug']) {
            $aResult['error'] = "Invalid request method.";
            echo json_encode($aResult);
        }
        exit;
    }

    // Visiting by POST, start the program
    session_start();

    $aResult = array();
    header($_SERVER['SERVER_PROTOCOL'] . " 403");
    header("Content-Type: application/json");

    // Check referer
    $url = array();
    array_push($url, $configs['referer']);
    array_push($url, $configs['referer'] . "index.php");
    array_push($url, $configs['referer'] . "comment.php");
    array_push($url, $configs['referer'] . "login.php");
    array_push($url, $configs['referer'] . "register.php");
    array_push($url, $configs['referer'] . "profile.php");

    if (!(in_array($_SERVER['HTTP_REFERER'], $url))) {
        if ($configs['debug'])
            $aResult['error'] = "Unauthorized referer.";
    }
    // Check random number
    else if ($_POST['r'] != $_SESSION['randomNumber']) {
        if ($configs['debug'])
            $aResult['error'] = "Wrong random number.";
    }
    // Check data has action value
    else if (is_invalid('action')) {
        if ($configs['debug'])
            $aResult['error'] = "Missing page.";
    }
    else {

        switch($_POST['action']) {

            case 'check':
                if (is_invalid('page')) {
                    $aResult['error'] = "Missing arguments!";
                }
                else if (!isset($_COOKIE['JWT'])) {
                    if ($configs['debug'])
                        $aResult['error'] = "Missing JWT cookie!";
                }
                else if ($_POST['page'] == 'index' || $_POST['page'] == 'comment') {
                    if (jwt_setUsername()) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['redirect'] = false;
                    }
                    else {
                        $aResult['redirect'] = false;
                        if ($configs['debug'])
                            $aResult['error'] = "Session set failed!";
                    }
                }
                else if ($_POST['page'] == 'login' || $_POST['page'] == 'register') {
                    // If SESSION set successfully, redirect to profile page
                    if (jwt_setUsername()) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['redirect'] = true;
                        $aResult['link'] = $configs['referer'] . "profile.php";
                    }
                    // If SESSION set failed, no need to redirect
                    else {
                        $aResult['redirect'] = false;
                        if ($configs['debug'])
                            $aResult['error'] = "Session set failed!";
                    }
                }
                else if ($_POST['page'] == 'profile') {
                    // If SESSION set successfully, no need to redirect
                    if (jwt_setUsername()) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['redirect'] = false;
                    }
                    // If SESSION set failed, redirect to login page
                    else {
                        $aResult['redirect'] = true;
                        $aResult['link'] = $configs['referer'] . "login.php";
                        if ($configs['debug'])
                            $aResult['error'] = "Session set failed!";
                    }
                }
                else {
                    $aResult['redirect'] = true;
                    $aResult['link'] = $configs['referer'];
                    if ($configs['debug'])
                        $aResult['error'] = "Invalid page!";
                }

                break;

            default:
                if ($configs['debug'])
                    $aResult['error'] === "Nonexistent action.";
        }
    }

    echo json_encode($aResult);

?>