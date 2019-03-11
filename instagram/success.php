<?php
session_name("some_session_name");
session_set_cookie_params(0, '/', '.'.$_SERVER['HTTP_HOST']);
session_start();

$code = $_GET['code'];

 header("location:".$_SESSION['matrix']['site_url']."/login/instagramlogin/".$code);


?>