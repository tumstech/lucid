<?php
session_start();


if ($_POST['user'])
{
if($_POST['pass'])
{
         // The submitted data is there, so process it
         login_check($_POST['user'], $_POST['pass']);

} else {
     // The form wasn't submitted, so go back
     login_go_back();
}
} else {
     // The form wasn't submitted, so go back
     login_go_back();
}

function login_check($user, $pass)
{

require ("../backend/config.php");

// Connecting, selecting database
$link = mysql_connect($db_host, $db_username, $db_password)
   or die('Could not connect: ' . mysql_error());
mysql_select_db($db_name) or die('Could not select database');

// Performing SQL query
$query = "SELECT password FROM ${db_prefix}users WHERE username='${user}'";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

//do compare
$line = mysql_fetch_array($result, MYSQL_ASSOC);

if($line)
{

$pass = crypt($pass, $conf_secretword);

foreach ($line as $col_value)
{

if($col_value == $pass)
{
// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
login_ok($user);
}
else
{
login_go_back("badlogin");
}
}
}
else
{
// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
login_go_back();
}
}

function login_go_back()
{
header("Location: ../backend/desktop_login.php?opmessage=Incorrect+Username+or+Password");
}

function login_ok($user)
{
require("../backend/config.php");

$link = mysql_connect($db_host, $db_username, $db_password)
   or die('Could not connect: ' . mysql_error());
mysql_select_db($db_name) or die('Could not select database');

$query = "UPDATE `${db_prefix}users` SET `logged` = '1' WHERE username ='${user}'";

$result = mysql_query($query) or die('Query failed: ' . mysql_error());
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
		$_SESSION['userid'] = $row['ID'];
		$_SESSION['username'] = $row['username'];
    }
mysql_close($link);
$_SESSION['userloggedin'] = TRUE;
global $conf_user;
$conf_user = $user;
require("desktop.php");
}

?>