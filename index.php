<?php

  // Require composer autoloader
  require __DIR__ . '/vendor/autoload.php';

  require __DIR__ . '/dotenv-loader.php';

  use Auth0\SDK\Auth0;

  $domain        = getenv('AUTH0_DOMAIN');
  $client_id     = getenv('AUTH0_CLIENT_ID');
  $client_secret = getenv('AUTH0_CLIENT_SECRET');
  $redirect_uri  = getenv('AUTH0_CALLBACK_URL');
  $audience      = getenv('AUTH0_AUDIENCE');
  $ip_id = getenv('CLIENT_ID');
  $ip_secret = getenv('CLIENT_SECRET');
  
  
  if($audience == ''){
    $audience = 'https://' . $domain . '/userinfo';
  }

  $auth0 = new Auth0([
    'domain' => $domain,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'audience' => $audience,
    'scope' => 'openid profile',
    'persist_id_token' => true,
    'persist_access_token' => true,
    'persist_refresh_token' => true,
  ]);

  $userInfo = $auth0->getUser();
  
  function callAPI($method,$token)
  {
	  $audience = getenv('AUTH0_AUDIENCE');
	  $curl = curl_init();
	 
				curl_setopt_array($curl, array(
				
				CURLOPT_URL => $audience.$method,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
					"content-type: application/json",
					"AUthorization: Bearer ".$token
				),
				));
			  
			  $data = curl_exec($curl);
			  $err = curl_error($curl);
              if($err)
			  {
				  return "cURL Error #:" . $err;
				  
			  }
			  else
			  {
				  return json_decode($data,true);
			  }
			  curl_close($curl);
  }

?>
<html>
    <head>
        <script src="http://code.jquery.com/jquery-3.1.0.min.js" type="text/javascript"></script>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- font awesome from BootstrapCDN -->
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">

        <link href="public/app.css" rel="stylesheet">
        
		<title>M.F.Aziz - Rules Per Application</title>


    </head>
    <body class="home">
        <div class="container">
            <div class="login-page clearfix">
              <?php if(!$userInfo): ?>
              <div class="login-box auth0-box before">
                <img src="https://i.cloudup.com/StzWWrY34s.png" />
                <h3>Auth0 Excercise 2</h3>
                <p>M.F.Aziz</p>
                <a id="qsLoginBtn" class="btn btn-primary btn-lg btn-login btn-block" href="login.php">Sign In</a>
              </div>
              <?php else: ?>
              <div class="logged-in-box auth0-box logged-in">
                <h1 id="logo"><img style='width:100px; margin-bottom:10px;' src="//cdn.auth0.com/samples/auth0_logo_final_blue_RGB.png" /></h1>
                <img style='width:100px;' class="avatar" src="<?php echo $userInfo['picture'] ?>"/>
                <h4>Welcome <span class="nickname"><?php echo $userInfo['nickname'] ?></span></h4>
				 <h4>Rules Per Application</h4>
				 <?php
				  $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://".$domain ."/oauth/token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\"grant_type\":\"client_credentials\",\"client_id\": \"vCRj8QlS5f9N2OZk0WyueHiI1R8WaEqm\",\"client_secret\": \"dsseT3MFqqVMQMH5Uo_vkzyz8902aQxOmp9Z6Lr85s9YUj_mauRbdkSSzmGZ6J6p\",\"audience\": \"https://mandhar.auth0.com/api/v2/\"}",
  CURLOPT_HTTPHEADER => array(
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
  exit;
  
} 
else {

				  $obj = json_decode($response);
				  $accessToken =  $obj->{'access_token'};
  
  
             $rules = callAPI("rules",$accessToken);
             $applications = callAPI("clients",$accessToken);			 
			
		    
           $refinedApplications = array();
			
			foreach($applications as $index => $app)
					{
						if($app['name']!='All Applications'){
							$data = array('name' => $app['name'],'clientId'=>$app['client_id'], 'rules' => array());
						    array_push($refinedApplications,$data);
						}
						
					}
			
		   
		
			
		   foreach($rules as $rule)
		   {
			  
			   $matches = array();
			   $matches1 = array();
			   $matches2 = array();
			   $matches3 = array();
			   
			   preg_match('/if\s*\(context\.clientName === \'([^\']+)\'\)/',$rule['script'] , $matches);
			   preg_match('/if\s*\(context\.clientName !== \'([^\']+)\'\)/',$rule['script'] , $matches1);
			   preg_match('/if\s*\(context\.clientId === \'([^\']+)\'\)/',$rule['script'] , $matches2);
			   preg_match('/if\s*\(context\.clientId !== \'([^\']+)\'\)/',$rule['script'] , $matches3);
			   
               //$filter = array_filter($matches);
			    if(count($matches)>0)
			    {
					$AllowedAppName = $matches[1];
			   
					 foreach($refinedApplications as $index => $app)
					 {
						 if($app['name']=== $AllowedAppName)
						 {
							array_push($refinedApplications[$index]['rules'],array('name' => $rule['name'], 'status' => $rule['enabled']));
						 }
					 }
			    }
				else if(count($matches1)>0)
			    {
					$notAllowedAppName = $matches1[1];
			   
					 foreach($refinedApplications as $index => $app)
					 {
						 if($app['name']=== $notAllowedAppName)
						 {
							array_push($refinedApplications[$index]['rules'],array('name' => $rule['name'], 'status' => $rule['enabled']));
						 }
					 }
			    }
				else if(count($matches2)>0)
			    {
					$AllowedAppNameId = $matches2[1];
			   
					 foreach($refinedApplications as $index => $app)
					 {
						 if($app['clientId']=== $AllowedAppNameId)
						 {
							array_push($refinedApplications[$index]['rules'],array('name' => $rule['name'], 'status' => $rule['enabled']));
						 }
					 }
			    }
				else if(count($matches3)>0)
			    {
					$notAllowedAppNameId = $matches3[1];
			   
					 foreach($refinedApplications as $index => $app)
					 {
						 if($app['clientId']=== $notAllowedAppNameId)
						 {
							array_push($refinedApplications[$index]['rules'],array('name' => $rule['name'], 'status' => $rule['enabled']));
						 }
					 }
			    }
				
			    else
			    {
				 
				 foreach($refinedApplications as $index => $app)
					{
						array_push($refinedApplications[$index]['rules'],array('name' => $rule['name'], 'status' => $rule['enabled']));
						
					} 
					
			   }
		   
		   }
		   
		   // echo "<pre>";
		   // print_r($refinedApplications);
		   // echo "</pre>";
		   
		   foreach($refinedApplications as $result)
		   {
			   echo "<hr><h4 style='color:blue; text-align:left;'> ".$result['name']."</h4>";
			   if(count($result['rules'])>0)
			   {
				   foreach($result['rules'] as $rule)
				   {
					   echo "<p style='font-size:14px; text-align:left;'>".$rule['name'];
					   if($rule['status']=='1'){
					   echo " Status: <span class='label label-success'>Enabled</success></p>";
					   }
					   else
					   {
						 echo " Status: <span class='label label-danger'>Disabled</span></p>";  
					   }
				   }
			   }
		   }
		  
		   
}
?>
				 
				 
				</div>
			  
              <?php endif ?>
            </div>
        </div>
    </body>
</html>
