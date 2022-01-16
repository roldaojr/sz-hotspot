<?php
error_reporting (E_ALL ^ E_NOTICE);
# Page title
$title = "Welcome to Guest Wireless Network";
# Redirect after authorization
$redirectURL = $_GET["url"] ? $_GET["url"] : "http://connectivitycheck.gstatic.com/generate_204";

require("ruckus-cp-auth.php");

function find_username($guest_code) {
  $host = $_ENV["MYSQL_HOST"];
  $port = $_ENV["MYSQL_PORT"];
  $dbname = $_ENV["MYSQL_DATABASE"];
  $username = $_ENV["MYSQL_USER"];
  $password = $_ENV["MYSQL_PASSWORD"];
  $conn = new mysqli($host, $username, $password, $dbname, $port);
  $result = $conn->query("select username from radcheck where attribute = 'Cleartext-Password' and value = '$guest_code'");
  if($result->num_rows > 0) {
    return $result->fetch_object()->username;
  }
  return null;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = find_username($_POST["guest_code"]);
  if(!$username) {
    $result = (object) Array(
      "error" => true,
      "message" => "Invalid guest code"
    );
  } else {
    $result = sz_hotspot_auth($username, $_POST["guest_code"]);
  }
}
// captive portal page
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <title><?php echo $title ?></title>
  <style type="text/css">
    body { padding-top: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="col-sm-6 offset-sm-3">
      <h2 class="page-header text-center">
        <?php echo $title ?>
      </h2>
      <div class="card">
        <div class="card-body">
          <?php if($result && !$result->error) { ?>
          <p>Access authorized.</p>
          <div class="d-grid">
            <a href="<?php echo $redirectURL ?>" class="btn btn-primary">Continue</a>
          </div>
          <?php } else { ?>
          <form method="POST">
            <?php if($result->error) { ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $result->message ?>
            </div>
            <?php } ?>
            <div class="mb-3">
              <label for="guest_code" class="form-label">Guest code</label>
              <input type="text" name="guest_code" class="form-control">
            </div>
            <div class="d-grid">
              <button class="btn btn-primary" type="submit">Submit</button>
            </div>
          </form>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
