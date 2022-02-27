#!/usr/bin/env php
<?php
error_reporting (E_ALL ^ E_NOTICE);
require('guest-dao.php');
$dao = new GuestDAO();
$options = getopt("ld:a:s:e:");
if(isset($options["c")) && $options["c"]) {
    $dao->cleanup();
} else if(isset($options["d"]) && $options["d"]) {
    // delete guest
    $dao->delete($options["d"]);
} else if(isset($options["a"]) && $options["a"]) {
    // add guest
    $guest_code = $options["a"];
    $attributes = Array("Cleartext-Password" => "guest");
    if($options["s"]) {
        $attributes["Simultaneous-Use"] = $options["s"];
    }
    if($options["e"]) {
        $attributes["Expiration"] = $options["e"];
    }
    $dao->create($guest_code, $attributes);
} else if(isset($options["l"])) {
    $guests = $dao->list();
    foreach($guests as $guest_code => $attributes) {
        printf("%s", str_pad($guest_code, 20));
        foreach($attributes as $attr => $value) {
            if($attr == "Cleartext-Password") continue;
            printf("%s=%s;", $attr, $value);
        }
        printf("\n");
    }
} else {
    $usage = <<<EOH
Usage: guest-manager-cli <command> [param]
  -l            List current guest codes
  -d <code>     Delete a guest code
  -a <code>     Add a guest code
  -s <num>      Simultaneous-Use por new guest code (use with -a)
  -e <date>     Expiration for new guest code (use with -a)
  -c            Cleanup expired guests
EOH;
    print($usage);
}
?>
