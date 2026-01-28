<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

$sql = "SELECT users.* FROM user_relationships 
        INNER JOIN users ON user_relationships.follower_user_id = users.id
        WHERE user_relationships.followee_user_id = :me";
$stmt = $dbh->prepare($sql);
$stmt->execute([':me' => $_SESSION['login_user_id']]);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>フォロワー</title>
</head>
<body>
    <h1>フォロワー（あなたをフォローしている人）</h1>
    <a href="/profile.php">プロフィールに戻る</a>
    <hr>
    <?php foreach($stmt as $user): ?>
        <div>
            <a href="/profile.php?user_id=<?= $user['id'] ?>">
                <?= htmlspecialchars($user['name']) ?>
            </a>
        </div>
        <hr>
    <?php endforeach; ?>
</body>
</html>


