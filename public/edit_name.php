<?php

// 1. PHP標準のセッション機能を開始
session_start();

// 2. ログインしているか$_SESSIONで確認
if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php");
    return;
}

// 3. 独自実装セッションのコードはすべて削除

$message = ''; // ユーザーへのメッセージを格納する変数

// フォームから新しい名前がPOSTで送信された場合のみ、更新処理を実行
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_name'])) {
    $new_name = $_POST['new_name'];

    // データベースに接続
    $dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

    // UPDATE文を実行して名前を更新
    $update_sth = $dbh->prepare("UPDATE users SET name = :new_name WHERE id = :id");
    $update_sth->execute([
        ':new_name' => $new_name,
        ':id' => $_SESSION['login_user_id'] // ★ $_SESSIONからIDを取得
    ]);

    // ★ セッションに保存されている名前も更新
    $_SESSION['user_name'] = $new_name; 
    
    $message = '<p style="color: green;">名前を更新しました！</p>';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>名前の変更</title>
</head>
<body>
    <h1>名前の変更</h1>
    
    <?php echo $message; // 処理結果のメッセージを表示 ?>
    
    <p>現在の名前: <?= htmlspecialchars($_SESSION['user_name']) ?></p>

    <form method="POST">
        <label>
            新しい名前:
            <input type="text" name="new_name" required>
        </label>
        <button type="submit">変更</button>
    </form>
    
    <hr>
    
    <a href="./login_finish.php">戻る</a>
</body>
</html>








