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
    array_push($url, $configs['referer'] . "comment.php?page=");
    array_push($url, $configs['referer'] . "postComment.php");
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

            case 'getComments':
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
                        $db->close();
                        break;
                    }

                    // Process $_POST['page'], preventing SQL injection
                    $page = (int) $_POST['page'];
                    if ($page < 1) {
                        if ($configs['debug'])
                            $aResult['error'] = "Invalid page!";
                        $db->close();
                        break;
                    }
                    $sql_result = $db->query(sqlcmd_getComments($page));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    // Being attacked
                    else if ($sql_result->num_rows == 0 && $page != 1) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 403");
                        $aResult['error'] = "Nope! This page got nothing.";
                    }
                    // Database accident
                    else if ($sql_result->num_rows > 11) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = "Unexpected error! (Please report if you are not attacking me)";
                    }
                    else {
                        if ($sql_result->num_rows == 11) $aResult['next'] = true;
                        else $aResult['next'] = false;

                        $aResult['comments'] = array();
                        $counter = 0;
                        while ($row = $sql_result->fetch_assoc()) {

                            $counter += 1;
                            if ($counter == 11) break;

                            if ($row['alive']) {
                                if ($_SESSION['username'] == stringDecode($row['username'])) $editable = true;
                                else $editable = false;
                                $comment = array('id'=>$row['id'], 
                                                 'avatar'=>$row['avatar'],
                                                 'username'=>stringDecode($row['username']), 
                                                 'title'=>stringDecode($row['title']), 
                                                 'content'=>stringDecode($row['content']), 
                                                 'editable'=>$editable);
                            }
                            else $comment = array('id'=>0);

                            array_push($aResult['comments'], json_encode($comment));
                        }
                        header($_SERVER['SERVER_PROTOCOL'] . " 200");
                        $aResult['result'] = "Succeed!";
                    }

                    // Close the connection
                    $db->close();
                }
                break;

            case 'deleteComment':
                if (is_invalid('id')) {
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
                        $db->close();
                        break;
                    }

                    // Get original data of the comment from database
                    $id = (int) $_POST['id'];
                    if ($id < 1) {
                        if ($configs['debug'])
                            $aResult['error'] = "Invalid id!";
                        $db->close();
                        break;
                    }
                    $sql_result = $db->query(sqlcmd_getCommentById($id));

                    // Query failed
                    if ($sql_result === FALSE) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = $db->error;
                    }
                    // No comment found
                    else if ($sql_result->num_rows === 0) {
                        if ($configs['debug'])
                            $aResult['error'] = "Invalid id, no comment founded!";
                    }
                    // Database accident or being attacked
                    else if ($sql_result->num_rows > 1) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 501");
                        $aResult['error'] = "Unexpected error! (Please report if you are not attacking me)";
                    }
                    // Authority check
                    else if ($_SESSION['username'] != stringDecode($sql_result->fetch_assoc()['username'])) {
                        header($_SERVER['SERVER_PROTOCOL'] . " 403");
                        if ($configs['debug'])
                            $aResult['error'] = "Unauthorized action!";
                    }
                    else {
                        // Delete comment: Set alive to fasle
                        $sql_result = $db->query(sqlcmd_deleteComment($id));

                        // Query failed
                        if ($sql_result === FALSE) {
                            header($_SERVER['SERVER_PROTOCOL'] . " 501");
                            $aResult['error'] = $db->error;
                        }
                        else {
                            header($_SERVER['SERVER_PROTOCOL'] . " 200");
                            $aResult['result'] = "Succeed!";
                        }
                    }

                    // Close the connection
                    $db->close();
                }
                break;

            case 'postComment':
                if (!isset($_SESSION['username']) || !isset($_COOKIE['JWT'])) {
                    $aResult['error'] = "Action unauthorized! (Please login first)";
                }
                else if (is_invalid('title') || is_invalid('content')) {
                    $aResult['error'] = "Missing arguments!";
                }
                else if (strlen($_POST['title']) > 30) {
                    if ($configs['debug'])
                        $aResult['error'] = "Title too long!";
                }
                else if (strlen($_POST['content']) > 600) {
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
                        $db->close();
                        break;
                    }

                    $sql_result = $db->query(sqlcmd_addComment($_SESSION['username'], $_POST['title'], $_POST['content']));

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