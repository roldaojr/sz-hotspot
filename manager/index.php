<?php
require('guest-dao.php');
$dao = new GuestDAO();
if($_SERVER['REQUEST_METHOD'] == "POST") {
  $result = new StdClass();
  if($_POST["action"] == "add") {
    try {
      $dao->create($_POST["guest_code"], Array(
        "Cleartext-Password" => "guest",
        "Simultaneous-Use" => $_POST["simultaneous_use"],
        "Expiration" => "{$_POST["expration_date"]} {$_POST["expration_time"]}"
      ));
      $result->message = "Guest created";
      $result->success = true;
    } catch(Throwable $ex) {
      $result->message = $ex->getMessage();
      $result->error = true;
    }
  } else if($_POST["action"] == "clean") {
    try {
      $dao->cleanup();
      $result->message = "Expired guets removed";
      $result->success = true;
    } catch(Throwable $ex) {
      $result->message = $ex->getMessage();
      $result->error = true;
    }
  } else if($_POST["delete"]) {
    try {
      $dao->delete($_POST["delete"]);
      $result->message = "Guest deleted";
      $result->success = true;
    } catch(Throwable $ex) {
      $result->message = $ex->getMessage();
      $result->error = true;
    }
  }
}
$guests = $dao->list();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php if($redirectURL && $response->authorized) { ?>
  <meta http-equiv="refresh" content="0;url=<?php echo $redirectURL ?>">
<?php } ?>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <title>Simple Guest Manager</title>
  <style type="text/css">
    body { padding-top: 20px; }
  </style>
</head>
<body>
  <form class="container" method="POST">
    <h2 class="page-header text-center">
      Simple Guest Manager
    </h1>
    <?php if($result) { ?>
    <div class="alert alert-<?php echo $result->error ? 'danger' : 'success' ?>"><?php echo $result->message ?></div>
    <?php } ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Guest Code</th>
          <th class="col-2">Simultaneous-Use</th>
          <th class="col-4">Expiration</th>
          <th class="col-1">Action</th>
        </tr>
      </thead>
      <?php foreach($guests as $guest_code => $attributes) { ?>
      <tr>
        <td><?php echo $guest_code ?></td>
        <td><?php echo $attributes["Simultaneous-Use"] ?></td>
        <td><?php echo $attributes["Expiration"] ?></td>
        <td>
          <div class="d-grid">
            <button type="submit" class="btn btn-sm btn-danger" name="delete" value="<?php echo $guest_code ?>">
              Delete
            </button>
          </div>
        </td>
      </tr>
      <?php } ?>
      <tr>
        <td>
          <div class="row g-2">
            <div class="col-lg-10">
              <input type="text" name="guest_code" class="form-control">
            </div>
            <div class="col-lg-2 d-grid">
              <button type="button" class="btn btn-sm btn-primary" id="random-guest-code">
                Random
              </button>
            </div>
          </div>
        </td>
        <td>
          <input type="number" name="simultaneous_use" value="1" class="form-control">
        </td>
        <td>
          <div class="row g-2">
            <div class="col-lg-8">
              <input type="date" name="expration_date" class="form-control">
            </div>
            <div class="col-lg-4">
              <input type="time" name="expration_time" class="form-control">
            </div>
          </div>
        </td>
        <td>
          <div class="d-grid">
            <button type="submit" class="btn btn-success" name="action" value="add">
              Add
            </button>
          </div>
        </td>
      </tr>
    </table>
    <button type="submit" class="btn btn-dark" name="action" value="clean">
      Remove expired guests
    </button>
  </form>
</body>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript">
function getRandomNumber(length) {
    var randomChars = '0123456789';
    var result = '';
    for ( var i = 0; i < length; i++ ) {
        result += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
    }
    return result;
}

$(function() {
  $("#random-guest-code").click(function() {
    $("input[name=guest_code]").val(getRandomNumber(8))
  })
})
</script>
</html>
