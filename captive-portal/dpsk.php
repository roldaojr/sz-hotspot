<?php
error_reporting (E_ALL ^ E_NOTICE);

function randomPassword($length = 8) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function generatePassword($mac) {
    $type = $_ENV["DB_TYPE"];
    $host = $_ENV["DB_HOST"];
    $port = $_ENV["DB_PORT"];
    $dbname = $_ENV["DB_NAME"];
    $dbuser = $_ENV["DB_USER"];
    $dbpass = $_ENV["DB_PASSWORD"];
    $_db = new PDO(
        "${type}:host=${host}${($port)?";port=${port}":""};dbname=${dbname};",
        $dbuser,
        $dbpass,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    $quoted_mac = $_db->quote($mac);
    $password = randomPassword(8);
    $_db->exec("delete from radreply where username = $quoted_mac and attribute = 'Ruckus-Dpsk'");
    $_db->exec(
        "insert into radreply(username, attribute, op, value) values ($quoted_mac, 'Ruckus-Dpsk', ':=', '$password')"
    );
    return $password;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = generatePassword(strtolower(strtr($_GET["client_mac"], Array(":" => "", "-" => ""))));
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <title>Generate Device Password</title>
  <style type="text/css">
    body { padding-top: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="col-lg-6 col-md-8 col-sm-10 offset-lg-3 offset-md-2 offset-sm-1">
      <h2 class="page-header text-center">
        Generate Device Password
      </h2>
      <div class="card">
        <div class="card-body">
          <form method="POST" class="mb-0">
            <?php if($password) { ?>
            <div class="alert alert-success" role="alert"><b>WPA-PSK password:</b> <?php echo $password ?></div>
            <?php } else { ?>
            <div class="alert alert-info" role="alert">Press the button to create a password</div>
            <?php } ?>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Generate WPA-PSK password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
