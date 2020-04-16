<?php
  session_start();

  if (!isset($_COOKIE['JWT'])) {
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
    <link rel="stylesheet" href="css/mycss.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="js/request.js"></script>
    <script src="js/profile.js"></script>
    <script src="js/redirect.js"></script>
    <script>
      r = <?php echo $_SESSION['randomNumber']; ?>;
      if (<?php echo isset($_SESSION['username']) * 1 ?> == 0) checkRedirect('profile');
    </script>
    <script defer>
      updateAvatar();
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
          <li class="nav-item active" id="profile-btn">
            <a class="nav-link" href="/profile.php">個人頁面</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/comment.php">留言版</a>
          </li>
        </ul>
      </nav>
      <div class="row justify-content-center h-100">
        <div class="col-3 col-md-2 h-100">
          <div class="row align-content-end h-50">
            <img class="img-thumbnail" style="background: #2eb8b8;" alt="Avatar" id="avatar">
          </div>
          <div class="row justify-content-start align-content-start h-50">
            <div class="col-12 pt-2 pl-0 pr-0">
              <label for="upload-image">上傳新頭像：</label>
            </div>
            <div class="col-12 p-0" align="center">
              <input type="file" class="form-control-file border" accept="image/*" id="upload-image">
            </div>
            <div class="col-12 pt-2 pl-0 pr-0" align="end">
              <button onclick="uploadImage()" class="btn btn-primary" id="upload-btn">
                <span class="spinner-border spinner-border-sm" style="display: none;" id="upload-spinner"></span>
                <a id="upload-text">上傳</a>
              </button>
            </div>
          </div>
        </div>
        <div class="col-7 col-md-6"></div>
      </div>
    </div>
  </body>

</html>