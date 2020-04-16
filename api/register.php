<?php

    declare(strict_types=1);
    $configs = include('config/config.php');
    include('lib/jwt.php');
    include('lib/sqlcmd.php');

    function is_invalid(string $argsName) : bool {
        if (!isset($_POST[$argsName]) || $_POST[$argsName] === '') return true;
        else return false;
    }

    // Prevent users from visiting this URL by methods except POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header($_SERVER['SERVER_PROTOCOL'] . " 403");
        if ($configs['debug']) {
            $aResult['error'] = 'Invalid request method.';
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
    $url = $configs['referer'] . "register.php";
    if ($_SERVER['HTTP_REFERER'] != $url) {
        if ($configs['debug'])
            $aResult['error'] = 'Unauthorized referer.';
    }
    // Check random number
    else if ($_POST['r'] != $_SESSION['randomNumber']) {
        if ($configs['debug'])
            $aResult['error'] = 'Wrong random number.';
    }
    // Check data has action value
    else if (is_invalid('action')) {
        if ($configs['debug'])
            $aResult['error'] = 'Missing action.';
    }
    else {

        switch($_POST['action']) {

            case 'register':
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
                    
                    $sql_result = $db->query(sqlcmd_createUserTable());
                    if ($sql_result === FALSE && $db->error !== "Table 'user' already exists") {
                        $aResult['error'] = $db->error;
                        break;
                    }

                    $sql_result = $db->query(sqlcmd_checkUserExist($_POST['username']));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    // Username has already been taken
                    else if ($sql_result->num_rows === 1) {
                        $aResult['error'] = "Username has been taken!";
                    }
                    else {
                        $sha512_pwd = hash('sha512', $_POST['password']);
                        $sql_result = $db->query(sqlcmd_addUser($_POST['username'], $sha512_pwd));

                        // Query failed
                        if ($sql_result === FALSE) {
                            header($_SERVER['SERVER_PROTOCOL'] . " 501");
                            $aResult['error'] = $db->error;
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
                                $aResult['result'] = "Register succeed by '$username'.";
                            }
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