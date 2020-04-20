<?php

    declare(strict_types=1);
    $configs = include('config/config.php');
    include('lib/sqlcmd.php');
    include('lib/jwt.php');

    function is_invalid(string $argsName) : bool {
        if (!isset($_POST[$argsName]) || $_POST[$argsName] === '') return true;
        else return false;
    }

    session_start();

    $aResult = array();
    header($_SERVER['SERVER_PROTOCOL'] . " 403");
    header("Content-Type: application/json");

    // Check referer
    $url = $configs['referer'] . "profile.php";
    if ($_SERVER['HTTP_REFERER'] != $url) {
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
            $aResult['error'] = "Missing action.";
    }
    else {

        switch($_POST['action']) {

            case 'updateAvatar':
                $db = mysqli_connect($configs['host'],
                                     $configs['username'],
                                     $configs['password'],
                                     $configs['dbname']);

                // Database connect failed
                if (!$db) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 501");
                    $aResult['error'] = "Connect Error ($db->connect_errno) $db->connect_error";
                    break;
                }

                $sql_result = $db->query(sqlcmd_getAvatar($_SESSION['username']));

                // Query failed
                if ($sql_result === FALSE) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 501");
                    $aResult['error'] = $db->error;
                }
                // Database accident or being attacked
                else if ($sql_result->num_rows === 0 || $sql_result->num_rows > 1) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 501");
                    $aResult['error'] = "Unexpected error! (Please report)";
                }
                else {
                    $link = $sql_result->fetch_assoc()["avatar"];
                    header($_SERVER['SERVER_PROTOCOL'] . " 200");
                    $aResult['result'] = "Refresh avatar succeed!";

                    if (strpos($link, "https://i.imgur.com/") === 0) {
                        $aResult['link'] = $link;
                    }
                    else $aResult['link'] = "https://i.imgur.com/9B9e2OY.png";
                }

                // Close the connection
                $db->close();
                break;

            case 'uploadImage':
                if (is_invalid('link')) {
                    $aResult['error'] = "Missing arguments!";
                }
                // Invalid link (Somebody attacks me)
                else if (strlen($_POST['link']) > 40 || strpos($_POST['link'], "https://i.imgur.com/") !== 0) {
                    if ($configs['debug'])
                        $aResult['error'] = "Invalid link.";
                }
                else {
                    $db = mysqli_connect($configs['host'],
                                         $configs['username'],
                                         $configs['password'],
                                         $configs['dbname']);

                    // Database connect failed
                    if (!$db) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = "Connect Error ($db->connect_errno) $db->connect_error";
                        break;
                    }

                    $db->query(sqlcmd_updateAvatar($_SESSION['username'], $_POST['link']));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    else {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['result'] = "Upload image succeed!";
                    }

                    // Close the connection
                    $db->close();
                }
                break;

            default:
                if ($configs['debug'])
                    $aResult['error'] = "Nonexistent action.";
        }
    }
    
    echo json_encode($aResult);
?>