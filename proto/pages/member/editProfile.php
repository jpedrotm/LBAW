<?php
if (!isset($_GET["id"]) || !preg_match('/^\d+$/', $_GET["id"])) {
    header("Location: ../common/error.php");
}

include_once('../../config/init.php');
include_once("../common/header.php");
include_once("../../database/account.php");
include_once("../../database/email.php");
include_once("../../database/topics.php");
$userid = $_GET["id"];
$user = getAccountByUserId($userid)[0];

if (!isset($user)) {
    header("Location: ../common/error.php");
}

$emails = getUserEmailList($userid);
$links = json_decode(getUserLinks($userid)[0]['links']);

$smarty->assign('user', $user);
$smarty->assign('emails', $emails);
$smarty->assign('links', $links);
$smarty->display('member/editProfile.tpl');
?>