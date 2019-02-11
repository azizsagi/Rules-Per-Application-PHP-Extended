<?php



  use Auth0\SDK\Auth0;

  $domain        = getenv('AUTH0_DOMAIN');
  $client_id     = getenv('AUTH0_CLIENT_ID');
  $client_secret = getenv('AUTH0_CLIENT_SECRET');
  $redirect_uri  = getenv('AUTH0_CALLBACK_URL');
  $audience      = getenv('AUTH0_AUDIENCE');
  $ip_id = getenv('CLIENT_ID');
  $ip_secret = getenv('CLIENT_SECRET');
  
  echo "<h1>Rules Per Application</h1>";
  
  $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://".$domain ."/oauth/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\"grant_type\":\"client_credentials\",\"client_id\": \"DMIVDQhGASysPTXKfbLSHCs4rSJ7gW2s\",\"client_secret\": \"YOUR_CLIENT_SECRET\",\"audience\": \"https://mandhar.auth0.com/api/v2/\"}",
  CURLOPT_HTTPHEADER => array(
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  print_r($response);
}
  
  
  