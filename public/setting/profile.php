<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// 現在の情報を取得
$stmt = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['login_user_id']]);
$user = $stmt->fetch();

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $introduction = $_POST['introduction'];
    $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;

    $update_stmt = $dbh->prepare("UPDATE users SET name = :name, introduction = :intro, birthday = :birth WHERE id = :id");
    $update_stmt->execute([
        ':name' => $name,
        ':intro' => $introduction,
        ':birth' => $birthday,
        ':id' => $_SESSION['login_user_id']
    ]);

    header("Location: /profile.php");
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プロフィール編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>プロフィール編集</h1>
    <a href="./index.php">戻る</a>
    <hr>
    <form method="POST">
        <label>
            名前:<br>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </label>
        <br><br>
        <label>
            自己紹介:<br>
            <textarea name="introduction" rows="5" cols="40"><?= htmlspecialchars($user['introduction'] ?? '') ?></textarea>
        </label>
        <br><br>
        <label>
            生年月日:<br>
            <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
        </label>
        <br><br>
        <button type="submit">保存する</button>
    </form>
</body>
</html>
