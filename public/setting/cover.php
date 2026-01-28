<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
    move_uploaded_file($_FILES['cover']['tmp_name'], '/var/www/public/image/' . $filename);
    
    $stmt = $dbh->prepare("UPDATE users SET cover_filename = :cover WHERE id = :id");
    $stmt->execute([':cover' => $filename, ':id' => $_SESSION['login_user_id']]);
    
    header("Location: /profile.php");
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カバー画像変更</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>カバー画像の変更</h1>
    <a href="./index.php">戻る</a>
    <hr>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="cover" accept="image/*" required>
        <br><br>
        <button type="submit">変更する</button>
    </form>
</body>
</html>

