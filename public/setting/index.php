<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>設定</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>設定メニュー</h1>
    <a href="/timeline.php">タイムラインに戻る</a> | <a href="/profile.php">自分のプロフィール</a>
    <hr>
    <ul>
        <li><a href="./icon.php">アイコン画像の変更</a></li>
        <li><a href="./cover.php">カバー画像の変更</a></li>
        <li><a href="./profile.php">プロフィール編集 (名前・自己紹介・生年月日)</a></li>
    </ul>
</body>
</html>
