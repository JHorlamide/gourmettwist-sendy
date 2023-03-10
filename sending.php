<?php 
	mysqli_report(MYSQLI_REPORT_OFF);
	include('includes/config.php');
	include('includes/helpers/locale.php');
	
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    global $dbPort;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 110px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later.')."</p></div></body></html>");
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail($errorMsg) { //Database connection fails
	//--------------------------------------------------------------//
	    echo $errorMsg;
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php 
	$campaign_id = is_numeric($_GET['c']) ? $_GET['c'] : exit;
	$email_list = mysqli_real_escape_string($mysqli, $_GET['e']);
	$email_list_exclude = mysqli_real_escape_string($mysqli, $_GET['ex']);
	$email_lists_segs = mysqli_real_escape_string($mysqli, $_GET['e_segs']);
	$email_lists_segs_excl = mysqli_real_escape_string($mysqli, $_GET['ex_segs']);
	$app = is_numeric($_GET['i']) ? $_GET['i'] : exit;
	$schedule = $_GET['s'];
	if(isset($_GET['cr'])) $cron = is_numeric($_GET['cr']) ? $_GET['cr'] : exit;
	$total_recipients = is_numeric($_GET['recipients']) ? $_GET['recipients'] : exit;
	
	//Set language
	$q = 'SELECT login.language FROM campaigns, login WHERE campaigns.id = '.$campaign_id.' AND login.app = campaigns.app';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
	set_locale($language);
	
	//check if sent
	$q = 'SELECT sent, quota_deducted FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sent = stripslashes($row['sent']);
			$current_quota_deducted = $row['quota_deducted']=='' ? 0 : $row['quota_deducted'];
	    }  
	}
	
	//Check if monthly quota needs to be updated
	$q = 'SELECT allocated_quota, current_quota FROM apps WHERE id = '.$app;
	$r = mysqli_query($mysqli, $q);
	if($r) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$allocated_quota = $row['allocated_quota'];
			$current_quota = $row['current_quota'];
		}
	}
	//Update quota if a monthly limit was set
	if($allocated_quota!=-1)
	{
		if($schedule!='true') $current_quota_deducted = 0;
		
		$updated_quota = ($current_quota + $total_recipients) - $current_quota_deducted;
		
		//if so, update quota
		$q = 'UPDATE apps SET current_quota = '.$updated_quota.' WHERE id = '.$app;
		mysqli_query($mysqli, $q);
	}

//if scheduled
if($schedule=='true'):

	//get POST variables
	$the_date = mysqli_real_escape_string($mysqli, $_GET['date']);
	$timezone = mysqli_real_escape_string($mysqli, $_GET['timezone']);
	
	$q = 'UPDATE campaigns SET send_date = "'.$the_date.'", lists = "'.$email_list.'", lists_excl = "'.$email_list_exclude.'", segs = "'.$email_lists_segs.'", segs_excl = "'.$email_lists_segs_excl.'", timezone = "'.$timezone.'", quota_deducted = '.$total_recipients.' WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if($r):
	?>
	<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo APP_PATH;?>/img/favicon.png">
		<title><?php echo _('Your campaign has been scheduled');?></title>
	</head>
	<style type="text/css">
		body{
			background: #f7f9fc;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #ffffff;
			-webkit-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			-moz-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			
			width: 280px;
			height: 250px;
			
			margin: -190px 0 0 -140px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			padding: 10px 20px;
		}
		p{
			text-align: center;
			font-size: 12px;
			line-height: 16px;
		}
		h2{
			font-weight: normal;
			text-align: center;
		}
		a{
			color: #000;
		}
		a:hover{
			text-decoration: none;
		}
		#sending{
			margin-left: 95px;
		}
		#top-pattern{
			margin-top: -8px;
			height: 8px;
			background: url("<?php echo APP_PATH; ?>/img/top-pattern2.gif") repeat-x 0 0;
			background-size: auto 8px;
		}
	</style>
	<body>
		<div id="top-pattern"></div>
		<div id="wrapper">
			<h2><?php echo _('Your campaign has been scheduled');?>!</h2>
			<img id="sending" src="<?php echo APP_PATH;?>/img/scheduled.jpg?2" height="92" />
			<p><?php echo _('You will be notified by email once your campaign has been sent.');?></p>
		</div>
	</body>
	</html>
	<?php endif;?>

<?php else:?>

	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta name="robots" content="noindex, nofollow">
			<script type="text/javascript" src="<?php echo APP_PATH;?>/js/jquery-3.5.1.min.js"></script>
			<link rel="Shortcut Icon" type="image/ico" href="<?php echo APP_PATH;?>/img/favicon.png">
			<title><?php echo _('Now sending');?></title>
			<?php if($sent==''):?>
			<script type="text/javascript">
				$(document).ready(function() {
					
					<?php if($email_list!=''):?>
						list = "<?php echo $email_list;?>";
					<?php else:?>
						list = "0";
					<?php endif;?>
					
					<?php if($email_lists_segs!=''):?>
						list_segs = "<?php echo $email_lists_segs;?>";
					<?php else:?>
						list_segs = "0";
					<?php endif;?>
					
					<?php if($email_list_exclude!=''):?>
						list_excl = "<?php echo $email_list_exclude;?>";
					<?php else:?>	
						list_excl = "0";
					<?php endif;?>
					
					<?php if($email_lists_segs_excl!=''):?>
						list_excl_segs = "<?php echo $email_lists_segs_excl;?>";
					<?php else:?>	
						list_excl_segs = "0";
					<?php endif;?>
					
					$.post("<?php echo APP_PATH;?>/includes/create/send-now.php", { campaign_id: <?php echo $campaign_id;?>, email_list: list, email_list_exclude: list_excl, email_lists_segs: list_segs, email_lists_segs_excl: list_excl_segs, app: <?php echo $app;?>, cron: <?php echo $cron;?>, total_recipients: <?php echo $total_recipients;?> },
					  function(data) {
					      if(data){}
					  }
					);
				});
			</script>
			<?php endif;?>
		</head>
		<style type="text/css">
		body{
			background: #f7f9fc;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #ffffff;
			-webkit-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			-moz-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			
			width: 350px;
			height: 250px;
			
			margin: -190px 0 0 -185px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			padding: 10px 20px;
		}
		p{
			text-align: center;
			font-size: 12px;
			line-height: 16px;
		}
		h2{
			font-weight: normal;
			text-align: center;
		}
		a{
			color: #000;
			text-decoration: none;
		}
		a:hover{
			text-decoration: underline;
		}
		#sending{
			margin-left: 130px;
		}
		#top-pattern{
			margin-top: -8px;
			height: 8px;
			background: url("<?php echo APP_PATH; ?>/img/top-pattern2.gif") repeat-x 0 0;
			background-size: auto 8px;
		}
		</style>
		<body>
			<div id="top-pattern"></div>
			<div id="wrapper">
				<h2><?php echo _('Your campaign is on the way!');?></h2>
				<img id="sending" src="<?php echo APP_PATH;?>/img/email-notifications/email-sending.gif" height="150" />
				<p><?php echo _('You can close this window and your campaign will continue to send. You will be notified by email once your campaign has completed sending.');?></p>
			</div>
		</body>
	</html>
	
<?php endif;?>