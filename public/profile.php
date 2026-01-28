<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

$user_id = $_GET['user_id'] ?? $_SESSION['login_user_id'];
$stmt = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) { echo "User not found"; return; }

$posts_stmt = $dbh->prepare("SELECT * FROM bbs_entries WHERE user_id = :id ORDER BY created_at DESC");
$posts_stmt->execute([':id' => $user_id]);
$posts = $posts_stmt->fetchAll();

$is_me = ($user_id == $_SESSION['login_user_id']);
$is_following = false;
if (!$is_me) {
    $rel_stmt = $dbh->prepare("SELECT * FROM user_relationships WHERE follower_user_id = :me AND followee_user_id = :you");
    $rel_stmt->execute([':me' => $_SESSION['login_user_id'], ':you' => $user_id]);
    $is_following = $rel_stmt->fetch();
}

$age = null;
if ($user['birthday']) {
    $age = (new DateTime())->diff(new DateTime($user['birthday']))->y;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>プロフィール</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <a href="/timeline.php">タイムライン</a>
    <hr>
    <?php if($user['cover_filename']): ?>
        <div style="height: 150px; background: url('/image/<?= $user['cover_filename'] ?>') center/cover;"></div>
    <?php endif; ?>
    
    <h2>
        <?php if($user['icon_filename']): ?>
            <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" style="width: 50px; height: 50px; border-radius: 50%;">
        <?php endif; ?>
        <?= htmlspecialchars($user['name']) ?>
    </h2>
    
    <?php if($age !== null): ?>
        <p><?= $age ?>歳</p>
    <?php endif; ?>
    
    <p><?= nl2br(htmlspecialchars($user['introduction'] ?? '')) ?></p>
    
    <?php if($is_me): ?>
        <a href="/setting/index.php">プロフィール編集</a>
    <?php elseif($is_following): ?>
        <a href="/unfollow.php?target=<?= $user['id'] ?>">フォロー解除</a>
    <?php else: ?>
        <a href="/follow.php?target=<?= $user['id'] ?>">フォローする</a>
    <?php endif; ?>

    <hr>
    <h3>投稿履歴</h3>
    <?php foreach($posts as $post): ?>
        <div style="border-bottom: 1px solid #eee; padding: 10px;">
            <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
            <?php if($post['image_filename']): ?>
                <img src="/image/<?= $post['image_filename'] ?>" style="max-height: 100px;">
            <?php endif; ?>
            <div style="color: #888; font-size: 0.8em;"><?= $post['created_at'] ?></div>
        </div>
    <?php endforeach; ?>
</body>
</html>



