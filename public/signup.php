<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $check_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email");
    $check_sth->execute([':email' => $_POST['email']]);
    if ($check_sth->fetch()) {
        header("HTTP/1.1 302 Found");
        header("Location: ./signup.php?duplicate_email=1");
        return;
    }

    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :pw)");
    $insert_sth->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':pw' => $hash,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ./signup_finish.php");
    return;
}
?>
<h1>会員登録</h1>
<?php if(isset($_GET['duplicate_email'])): ?>
    <p style="color:red;">既にこのメールアドレスは登録されています。</p>
<?php endif; ?>
<form method="POST">
    名前: <input type="text" name="name" required><br>
    メール: <input type="email" name="email" required><br>
    パスワード: <input type="password" name="password" required><br>
    <button type="submit">登録</button>
</form>
<a href="/login.php">ログインはこちら</a>
