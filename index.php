<?php

/**
Open source CAD system for RolePlaying Communities.
Copyright (C) 2017 Shane Gill

This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

This program comes with ABSOLUTELY NO WARRANTY; Use at your own risk.
**/

if(!file_exists(getcwd().'/oc-config.php') && is_writable(getcwd())){

   header('Location://'.$_SERVER['SERVER_NAME'].'/oc-install/start.php');
}
	require_once(__DIR__ . "/oc-config.php");
	require_once(__DIR__ . "/actions/register.php");
	require_once(__DIR__ . "/actions/publicFunctions.php");

	$testing = false; //If set to true, will default some data for you

	session_start();
	$_SESSION['root_path'] = getcwd();
	$registerError = "";
	$registerSuccess = "";
	$loginMessage = "";

	if ( (isset($_SESSION['logged_in'])) == "YES" )
	{
	  header ('Location: ./dashboard.php');
;      //echo $_SESSION['name']." is logged in!";
	}
	if (isset($_GET['loggedOut']))
	{
	  $loginMessage = '<div class="alert alert-success" style="text-align: center;" ><span>You\'ve successfully been logged out</span></div>';
   }
   if(isset($_SESSION['register_error']))
   {
	}
	if(isset($_SESSION['register_error']))
	{
	  $registerError = '<div class="alert alert-danger" style="text-align: center;"><span>'.$_SESSION['register_error'].'</span></div>';
		unset($_SESSION['register_error']);
	}
	if(isset($_SESSION['register_success']))
	{
	  $registerError = '<div class="alert alert-success" style="text-align: center;"><span>'.$_SESSION['register_success'].'</span></div>';
		unset($_SESSION['register_success']);
	}
	if(isset($_SESSION['loginMessageDanger']))
	{
	  $loginMessage = '<div class="alert alert-danger" style="text-align: center;"><span>'.$_SESSION['loginMessageDanger'].'</span></div>';
		unset($_SESSION['loginMessageDanger']);
	}
	if(isset($_POST["email"]) && (!empty($_POST["email"]))){
    $email = $_POST["email"];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error ="<p>Invalid email address please type a valid email address!</p>";
    }else{
        $sel_query = "SELECT * FROM ".DB_PREFIX."users WHERE email='".$email."'";
        $con = PDO::prepare("INSERT INTO ".DB_PREFIX."password_reset_temp (`email`, `key`, `expDate`)
VALUES ('?', '?', '?');");
        $results = mysqli_query($con, $sel_query);
        $row = mysqli_num_rows($results);
        if ($row==""){
            $error = "<p>No user is registered with this email address!</p>";
        }
    }
    if($error!=""){
        echo "<div class='error'>".$error."</div>
   <br /><a href='javascript:history.go(-1)'>Go Back</a>";
    }else{
        $expFormat = mktime(
            date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y")
        );
        $expDate = date("Y-m-d H:i:s",$expFormat);
        $key = md5(2418*2+$email);
        $addKey = substr(md5(uniqid(rand(),1)),3,10);
        $key = $key . $addKey;
// Insert Temp Table
        mysqli_query($con,
            "INSERT INTO ".DB_PREFIX."password_reset_temp (`email`, `key`, `expDate`)
VALUES ('".$email."', '".$key."', '".$expDate."');");

        $output='<p>Dear user,</p>';
        $output.='<p>Please click on the following link to reset your password.</p>';
        $output.='<p>-------------------------------------------------------------</p>';
        $output.='<p><a href="https://'.$_SERVER['SERVER_NAME'].'/reset-password.php?
key='.$key.'&email='.$email.'&action=reset" target="_blank">
https://'.$_SERVER['SERVER_NAME'].'/reset-password.php
?key='.$key.'&email='.$email.'&action=reset</a></p>';
        $output.='<p>-------------------------------------------------------------</p>';
        $output.='<p>Please be sure to copy the entire link into your browser.
The link will expire after 1 day for security reason.</p>';
        $output.='<p>If you did not request this forgotten password email, no action 
is needed, your password will not be reset. However, you may want to log into 
your account and change your security password as someone may have guessed it.</p>';
        $output.='<p>Thanks,</p>';
        $output.=  '<COMMUNITY_NAME>';
        $body = $output;
        $subject = "Password Recovery - <COMMUNITY_NAME>";

        $email_to = $email;
        $fromserver = "noreply@".BASE_URL."";
        require("PHPMailer/PHPMailerAutoload.php");
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host = "mail.".BASE_URL.""; // Enter your host here
        $mail->SMTPAuth = true;
        $mail->Username = "noreply@".BASE_URL.""; // Enter your email here
        $mail->Password = "password"; //Enter your password here
        $mail->Port = 25;
        $mail->IsHTML(true);
        $mail->From = "noreply@".BASE_URL."";
        $mail->FromName = "<COMMUNITY_NAME>";
        $mail->Sender = $fromserver; // indicates ReturnPath header
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($email_to);
        if(!$mail->Send()){
            echo "Mailer Error: " . $mail->ErrorInfo;
        }else{
            echo "<div class='error'>
<p>An email has been sent to you with instructions on how to reset your password.</p>
</div><br /><br /><br />";
        }
    }
}else{
?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php } ?>

<!DOCTYPE html>
<html lang="en">
   <?php include "./oc-includes/header.inc.php"; ?>
   <body class="login">
	  <div>
		 <a class="hiddenanchor" id="signup"></a>
		 <a class="hiddenanchor" id="signin"></a>
		 <a class="hiddenanchor" id="civreg"></a>
		 <div class="login_wrapper">
			<div class="animate form login_form civ_login">
			   <?php echo $loginMessage;?>
			   <section class="login_content">
				  <form role="form" action="<?php echo BASE_URL; ?>/actions/login.php" method="post">
					 <h1>Login</h1>
					 <div>
						<input class="form-control" placeholder="Email" name="email" type="text" value="<?php if($testing){echo "test@test.test";}?>" required>
					 </div>
					 <div>
						<input class="form-control" placeholder="Password" name="password" type="password" value="<?php if($testing){echo "password";}?>" required >
					 </div>
					 <div>
						<input name="login_btn" type="submit" class="btn btn-default submit" value="Login" />
						<a <form method="post" action="" name="reset"><br /><br />
                            <label><strong>Enter Your Email Address:</strong></label><br /><br />
                            <input type="email" name="email" placeholder="username@email.com" />
                            <br /><br />
                            <input type="submit" value="Reset Password"/>
                  </form> Lost your password?</a>
					 </div>
					 <?php if (CIV_REG === true) { ?>
			 <div class="clearfix"></div>
			 <div class="separator">
				<p class="change_link">New?
				   <a href="#signup" class="to_register"> Request Access </a>
				</p>
				<p class="change_link">Civilian Only?
				   <a href="#civreg" class="to_register"> Request Access as Civilian </a>
				</p>
				<div class="clearfix"></div>
				<br />
				<div>
				   <h1><i class="fas fa-users"></i> <?php echo COMMUNITY_NAME?> CAD System</h1>
				   <h2> OpenCAD Version <?php getOpenCADVersion();?> </h2>
				</div>
			 </div>
					 <?php } else { ?>
					 <div class="clearfix"></div>
					 <div class="separator">
						<p class="change_link">New?
						   <a href="#signup" class="to_register"> Request Access </a>
						</p>
						<p class="change_link">Civilian Only? Not Enabled
						</p>
						<div class="clearfix"></div>
						<br />
						<div>
						   <h1><i class="fas fa-users"></i> <?php echo COMMUNITY_NAME?> CAD System</h1>
						   <h2> OpenCAD Version <?php getOpenCADVersion();?> </h2>
						</div>
					 </div>
					 <?php } ?>
				  </form>
			   </section>
			</div>
			<div id="register" class="animate form registration_form">
			   <section class="login_content">
				  <?php echo $registerError, $registerSuccess;?>
				  <form action="<?php echo BASE_URL; ?>/actions/register.php" method="post">
					 <h1>Request Access</h1>
					 <div>
						<input class="form-control" placeholder="Name" name="uname" type="text" required>
					 </div>
					 <div>
						<input class="form-control" placeholder="Email" name="email" type="email" required>
					 </div>
					 <div>
						<input class="form-control" placeholder="Identifier (Code Number, Unit ID)" name="identifier" type="text" required>
					 </div>
					 <div class="form-group">
						<input class="form-control" placeholder="Password" name="password" type="password" value="<?php if($testing){echo "password";}?>" required>
					 </div>
					 <!-- ./ form-group -->
					 <div class="form-group">
						<input class="form-control" placeholder="Confirm Password" name="password1" type="password" required>
					 </div>
					 <!-- ./ form-group -->
					 <div class="form-group">
						<label>Division (Select all that apply)</label>
						<select class="form-control selectpicker" id="division" name="division[]" multiple="multiple" size="6" required>
							<?php getDataSetTable($dataSet = "departments", $column1 = "department_id", $column2 = "department_long_name", $leadTrim = 17, $followTrim = 11, $isRegistration = true, $isVehicle = false); ?>
						</select>
					 </div>
					 <div class="clearfix"></div>
					 <div>
						<input name="register" type="submit" class="btn btn-default btn-sm pull-right" value="Request Access" />
					 </div>
					 <div class="clearfix"></div>
					 <div class="separator">
						<p class="change_link">Already a member?
						   <a href="#signin" class="to_register"> Log in </a>
						</p>
						<div class="clearfix"></div>
						<br />
						<div>
						   <h1><i class="fas fa-users"></i> <?php echo COMMUNITY_NAME ?> CAD System</h1>
						   <h2> OpenCAD Version <?php getOpenCADVersion();?> </h2>
						</div>
					 </div>
				  </form>
			   </section>
			</div>
			 <?php if (CIV_REG === true) { ?>
			<div id="civ" class="animate form civilian_form">
			   <section class="login_content">
				  <?php echo $registerError, $registerSuccess;?>
				  <form action="<?php echo BASE_URL; ?>/actions/register.php" method="post">
					 <h1>Civilian Registration</h1>
					 <div>
						<input class="form-control" placeholder="Name" name="uname" type="text" value="<?php if($testing){echo "Test";}?>" required>
					 </div>
					 <div>
						<input class="form-control" placeholder="Email" name="email" type="email" value="<?php if($testing){echo "test@test.test";}?>" required>
					 </div>
					 <div>
						<input class="form-control" placeholder="Identifier (Code Number, Unit ID)" name="identifier" type="text" value="<?php if($testing){echo "1A-1";}?>" required>
					 </div>
					 <div class="form-group">
						<input class="form-control" placeholder="Password" name="password" type="password" value="<?php if($testing){echo "password";}?>" required>
					 </div>
					 <!-- ./ form-group -->
					 <div class="form-group">
						<input class="form-control" placeholder="Confirm Password" name="password1" type="password" value="<?php if($testing){echo "password";}?>" required>
					 </div>
					 <!-- ./ form-group -->
					 <div class="clearfix"></div>
					 <div>
						<input name="civreg" type="submit" class="btn btn-default btn-sm pull-right" value="Register" />
					 </div>
					 <div class="clearfix"></div>
					 <div class="separator">
						<p class="change_link">Already a member?
						   <a href="#signin" class="to_register"> Log in </a>
						</p>
						<div class="clearfix"></div>
						<br />
						<div>
						   <h1><i class="fas fa-users"></i> <?php echo COMMUNITY_NAME ?> CAD System</h1>
						   <h2> OpenCAD Version <?php getOpenCADVersion();?> </h2>
						</div>
					 </div>
				  </form>
			   </section>
			</div>
			<?php } else { ?>
			  <div id="civ" class="animate form civilian_form">
					<?php echo $registerError, $registerSuccess;?>
					   <p>Stop trying to backdoor into OpenCAD
						  This has been logged. </p>
					   <div class="clearfix"></div>
					   <div class="separator">
						  <p class="change_link">Already a member?
							 <a href="#signin" class="to_register"> Log in </a>
						  </p>
						  <div class="clearfix"></div>
						  <div>
							 <h1><i class="fas fa-users"></i> <?php echo COMMUNITY_NAME ?> CAD System</h1>
							 <h2> OpenCAD Version <?php getOpenCADVersion();?> </h2>
						  </div>
					   </div>
			  </div>
			   <?php } ?>
		 </div>
	  </div>
	  <?php include "./oc-includes/jquery-colsolidated.inc.php"; ?>
	  <script type="text/javascript">
		 $(document).ready(function() {
			// $('#division').multiselect();
		 });
	  </script>
   </body>
</html>
