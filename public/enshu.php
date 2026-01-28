<?php
// ==========================================================
// 1. セッション管理のコード
// ==========================================================

// セッションIDの取得(なければ新規で作成&設定)
$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$session_cookie_name])) {
    setcookie($session_cookie_name, $session_id, time() + 86400, "/");
}

// Redisに接続（この接続をセッションと掲示板で共有します）
$redis = new Redis();
$redis->connect('redis', 6379);

// redisにセッション変数を保存しておくキー
$redis_session_key = "session-" . $session_id;

// セッションデータを読み込む
$session_values = $redis->exists($redis_session_key)
    ? json_decode($redis->get($redis_session_key), true)
    : [];

// ★★★ ここを変更 ★★★
// 例として、username は hattoriとセッションに保存
$session_values["username"] = "hattori";
$redis->set($redis_session_key, json_encode($session_values));


// ==========================================================
// 2. 掲示板のロジック
// ==========================================================

$posts = []; // 投稿を格納する配列
$error = '';

try {
    // [読み込み] RedisからJSON形式の投稿データを取得
    $jsonPosts = $redis->get('posts_list_with_user'); // キー名を変更
    if ($jsonPosts) {
        $posts = json_decode($jsonPosts, true);
    }

    // [書き込み] フォームがPOST送信された場合の処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['message'])) {
            $newMessage = $_POST['message'];
            
            // --- 変更点：セッションからユーザー名を取得 ---
            $username = $session_values['username'] ?? '名無しさん';
            
            // --- 変更点：投稿内容を「ユーザー名」と「メッセージ」の連想配列にする ---
            $newPost = [
                'username' => $username,
                'message' => $newMessage,
                'time' => date('Y-m-d H:i:s')
            ];
            
            // 配列の先頭に新しい投稿を追加
            array_unshift($posts, $newPost);

            $jsonNewPosts = json_encode($posts, JSON_UNESCAPED_UNICODE);

            // Redisに保存
            $redis->set('posts_list_with_user', $jsonNewPosts); // キー名を変更

            // PRGパターンでリダイレクト
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            exit();
        }
    }

} catch (Exception $e) {
    $error = 'エラー: Redisに接続できませんでした。' . $e->getMessage();
}

// --- HTML表示部分 ---
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>セッション機能付き掲示板</title>
</head>
<body>

    <h1>掲示板</h1>
    <p>ようこそ、<strong><?php echo htmlspecialchars($session_values['username'] ?? 'ゲスト'); ?></strong>さん</p>

    <form action="" method="POST">
        <textarea name="message" rows="4" cols="50" required></textarea>
        <br>
        <button type="submit">更新</button>
    </form>

    <hr>

    <h2>現在の内容</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php else: ?>
        <?php if (empty($posts)): ?>
            <p>まだ投稿はありません。</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div>
                    <p>
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        (<?php echo htmlspecialchars($post['time']); ?>)
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($post['message'])); ?></p>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>







