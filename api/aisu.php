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
    $url = array();
    array_push($url, $configs['referer']);
    array_push($url, $configs['referer'] . "index.php");
    if ($_SERVER['HTTP_REFERER'] != $url[0] && $_SERVER['HTTP_REFERER'] != $url[1]) {
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

            case 'updateTotalLoginTurn':
                
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

                $sql_result = $db->query(sqlcmd_getNumber('total_login_turn'));

                // Query failed
                if ($sql_result === FALSE) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 501");
                    $aResult['error'] = $db->error;
                }
                // No number found
                else if ($sql_result->num_rows === 0 || $sql_result->num_rows > 1) {
                    header($_SERVER['SERVER_PROTOCOL'] . " 501");
                    $aResult['error'] = "Unexpected error! (Please report)";
                }
                else {
                    header($_SERVER['SERVER_PROTOCOL'] . " 200");
                    $aResult['result'] = $sql_result->fetch_assoc()['value'];
                }
                
                //Close the connection
                $db->close();

                break;

            default:
                if ($configs['debug'])
                    $aResult['error'] === "Nonexistent action.";
        }
    }

    echo json_encode($aResult);

?>