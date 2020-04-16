<?php

    declare(strict_types=1);
    $configs = include('config/config.php');
    include('lib/sqlcmd.php');
    include('lib/jwt.php');

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
    array_push($url, $configs['referer'] . "comment.php?page=");
    array_push($url, $configs['referer'] . "post.php");
    if (strpos($_SERVER['HTTP_REFERER'], $url[0]) !== 0 && $_SERVER['HTTP_REFERER'] != $url[1]) {
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

            case 'getComment':
                if (is_invalid('page')) {
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

                    $sql_result = $db->query(sqlcmd_createCommentTable());
                    if ($sql_result === FALSE && $db->error !== "Table 'comment' already exists") {
                        $aResult['error'] = $db->error;
                        break;
                    }

                    $page = (int) $_POST['page'];
                    if ($page < 1) {
                        if ($configs['debug'])
                            $aResult['error'] = "Invalid page!";
                        break;
                    }
                    $sql_result = $db->query(sqlcmd_getComment($page));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    else {
                        $aResult['comment'] = array();
                        while ($row = $sql_result->fetch_assoc()) {
                            $result = array('username'=>stringDecode($row['username']), 
                                            'content'=>stringDecode($row['content']));
                            array_push($aResult['comment'], json_encode($result));
                        }
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['result'] = "Succeed!";
                    }

                    // Close the connection
                    $db->close();
                }
                break;

            case 'getComment':
                if (is_invalid('page')) {
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

                    // Create table 'comment'
                    $sql_result = $db->query(sqlcmd_createCommentTable());
                    if ($sql_result === FALSE && $db->error !== "Table 'comment' already exists") {
                        $aResult['error'] = $db->error;
                        break;
                    }

                    // Process $_POST['page'], preventing SQL injection
                    $page = (int) $_POST['page'];
                    if ($page < 1) {
                        if ($configs['debug'])
                            $aResult['error'] = "Invalid page!";
                        break;
                    }
                    $sql_result = $db->query(sqlcmd_getComment($page));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    else {
                        $aResult['comment'] = array();
                        while ($row = $sql_result->fetch_assoc()) {
                            $result = array('username'=>stringDecode($row['username']), 
                                            'content'=>stringDecode($row['content']));
                            array_push($aResult['comment'], json_encode($result));
                        }
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['result'] = "Succeed!";
                    }

                    // Close the connection
                    $db->close();
                }
                break;

            case 'postComment':
                if (!isset($_SESSION['username']) || !isset($_COOKIE['JWT'])) {
                    $aResult['error'] = "Action unauthorized! (Please login first)";
                }
                else if (strlen($_POST['content']) > 180) {
                    if ($configs['debug'])
                        $aResult['error'] = "Content too long!";
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

                    // Create table 'comment'
                    $sql_result = $db->query(sqlcmd_createCommentTable());
                    if ($sql_result === FALSE && $db->error !== "Table 'comment' already exists") {
                        $aResult['error'] = $db->error;
                        break;
                    }

                    $sql_result = $db->query(sqlcmd_addComment($_SESSION['username'], $_POST['content']));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    else {
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['result'] = "Succeed!";
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