<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    move_uploaded_file($_FILES['icon']['tmp_name'], '/var/www/public/image/' . $filename);
    
    $stmt = $dbh->prepare("UPDATE users SET icon_filename = :icon WHERE id = :id");
    $stmt->execute([':icon' => $filename, ':id' => $_SESSION['login_user_id']]);
    
    header("Location: /profile.php"); // プロフィールへ戻る
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アイコン変更</title>
</head>
<body>
    <h1>アイコン画像の変更</h1>
    <a href="./index.php">戻る</a>
    <hr>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="icon" accept="image/*" required>
        <button type="submit">変更する</button>
    </form>
</body>
</html>



