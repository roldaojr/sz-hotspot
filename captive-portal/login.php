<?php
error_reporting (E_ALL ^ E_NOTICE);
$redirectURL = $_GET["url"] ?? "";
require("ruckus-cp-auth.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $response = sz_hotspot_auth($_POST["UserName"], $_POST["Password"]);
  if($response->authorized && $_POST['remember']) {
    setcookie("cp_username", $_POST["UserName"], time()+86400);
    setcookie("cp_password", $_POST["Password"], time()+86400);
  }
} elseif($_COOKIE['cp_username'] && $_COOKIE['cp_password']) {
  $response = sz_hotspot_auth($_COOKIE['cp_username'], $_COOKIE['cp_password']);
  if($response->authorized && $redirectURL) {
    header("Location: $redirectURL");
    exit();
  }
}

if(!$response) {
    $response = sz_hotspot_auth_status();
}

if(!$response->authorized) {
  setcookie("cp_username", "", time()-3600);
  setcookie("cp_password", "", time()-3600);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php if($redirectURL && $response->authorized) { ?>
  <meta http-equiv="refresh" content="0;url=<?php echo $redirectURL ?>">
<?php } ?>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <title>Welcome to Wireless Network</title>
  <style type="text/css">
    body { padding-top: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="col-lg-6 col-md-8 col-sm-10 offset-lg-3 offset-md-2 offset-sm-1">
      <h2 class="page-header text-center">
        Welcome to Wireless Network
      </h2>
      <div class="card">
        <div class="card-body">
          <form method="POST" class="mb-0">
            <?php if($response->authorized) { ?>
            <div class="alert alert-success" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } elseif($response->error) { ?>
            <div class="alert alert-danger" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } elseif($response->ReplyMessage) { ?>
            <div class="alert alert-info" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } ?>
            <?php if($response->authorized) { ?>
            <p>
              <b>Username:</b> <?php echo $response->{'UE-Username'} ?>
            </p>
            <div class="d-grid gap-2">
              <?php if($redirectURL) { ?>
              <a href="<?php echo $redirectURL ?>" class="btn btn-primary">Continue</a>
              <?php } ?>
              <button type="submit" name="action" value="logoff" class="btn btn-danger">Logoff</a>
            </div>
            <?php } else { ?>
            <div class="mb-3">
              <label for="username" class="form-label<?php echo ($response->error)?' text-danger':''?>">Username</label>
              <input type="text" name="UserName" class="form-control<?php echo ($response->error)?' is-invalid':''?>">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label<?php echo ($response->error)?' text-danger':''?>">Password</label>
              <input type="password" name="Password" class="form-control<?php echo ($response->error)?' is-invalid':''?>">
            </div>
            <div class="d-grid">
              <button class="btn btn-primary" type="submit">Login</button>
            </div>
            <?php } ?>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
