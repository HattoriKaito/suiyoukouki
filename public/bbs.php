<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// 全体の投稿を取得
$sql = 'SELECT bbs_entries.*,'
    . '(SELECT name FROM users WHERE id = bbs_entries.user_id) user_name,'
    . '(SELECT icon_filename FROM users WHERE id = bbs_entries.user_id) user_icon_filename'
    . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
    . ' ORDER BY bbs_entries.created_at DESC';
$select_sth = $dbh->query($sql);

function bodyFilter ($body) {
    $body = htmlspecialchars($body);
    $body = nl2br($body);
    return preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry$1">&gt;&gt;$1</a>', $body);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>全体掲示板</title>
</head>
<body>
    <h1>全体掲示板</h1>
    
    <div style="margin-bottom: 1em;">
        <a href="/timeline.php"><strong>タイムラインへ移動する</strong></a>
    </div>

    <?php if(empty($_SESSION['login_user_id'])): ?>
        <p><a href="/login.php">ログイン</a>して自分のタイムラインを閲覧しましょう！</p>
    <?php else: ?>
        <p>※投稿は<a href="/timeline.php">タイムライン</a>から行ってください。</p>
    <?php endif; ?>

    <hr>

    <?php foreach($select_sth as $entry): ?>
        <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
            <dt>投稿者</dt>
            <dd>
                <a href="/profile.php?user_id=<?= $entry['user_id'] ?>">
                    <?php if(!empty($entry['user_icon_filename'])): ?>
                        <img src="/image/<?= $entry['user_icon_filename'] ?>"
                             style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover; vertical-align: middle;">
                    <?php endif; ?>
                    <?= htmlspecialchars($entry['user_name']) ?>
                </a>
            </dd>
            <dt>日時</dt>
            <dd><?= $entry['created_at'] ?></dd>
            <dt>内容</dt>
            <dd>
                <?= bodyFilter($entry['body']) ?>
                <?php if(!empty($entry['image_filename'])): ?>
                    <div><img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;"></div>
                <?php endif; ?>
            </dd>
        </dl>
    <?php endforeach ?>
</body>
</html>    
