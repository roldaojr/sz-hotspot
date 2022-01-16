<?php
error_reporting (E_ALL ^ E_NOTICE);
# Page title
$title = "Welcome to Wireless Network";
# Redirect after authorization
$redirectURL = $_GET["url"] ? $_GET["url"] : "http://connectivitycheck.gstatic.com/generate_204";

require("ruckus-cp-auth.php");

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $result = sz_hotspot_auth($_POST["username"], $_POST["password"]);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php if($redirectURL) { ?>
  <meta http-equiv="refresh" content="0;url=<?php echo $redirectURL ?>">
<?php } ?>
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
            <?php if($result->message) { ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $result->message ?>
            </div>
            <?php } ?>
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" name="username" class="form-control">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" class="form-control">
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
