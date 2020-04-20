<?php
  session_start();

  if (!isset($_COOKIE['JWT'])) unset($_SESSION['username']);

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
    <script src="js/aisu.js"></script>
    <script src="js/check.js"></script>
    <script>
      r = <?php echo $_SESSION['randomNumber']; ?>;
      if (getCookie('JWT') != "" && <?php echo isset($_SESSION['username']) * 1 ?> == 0) check('index');
      updateTotalLoginTurn();
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

          <?php if (isset($_SESSION['username']) && isset($_COOKIE['JWT'])) { ?>
            <li class="nav-item">
              <a class="nav-link" href="/profile.php">個人頁面</a>
            </li>
          <?php } ?>

          <li class="nav-item">
            <a class="nav-link" href="/comment.php?page=1">留言版</a>
          </li>

          <?php // Not having both SESSION and JWT ?>
          <?php if (!isset($_SESSION['username']) || !isset($_COOKIE['JWT'])) { ?>
            <li class="nav-item">
              <a class="nav-link" href="/login.php">登入</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/register.php">註冊</a>
            </li>
          <?php } ?>

        </ul>
      </nav>

      <div class="row justify-content-start align-content-center h-100" style="padding-top: 65px;">
        <div class="col-6 col-md-5">
          <div class="row justify-content-end align-content-center h-100" style="padding-left: 15px;">
            <img class="img-thumbnail" style="max-height: 600px; object-fit: contain;" src="me.jpg" alt="Me">
          </div>
        </div>
        <div class="col-6 col-md-7">
          <div class="row justify-content-start align-content-center h-100">

            <div class="col-12" style="margin-bottom: 15px;">
              <div class="card" id="login-form" style="max-width: 500px;">
                <div class="card-header" align="center">個人簡介</div>
                <div class="card-body">
                  <div class="form-group text-left">
                    <div>姓名：<br/>
                    　洪偉倫<br/>
                    綽號：<br/>
                    　冰塊<br/><br/>
                    目前就讀：<br/>
                    　台師大資工大二</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="card" id="login-form" style="max-width: 500px;">
                <div class="card-header" align="center">關於網站</div>
                <div class="card-body">
                  <div class="form-group text-left">
                    <div>目前總登入人數：</div>
                    <div id="total_login_turn"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      
    </div>
  </body>

</html>