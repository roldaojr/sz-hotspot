<?php
// Show messages by ResponseCode
$replyMessages = Array(
  100 => "Type your username and password to access the Internet",
  101 => "Authorized. You can now browse the Internet",
  200 => "Type your username and password to access the Internet",
  201 => "Authorized. You can now browse the Internet",
  301 => "Invalid username or password"
);
// Request hotspot auth info
function sz_hotspot_auth($username, $password) {
  $response = sz_nbi_request(Array(
    "RequestCategory" => "UserOnlineControl",
    "RequestType" =>  ($_REQUEST['action'] == "logoff") ? "Logout" : "Login",
    "UE-Username" => $username,
    "UE-Password" => $password
  ));
  if(in_array($response->ResponseCode, Array(101, 201))) {
    $response->authorized = true;
  } else {
    $response->authorized = false;
  }
  if($response->ResponseCode >= 300) {
    $response->error = true;
  } else {
    $response->error = false;
  }
  return $response;
}

function sz_hotspot_auth_status() {
  $response = sz_nbi_request(Array(
    "RequestCategory" => "UserOnlineControl",
    "RequestType" =>  "Status",
  ));
  if(in_array($response->ResponseCode, Array(101, 201))) {
    $response->authorized = true;
  } else {
    $response->authorized = false;
  }
  if($response->ResponseCode >= 300) {
    $response->error = true;
  } else {
    $response->error = false;
  }
  return $response;
}

// Call Ruckus Smart Zone Nouthbound interface API
function sz_nbi_request($request) {
  global $replyMessages;
  $nbiIP = $_GET["nbiIP"]; # smart zone NBI IP
  $nbiUsername = $_ENV["NBI_USERNAME"]; # smart zone NBI user name
  $nbiPassword = $_ENV["NBI_PASSWORD"]; # smart zone NBI password
  // Change default reply messages
  $json_request = json_encode(array_merge(
    Array(
      "Vendor" => "ruckus",
      "APIVersion" => "1.0",
      "RequestUserName" => $nbiUsername,
      "RequestPassword" => $nbiPassword,
      "UE-IP" => $_GET["uip"],
      "UE-MAC" => $_GET["client_mac"],
      "UE-Proxy" => $_GET["proxy"]
    ), $request
  ));
  $curl = curl_init();  
  curl_setopt_array($curl, array(
    CURLOPT_VERBOSE => 1,
    CURLOPT_URL => "https://${nbiIP}:9443/portalintf",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $json_request,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => Array(
       "Content-Type: application/json",
       "Accept: application/json",
    ),
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_CONNECTTIMEOUT => 2
  ));
  $content = curl_exec($curl);
  if ($content === false) {
    return (object) Array(
      "ResponseCode" => 500,
      "ReplyMessage" => "Connection failed"
    );
  }
  curl_close($curl);
  $response = json_decode($content);
  if(in_array($response->ResponseCode, array_keys($replyMessages))) {
    $response->ReplyMessage = $replyMessages[$response->ResponseCode];
  }
  return $response;
}
