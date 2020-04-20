<?php

    declare(strict_types=1);
    $configs = include($_SERVER['DOCUMENT_ROOT'] . "/api/config/config.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/api/lib/sqlcmd.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/api/lib/jwt.php");

    function is_invalid(string $argsName) : bool {
        if (!isset($_POST[$argsName]) || $_POST[$argsName] === '') return true;
        else return false;
    }

    function add_login_turn($configs) {
        $db = mysqli_connect($configs['host'],
                             $configs['username'],
                             $configs['password'],
                             $configs['dbname']);

        // Database connect failed
        if (!$db) {
            header($_SERVER['SERVER_PROTOCOL'] . " 501");
            $aResult['error'] = "Connect Error ($db->connect_errno) $db->connect_error";
            return;
        }

        $sql_result = $db->query(sqlcmd_addUserLoginTurn($_SESSION['username']));

        // Query failed
        if ($sql_result === FALSE) $output = "<br/>Unexpected error! (Please report)";
        else {
            $sql_result = $db->query(sqlcmd_getUserLoginTurn($_SESSION['username']));

            // Query failed
            if ($sql_result === FALSE || $sql_result->num_rows !== 1)
                $output = "<br/>Unexpected error! (Please report)";
            else $output = $sql_result->fetch_assoc()['login_turn'];
        }

        $sql_result = $db->query(sqlcmd_addNumberByOne('total_login_turn'));

        $db->close();
        return $output;
    }

    session_start();

    $aResult = array();
    header($_SERVER['SERVER_PROTOCOL'] . " 403");
    header("Content-Type: application/json");

    // Check referer
    $url = array();
    array_push($url, $configs['referer']);
    array_push($url, $configs['referer'] . "index.php");
    array_push($url, $configs['referer'] . "login.php");
    array_push($url, $configs['referer'] . "register.php");
    array_push($url, $configs['referer'] . "profile.php");

    if (!(in_array($_SERVER['HTTP_REFERER'], $url)) && (strpos($_SERVER['HTTP_REFERER'], $configs['referer'] . "comment.php?page=") !== 0)) {
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
                        $_SESSION['loginTurn'] = add_login_turn($configs);
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
                        $_SESSION['loginTurn'] = add_login_turn($configs);
                    }
                    // If SESSION set failed, no need to redirect
                    else {
                        $aResult['redirect'] = false;
                        if ($configs['debug'])
                            $aResult['error'] = "Session set failed!";
                    }
                }
                else if ($_POST['page'] == 'profile' || $_POST['page'] = 'postComment') {
                    // If SESSION set successfully, no need to redirect
                    if (jwt_setUsername()) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['redirect'] = false;
                        $_SESSION['loginTurn'] = add_login_turn($configs);
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