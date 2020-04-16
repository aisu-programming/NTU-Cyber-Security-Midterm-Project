<?php
  session_start();
  $_SESSION['randomNumber'] = mt_rand();
?>

<!DOCTYPE HTML>
<html>

  <head>
    <title>首頁</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/mycss.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="js/request.js"></script>
    <script src="js/login.js"></script>
    <script src="js/redirect.js"></script>
    <script>

      r = <?php echo $_SESSION['randomNumber']; ?>;

      if (<?php echo isset($_SESSION['username']) * 1 ?> == 0) checkRedirect('index');

      $(document).ready(function () {
        if (<?php echo isset($_SESSION['username']) * 1 ?> == 1) {
          document.getElementById("profile-btn").style.display = "inherit";
          document.getElementById("login-btn").style.display = "none";
          document.getElementById("register-btn").style.display = "none";
        }
      })

    </script>
  </head>

  <body>
    <div class="container-fluid h-100">
      <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
        <a class="navbar-brand p-0" href="/">
          <img src="logo.png" alt="Logo" style="width: 50px;">
          NTNU-Aisu
        </a>
        <ul class="navbar-nav">
          <li class="nav-item" id="profile-btn" style="display: none;">
            <a class="nav-link" href="/profile.php">個人頁面</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/comment.php">留言版</a>
          </li>
          <li class="nav-item" id="login-btn" style="display: inherit;">
            <a class="nav-link" href="/login.php">登入</a>
          </li>
          <li class="nav-item" id="register-btn" style="display: inherit;">
            <a class="nav-link" href="/register.php">註冊</a>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link disabled" href="#">Disabled</a>
          </li> -->
        </ul>
      </nav>
    </div>
  </body>

</html>