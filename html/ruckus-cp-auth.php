<?php
// Call Ruckus Smart Zone API to authenticate captivel portal
function sz_hotspot_auth($username, $password) {
  $nbiIP = $_GET["nbiIP"]; # smart zone NBI IP
  $nbiUsername = $_ENV["NBI_USERNAME"]; # smart zone NBI user name
  $nbiPassword = $_ENV["NBI_PASSWORD"]; # smart zone NBI passwrod
  $request = json_encode(Array(
    "Vendor" => "ruckus",
    "APIVersion" => "1.0",
    "RequestUserName" => $nbiUsername,
    "RequestPassword" => $nbiPassword,
    "RequestCategory" => "UserOnlineControl",
    "RequestType" =>  "Login",
    "UE-IP" => $_GET["uip"],
    "UE-MAC" => $_GET["client_mac"],
    "UE-Proxy" => $_GET["proxy"],
    "UE-Username" => $username,
    "UE-Password" => $password
  ));
  $curl = curl_init();
  curl_setopt_array($curl, array(
      CURLOPT_VERBOSE => 1,
      CURLOPT_URL => "https://${nbiIP}:9443/portalintf",
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $request,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => Array(
         "Content-Type: application/json",
         "Accept: application/json",
      ),
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CONNECTTIMEOUT => 2
  ));
  try {
    $content = curl_exec($curl);
    if ($content === false) {
        throw new Exception(curl_error($curl), curl_errno($curl));
    }
    curl_close($curl);
    $response = json_decode($content);
    // Check response code
    if(in_array($response->ResponseCode, Array(101, 201))) {
      return (object) Array(
        "error" => false,
        "message" => "Access allowed"
      );
    }
    // Access rejected
    if($response->ResponseCode == 300) {
      $errorMsg = "Guest not found!";
    } elseif($response->ResponseCode == 301) {
      $errorMsg = "Invalid credentials!";
    } else {
      $errorMsg = "Error {$response->ResponseCode}: {$response->ReplyMessage}";
    }
  } catch(Exception $ex) {
    $errorMsg = "Connection failed!";
  }
  return (object) Array(
    "error" => true,
    "message" => $errorMsg
  );
}
?>
