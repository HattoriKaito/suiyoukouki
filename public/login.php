<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

$error_message = ""; // エラーメッセージ用変数を初期化

if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email");
    $select_sth->execute([':email' => $_POST['email']]);
    $user = $select_sth->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        // ログイン成功
        session_regenerate_id(true);
        $_SESSION['login_user_id'] = $user['id'];
        
        header("HTTP/1.1 302 Found");
        header("Location: ./login_finish.php");
        return;
    } else {
        // ログイン失敗（ユーザーがいない、またはパスワードが違う）
        $error_message = "メールアドレスまたはパスワードが間違っています。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>
<body>
    <h1>ログイン</h1>

    <?php if(!empty($error_message)): ?>
        <p style="color: red; font-weight: bold;">
            <?= htmlspecialchars($error_message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        メール: <input type="email" name="email" required><br>
        パスワード: <input type="password" name="password" required><br>
        <button type="submit">ログイン</button>
    </form>
    
    <div style="margin-top: 20px;">
        <a href="/signup.php">会員登録はこちら</a>
    </div>
</body>
</html>


