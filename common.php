	<?php

	error_reporting(0);
	$re=file_get_contents('../user/Bin/Configuration.php');
	$res=explode('\'',$re);
	date_default_timezone_set('UTC'); 

	 $hostname=$res[1];

	 $username=$res[3];

	 $password=$res[5]; 

	 $dbname  =$res[7];



	$mysqli = new mysqli($hostname, $username, $password, $dbname);
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	if($mysqli) 
	{

		$siteautoparamdetails = file_get_contents($_SESSION['cdnuploadurl'].'/uploads/siteautoload.js');
		$siteloaddetails=json_decode($siteautoparamdetails,true);			
		foreach ($siteloaddetails as $keysite => $valuesite) {

				foreach ($valuesite as $keyparamsiteset => $valueparamsiteset) {

					if($keyparamsiteset=='site_service_worker'){

						$_SESSION['site']['site_service_worker']=$valueparamsiteset;
					}

					if($keyparamsiteset=='mass_payout'){

						$_SESSION['site']['mass_payout']=$valueparamsiteset;
					}
					if($keyparamsiteset=='https_enble'){

						if($valueparamsiteset=='1'){
							$_SESSION['site']['scheme']='https';
						}else{
							$_SESSION['site']['scheme']='http';
						}

					}						
					if($keyparamsiteset=='site_url' )
					{						
                    	
						$_SESSION['matrix']['site_url']=$valueparamsiteset;
						$asseturl=explode("/user",$valueparamsiteset);
						$_SESSION['asseturl']=$asseturl[0];	
					}					

						if($keyparamsiteset=='subdomain_enble'){

							$_SESSION['site']['subdomain_enble']=$valueparamsiteset;
							$subdomain_host_url=$_SERVER['HTTP_HOST'];
							$subdomain_host_url=explode('.', $subdomain_host_url);
							if(count($subdomain_host_url)>2){
								$subdomain_host_url=$subdomain_host_url[1].'.'.$subdomain_host_url[2];
							}
							$_SESSION['site']['subdomain_host']=$subdomain_host_url;

							if($valueparamsiteset=='1'){

									if(count($_SESSION['default']['customer_id']))
			                        {
			                        	$urlseh=explode('//',$_SESSION['asseturl']);
			                        	
			                        	$scheme=$_SESSION['site']['scheme'];
			                        	 
			                        	$namelash=$scheme.'://'.$_SESSION['default']['customer_name'].'.'.$urlseh[1].'/user';
			                        	$_SESSION['matrix']['site_url']=$namelash;
										$asseturl=explode("/user",$namelash);
										$_SESSION['asseturl']=$asseturl[0];                                             
			                         
			                        }
			                        else{ 
			                            
			                            if($_SESSION['wrd']['sponsor']!='' && count($_SESSION['default']['customer_id']))
			                            {
			                                $urlseh=explode('//',$_SESSION['asseturl']);
			                            	$scheme=$_SESSION['site']['scheme'];
			                            	$namelash=$scheme.'://'.$_SESSION['wrd']['sponsor'].'.'.$urlseh[1].'/user';
			                            	$_SESSION['matrix']['site_url']=$namelash;
			    							$asseturl=explode("/user",$namelash);
			    							$_SESSION['asseturl']=$asseturl[0]; 
			                            }
			                           
			                                               
									
									}

							}

						}
					
					if($keyparamsiteset=='site_currency')
					{
						$_SESSION['matrix']['site_currency']=$valueparamsiteset;
					}
					if($keyparamsiteset=='site_currency_code')
					{
						$_SESSION['matrix']['site_currency_code']=$valueparamsiteset;  
					}
					if($keyparamsiteset=='db_prefix')
					{
						define("STORE_PREFIX",$valueparamsiteset); 
					}
					if($keyparamsiteset=='sitetimezone')
					{
						date_default_timezone_set($valueparamsiteset);
					}
					if($keyparamsiteset=='theme_id')
					{
						if($valueparamsiteset>0)
						{
							$_SESSION['site']['theme']='/theme'.$valueparamsiteset;
						}
						if($valueparamsiteset=='0')
						{
							$_SESSION['site']['theme']='';
						}
					}					
					else
					{
						$_SESSION[$keyparamsiteset]=$valueparamsiteset;
					}
				}
				
		}


		$querystatus = "SELECT * FROM  ".PROMLM_PREFIX."sitesettings_status_table";
		$resultstatus = $mysqli->query($querystatus) or die($mysqli->error.__LINE__);
		$outputstatus=array();
		if($resultstatus->num_rows > 0) 
		{

				if($resultstatus->num_rows!='0')
				{					
					$rowstatus = $resultstatus->fetch_array(MYSQLI_ASSOC);

					foreach ($rowstatus as $key => $value) {

						$outputstatus[$key]=$value;
					}
				}
		}

		//After user login	
		if(!empty($_SESSION['default']['customer_id']))
		{
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			$user_id=$_SESSION['default']['customer_id'];
			//update last action time 
			if($_GET['do']=='logout'){

				$sqllastaction="UPDATE ".PROMLM_PREFIX."users_csrf_login SET last_action_time=NOW(),logout_time=NOW() WHERE members_id='".$user_id."' AND ipaddress='".$ipaddress."' ";
				$mysqli->query($sqllastaction);
			}
			else
			{


				
				$current_time=date('Y-m-d H:i:s');
				$sqllastaction = "SELECT * FROM ".PROMLM_PREFIX."users_csrf_login WHERE members_id='".$user_id."' AND ipaddress='".$ipaddress."'"; 
				$result_lastaction= $mysqli->query($sqllastaction);
		   		$row_lastaction= $result_lastaction->fetch_assoc();
				$last_action_time=$row_lastaction['last_action_time'];
			    $minutes_to_add = 15;
				$time = new DateTime($last_action_time);
				$time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
				$stamp = $time->format('Y-m-d H:i:s');
				$d1 = new DateTime($current_time);
				$d2 = new DateTime($stamp);
		
				if($d1 > $d2 && count($row_lastaction)>0)
				{
					$sqllastaction="DELETE FROM ".PROMLM_PREFIX."users_csrf_login  WHERE members_id='".$user_id."' AND ipaddress='".$ipaddress."'";
					$mysqli->query($sqllastaction);	
					header('Location:'.$_SESSION['matrix']['site_url'].'/logout');				
				}
				else{
					$sqllastaction="UPDATE ".PROMLM_PREFIX."users_csrf_login SET last_action_time=NOW() WHERE members_id='".$user_id."' AND ipaddress='".$ipaddress."'";
					$mysqli->query($sqllastaction);
				}


				
			}
			

			if($outputstatus['side_matrix_link']=='0' || $_SESSION['side_matrix_link']==''){
				$sqlmatrix="SELECT a.matrix_id,b.matrix_name
				FROM ".PROMLM_PREFIX."matrix_members_link_table AS a LEFT JOIN ".PROMLM_PREFIX."matrix_table 
				AS b ON b.matrix_id=a.matrix_id LEFT JOIN ".PROMLM_PREFIX."matrix_type_table AS c ON c.matrix_type_id=
				b.matrix_type_id  LEFT JOIN ".PROMLM_PREFIX."members_table AS d ON d.members_id=a.members_id WHERE 
				a.members_id='".$user_id."'  GROUP BY a.matrix_id";
				$resultmatrix = $mysqli->query($sqlmatrix) or die($mysqli->error.__LINE__);
				if($resultmatrix->num_rows > 0) 
				{

					if($resultmatrix->num_rows!='0')
					{
						while($rowmatrix = $resultmatrix->fetch_array(MYSQLI_ASSOC)) 
						{

							 $_SESSION['side_matrix_link'].='<li class="nav-item ">
	                                    <a class="nav-link " href="'.$_SESSION['matrix']['site_url'].'genealogy/view/'.$rowmatrix['matrix_id'].'/'.$_SESSION['default']['customer_id'].'/'.$_SESSION['default']['customer_id'].'" target="_blank">
	                                        <span class="title">'.$rowmatrix['matrix_name'].'</span>
	                                    </a>
	                                </li>';

						}
					}
				}

				$queryupstatus = "UPDATE ".PROMLM_PREFIX."sitesettings_status_table
				SET side_matrix_link='1' WHERE side_matrix_link='0";
				$mysqli->query($queryupstatus) ;	

			}

		if($outputstatus['replicatingedit']=='0' || $_SESSION['replicatingedit']==''){

				
			 	$query = "SELECT * FROM  ".PROMLM_PREFIX."promotional_sitesettings 
				WHERE sitesettings_name='Usereditable' AND sitesetings_default='1'";
				$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
				if($result->num_rows > 0) 
				{

					if($result->num_rows!='0')
					{
						
						while($row = $result->fetch_array(MYSQLI_ASSOC)) 
						{
							$sitesettings_value=$row['sitesettings_value'];
							$_SESSION['replicatingedit']=$sitesettings_value;
						}
					}
				}

				$queryupstatus = "UPDATE ".PROMLM_PREFIX."sitesettings_status_table
				SET replicatingedit='1' WHERE replicatingedit='0'";
				$mysqli->query($queryupstatus) ;
			}

 
			if($outputstatus['member_image']=='0' || $_SESSION['members_image']==''){
				 $query = "SELECT members_image FROM   ".PROMLM_PREFIX."members_table 
				WHERE members_id='".$user_id."' AND  members_image!=''";
				$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
				$_SESSION['members_image']=''.$_SESSION['cdnasseturl'].'/assets/pages/img/avatars/avatar.png';

				if($result->num_rows > 0) 
				{

					if($result->num_rows!='0')
					{
						
						while($row = $result->fetch_array(MYSQLI_ASSOC)) 
						{
        					$member_image=$row['members_image'];

							$_SESSION['members_image']=$_SESSION['cdnuploadurl'].'/'.$member_image;
						}
					}
				}

	
				$queryupstatus = "UPDATE ".PROMLM_PREFIX."sitesettings_status_table
				SET member_image='1' WHERE member_image='0'";
				$mysqli->query($queryupstatus) ;
			}



		}
		
		
        

		//For license addons

		$query = "SELECT * FROM  ".PROMLM_PREFIX."sitesettings_table 
		WHERE sitesettings_name='authentication_status'";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		if($result->num_rows > 0) 
		{

			if($result->num_rows!='0')
			{
				
				while($row = $result->fetch_array(MYSQLI_ASSOC)) 
				{	

					$authentication_status=$row['sitesettings_description'];
					if($authentication_status!='0' || $authentication_status!='1' || $_SESSION['addoncheckstatus']=='')
					{						
						$queryupstatus = "UPDATE ".PROMLM_PREFIX."sitesettings_table
						SET sitesettings_description='1' WHERE sitesettings_name='authentication_status'"; 
						$mysqli->query($queryupstatus) ;	
						$_SESSION['addoncheckstatus']='1';
					}
					if($authentication_status=='1' )
					{

							$querysite = "SELECT * FROM  ".PROMLM_PREFIX."sitesettings_table 
							WHERE sitesettings_name='site_url'";
							$resultsite = $mysqli->query($querysite);
							$rowsite = $resultsite->fetch_array(MYSQLI_ASSOC);
							$domainlicenurl=$rowsite['sitesettings_value'];
							$domainlicenurlpar=explode('/', $domainlicenurl);
							$domainlicenurlpar=$domainlicenurlpar[2];
							$namekeywwwcheck=explode('www.',$domainlicenurlpar);
							if(count($namekeywwwcheck)>1){
								$domainlicen=$namekeywwwcheck[1];
							}else{
								$domainlicen=$namekeywwwcheck[0];
							}

					        $ip=$_SERVER['SERVER_ADDR'];
					        $ch = curl_init();
					        $fields = array('domainname'=>$domainlicen,'ipaddress'=>$ip);
					        curl_setopt($ch, CURLOPT_URL,"https://licensing.sunsoftny.com/license/getaddonmenulist");
					        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
					        curl_setopt($ch, CURLOPT_VERBOSE, true);
					        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					      
					        $server = curl_exec ($ch);
					        curl_close($ch);
					        $checkkey=$server;
					    
					    	$_SESSION['useraddon']=array();
					    	$checkkey=json_decode($checkkey);
					    	foreach ($checkkey as $keyadd => $valueadd) {

					    		$_SESSION['useraddon'][$valueadd]='1';
					    		        
					    	}


					   	$queryupstatus = "UPDATE ".PROMLM_PREFIX."sitesettings_table
						SET sitesettings_description='0' WHERE sitesettings_name='authentication_status'";
						$mysqli->query($queryupstatus) ;	

					}
				}
			}
		}

	}


$mysqli->close();

?>

