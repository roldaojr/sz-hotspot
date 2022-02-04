<?php
error_reporting (E_ALL ^ E_NOTICE);
$redirectURL = $_GET["url"] ? $_GET["url"] : "";
require("ruckus-cp-auth.php");

$replyMessages = Array(
  100 => "Type the guest code to access the Internet",
  101 => "Authorized. You can now browse the Internet",
  200 => "Type the guest code to access the Internet",
  201 => "Authorized. You can now browse the Internet",
  301 => "Invalid guest code"
);

function get_username($guest_code) {
  $host = $_ENV["MYSQL_HOST"];
  $port = $_ENV["MYSQL_PORT"];
  $dbname = $_ENV["MYSQL_DATABASE"];
  $dbuser = $_ENV["MYSQL_USER"];
  $dbpass = $_ENV["MYSQL_PASSWORD"];
  $conn = new mysqli($host, $dbuser, $dbpass, $dbname, $port);
  // find user by guest code
  $result = $conn->query("select username from radcheck where attribute = 'Cleartext-Password' and value = '{$conn->real_escape_string($guest_code)}'");
  if($result->num_rows > 0) {
    return $result->fetch_object()->username;
  } else {
    return null;
  }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = get_username($_POST["guest_code"]);
  $response = sz_hotspot_auth($username, $_POST["guest_code"]);
} else {
  $response = sz_hotspot_auth_status();
}

// captive portal page
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
  <title>Welcome to Guest Wireless Network</title>
  <style type="text/css">
    body { padding-top: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="col-lg-6 col-md-8 col-sm-10 offset-lg-3 offset-md-2 offset-sm-1">
      <h2 class="page-header text-center">
        Welcome to Guest Wireless Network
      </h2>
      <div class="card">
        <div class="card-body">
          <form method="POST">
            <?php if($response) { ?>
            <?php if($response->authorized) { ?>
            <div class="alert alert-success" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } elseif($response->error) { ?>
            <div class="alert alert-danger" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } else { ?>
            <div class="alert alert-info" role="alert"><?php echo $response->ReplyMessage ?></div>
            <?php } ?>
            <?php } ?>
            <?php if($response->authorized) { ?>
            <p>
              <b>Username:</b> <?php echo $response->{'UE-Username'} ?>
            </p>
            <div class="d-grid gap-2">
              <?php if($redirectURL) { ?>
              <a href="<?php echo $redirectURL ?>" class="btn btn-primary">Continue</a>
              <?php } ?>
              <button type="submit" name="action" value="disconnect" class="btn btn-danger">Disconnect</a>
            </div>
            <?php } else { ?>
            <div class="mb-3">
              <label for="guest_code" class="form-label<?php echo ($response->error)?' text-danger':''?>">Guest code</label>
              <input type="text" name="guest_code" autocomplete="off" class="form-control<?php echo ($response->error)?' is-invalid':''?>">
            </div>
            <div class="d-grid">
              <button class="btn btn-primary" type="submit">Submit</button>
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
