<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: ./login.php");
    return;
}
?>
<h1>ログイン完了</h1>
<p><a href="/timeline.php">タイムラインへ</a></p>




