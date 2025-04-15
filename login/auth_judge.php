<?php
session_start();
if (!isset($_SESSION["judge_logged_in"])) {
    header("Location: ../login/judge_login.php");
    exit;
}
?>
