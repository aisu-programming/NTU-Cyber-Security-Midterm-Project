<?php
  session_start();

  // Ban users who is not login but try to visit this page
  // So this page only allow users who has JWT cookie
  if (!isset($_COOKIE['JWT'])) {
    unset($_SESSION['username']);
    header("Location: login.php");
    exit;
  }

  $_SESSION['randomNumber'] = mt_rand();
?>

<!DOCTYPE HTML>
<html>

  <head>
    <title>個人頁面</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
      @media only screen and (min-width: 576px) {
        .head-spacer {
          display: none;
        }
      }
    </style>
    <link rel="stylesheet" href="css/mycss.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="js/request.js"></script>
    <script src="js/profile.js"></script>
    <script src="js/check.js"></script>
    <script>
      r = <?php echo $_SESSION['randomNumber']; ?>;
      <?php // Check if user has JWT but so SESSION ?>
      if (getCookie('JWT') != "" && <?php echo isset($_SESSION['username']) * 1 ?> == 0) check('profile');
    </script>
    <script defer>
      updateAvatar();
    </script>
  </head>
  
  <body>

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
      <a class="navbar-brand p-0" href="/">
        <img src="logo.png" alt="Logo" style="width: 50px;">
        NTNU-Aisu
      </a>
      <ul class="navbar-nav">
        <li class="nav-item active" id="profile-btn">
          <a class="nav-link" href="/profile.php">個人頁面</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/comment.php?page=1">留言版</a>
        </li>
      </ul>
    </nav>

    <div class="container-fluid h-100" style="padding-top: 65px;">

      <?php if (isset($_SESSION['username'])) { ?>
        <div class="row head-spacer" style="height: 32px;"></div>
      <?php } else { ?>
        <div class="row head-spacer" style="height: 72px;"></div>
      <?php } ?>

      <div class="row justify-content-center align-content-end h-50">
        <img class="img-thumbnail" style="background: #cccccc; width: 250px; height: 250px;" alt="Avatar" id="avatar">
      </div>

      <div class="row justify-content-center align-content-start h-50">
        <div class="col-12 p-2 pt-4" align="center">
          <h4 class="m-0">點此上傳新頭像</h4>
        </div>
        <div class="col-12 p-2" align="center">
          <input type="file" class="form-control-file border" style="max-width: 300px;" accept="image/*" id="upload-image">
        </div>
        <div class="col-12 p-2" align="center">
          <button onclick="uploadImage()" class="btn btn-primary" id="upload-btn">
            <span class="spinner-border spinner-border-sm" style="display: none;" id="upload-spinner"></span>
            <a id="upload-text">上傳</a>
          </button>
        </div>
        <div class="col-12 p-2" align="center">
          <div>目前登入次數：<?php echo $_SESSION['loginTurn']; ?></div>
        </div>
      </div>

    </div>
  </body>

</html>