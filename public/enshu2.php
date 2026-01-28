
<?php
// --- セッション管理 ---

// セッションIDの取得
date_default_timezone_set('Asia/Tokyo');
$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$session_cookie_name])) {
    setcookie($session_cookie_name, $session_id, time() + 86400, "/"); // Cookieは1日間有効
}


// --- セッションデータ処理 ---

$session_values = [];
$error = '';
$previous_access_time = '今回が初めてのアクセスです。';

try {
    // Redisに接続
    $redis = new Redis();
    $redis->connect('redis', 6379);

    // このセッション専用のキーを定義
    $redis_session_key = "session-" . $session_id;

    // 1. Redisから既存のセッションデータを読み込む
    if ($redis->exists($redis_session_key)) {
        $session_values = json_decode($redis->get($redis_session_key), true);
        // 前回アクセス日時を取得
        if (isset($session_values['last_access_time'])) {
            $previous_access_time = $session_values['last_access_time'];
        }
    }

    // 2. アクセスカウンタを更新 (なければ1に初期化)
    $current_count = isset($session_values['count']) ? $session_values['count'] + 1 : 1;
    $session_values['count'] = $current_count;
    
    // 3. 今回のアクセス日時をセッションに保存
    $session_values['last_access_time'] = date('Y-m-d H:i:s');
    
    // 4. 更新したセッション情報をRedisに書き戻す
    $redis->set($redis_session_key, json_encode($session_values));

} catch (Exception $e) {
    $error = "エラー: Redisに接続できませんでした。";
}


// --- HTML表示 ---
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>演習2: アクセス日時記録</title>
</head>
<body>
    <h1>アクセスカウンタ</h1>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php else: ?>
        <p>このセッションでの <strong><?php echo htmlspecialchars($current_count); ?></strong> 回目のアクセスです！</p>
        <p>前回のアクセス日時: <?php echo htmlspecialchars($previous_access_time); ?></p>
    <?php endif; ?>
    
</body>
</html>















