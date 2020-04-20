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
    $url = $configs['referer'] . "login.php";
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

            case 'login':
                if (is_invalid('username') || is_invalid('password')) {
                    $aResult['error'] = "Missing arguments!";
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

                    $sql_result = $db->query(sqlcmd_getUser($_POST['username'], $_POST['password']));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    // No user found
                    else if ($sql_result->num_rows === 0) {
                        $aResult['error'] = "Login failed!";
                    }
                    // Database accident or being attacked
                    else if ($sql_result->num_rows > 1) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = "Unexpected error! (Please report if you are not attacking me)";
                    }
                    else {
                        $jwt_result = jwt_create($_POST['username'],
                                                 $configs['isser'],
                                                 $configs['exp'],
                                                 $configs['key']);
                        if (strpos($jwt_result, "Error:") === 0) {
                            $aResult['error'] = $jwt_result . " (Please report)";
                        }
                        else {
                            header($_SERVER['SERVER_PROTOCOL'] . " 200");
                            $aResult['result'] = "Login succeed with '" . $_POST['username'] . "'.";
                        }
                    }
                    
                    //Close the connection
                    $db->close();
                }
                break;

            default:
                if ($configs['debug'])
                    $aResult['error'] === "Nonexistent action.";
        }
    }

    echo json_encode($aResult);

?>