<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// ログインしていない場合はログイン画面へ
if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: /login.php");
    return;
}

// --- 投稿処理 (変更なし) ---
if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {
    $image_filename = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
        $filepath = '/var/www/public/image/' . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }
    $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename)");
    $insert_sth->execute([
        ':user_id' => $_SESSION['login_user_id'],
        ':body' => $_POST['body'],
        ':image_filename' => $image_filename,
    ]);
    header("HTTP/1.1 303 See Other");
    header("Location: ./timeline_subquery.php"); // リダイレクト先も自分自身に変更
    return;
}

// --- タイムライン表示用データの取得 (★ここがサブクエリ版) ---

// WHERE句の中で、「フォローしている人のID」を取得するサブクエリを実行しています。
// さらに OR を使って「自分自身のID」も含めています。
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
     . ' FROM bbs_entries'
     . ' INNER JOIN users ON bbs_entries.user_id = users.id'
     . ' WHERE bbs_entries.user_id IN ('
     . '    SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :me'
     . ' )'
     . ' OR bbs_entries.user_id = :me'
     . ' ORDER BY bbs_entries.created_at DESC';

$select_sth = $dbh->prepare($sql);
$select_sth->execute([
    ':me' => $_SESSION['login_user_id'],
]);

// 本文フィルタ関数
function bodyFilter (string $body): string {
    $body = htmlspecialchars($body);
    $body = nl2br($body);
    return preg_replace('/&gt;&gt;(\d+)/', '<a href="#entry$1">&gt;&gt;$1</a>', $body);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タイムライン(サブクエリ)</title>
</head>
<body>
    <h1>タイムライン (サブクエリ版)</h1>
    <a href="/login_finish.php">マイページに戻る</a> | <a href="/bbs.php">全体の掲示板を見る</a>
    <hr>

    <form method="POST" enctype="multipart/form-data">
        <textarea name="body" required rows="3" cols="50" placeholder="今どうしてる？"></textarea>
        <div style="margin: 1em 0;">
            <input type="file" accept="image/*" name="image">
        </div>
        <button type="submit">送信</button>
    </form>

    <hr>

    <?php foreach($select_sth as $entry): ?>
        <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
            <dt>投稿者</dt>
            <dd>
                <a href="/profile.php?user_id=<?= $entry['user_id'] ?>" style="text-decoration: none; color: #333; display: flex; align-items: center;">
                    <?php if(!empty($entry['user_icon_filename'])): ?>
                        <img src="/image/<?= $entry['user_icon_filename'] ?>" 
                             style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                    <?php else: ?>
                        <div style="height: 2em; width: 2em; border-radius: 50%; background: #eee; margin-right: 10px;"></div>
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
                    <br>
                    <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 200px; margin-top: 10px;">
                <?php endif; ?>
            </dd>
        </dl>
    <?php endforeach ?>

</body>
</html>
