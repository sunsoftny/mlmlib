<?php

session_start();
$subdomain_enble=$_SESSION['site']['subdomain_enble'];
if($subdomain_enble=='1'){
	session_name("some_session_name");
	session_set_cookie_params(0, '/', '.'.$_SERVER['HTTP_HOST']);
}

require 'autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

if (isset($_REQUEST['oauth_verifier'], $_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] == $_SESSION['oauth_token']) {	
	$request_token = [];
	$request_token['oauth_token'] = $_SESSION['oauth_token'];
	$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
	$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
	$_SESSION['access_token'] = $access_token;

	header("location:".$_SESSION['matrix']['site_url']."/login/twitter");
}