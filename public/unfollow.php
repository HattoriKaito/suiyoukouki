<?php
session_start();
if (empty($_SESSION['login_user_id']) || empty($_GET['target'])) {
    header("Location: /timeline.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$stmt = $dbh->prepare("DELETE FROM user_relationships WHERE follower_user_id = :me AND followee_user_id = :you");
$stmt->execute([':me' => $_SESSION['login_user_id'], ':you' => $_GET['target']]);
header("Location: " . $_SERVER['HTTP_REFERER']);

