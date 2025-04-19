<?php
session_start();
if(!isset($_SESSION["rightCaptchaNumber"])){
$_SESSION["generateCaptchaNumber1"] = rand(0,10);
$_SESSION["generateCaptchaNumber2"] = rand(0,10);
$_SESSION["rightCaptchaNumber"] = $_SESSION["generateCaptchaNumber1"]+$_SESSION["generateCaptchaNumber2"];
}
?>