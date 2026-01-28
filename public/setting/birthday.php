<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: /login.php");
    return;
}

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $select_sth->fetch();

// 生年月日の更新処理
if (isset($_POST['birthday'])) {
    // POSTされた日付を保存 (空文字の場合はNULLにする処理を入れても良いですが、今回はそのまま保存)
    $update_sth = $dbh->prepare("UPDATE users SET birthday = :birthday WHERE id = :id");
    $update_sth->execute([
        ':id' => $user['id'],
        ':birthday' => $_POST['birthday'],
    ]);
    
    header("Location: ./birthday.php?success=1");
    return;
}
?>
<a href="./index.php">設定一覧に戻る</a>

<h1>生年月日設定</h1>
<form method="POST">
    <label>
        生年月日:
        <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
    </label>
    <br><br>
    <button type="submit">設定する</button>
</form>

<?php if(!empty($_GET['success'])): ?>
    <p style="color: green;">生年月日を更新しました。</p>
<?php endif; ?>













