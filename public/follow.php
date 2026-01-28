<?php
session_start();
if (empty($_SESSION['login_user_id']) || empty($_GET['target'])) {
    header("Location: /timeline.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$stmt = $dbh->prepare("INSERT INTO user_relationships (follower_user_id, followee_user_id) VALUES (:me, :you)");
$stmt->execute([':me' => $_SESSION['login_user_id'], ':you' => $_GET['target']]);
header("Location: " . $_SERVER['HTTP_REFERER']);


