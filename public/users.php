<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

$search_name = $_GET['name'] ?? null;
$year_from = $_GET['year_from'] ?? null;
$year_to = $_GET['year_to'] ?? null;

$sql = 'SELECT users.*, r.id AS relationship_id'
     . ' FROM users'
     . ' LEFT JOIN user_relationships AS r'
     . ' ON users.id = r.followee_user_id AND r.follower_user_id = :me';

$where = [];
$params = [':me' => $_SESSION['login_user_id']];

if (!empty($search_name)) {
    $where[] = 'users.name LIKE :name';
    $params[':name'] = '%' . $search_name . '%';
}
if (!empty($year_from)) {
    $where[] = 'users.birthday >= :from';
    $params[':from'] = $year_from . '-01-01';
}
if (!empty($year_to)) {
    $where[] = 'users.birthday <= :to';
    $params[':to'] = $year_to . '-12-31';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY users.id DESC';

$stmt = $dbh->prepare($sql);
$stmt->execute($params);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会員一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>会員一覧</h1>
    <a href="/timeline.php">タイムライン</a> | <a href="/setting/index.php">設定</a>
    <hr>
    
    <form method="GET">
        名前: <input type="text" name="name" value="<?= htmlspecialchars($search_name ?? '') ?>"><br>
        生まれ年: <input type="number" name="year_from" value="<?= htmlspecialchars($year_from ?? '') ?>"> ～ 
        <input type="number" name="year_to" value="<?= htmlspecialchars($year_to ?? '') ?>"><br>
        <button type="submit">検索</button>
    </form>
    <hr>

    <?php foreach($stmt as $user): ?>
        <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <?php if($user['icon_filename']): ?>
                        <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <?php endif; ?>
                    <a href="/profile.php?user_id=<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['name']) ?>
                    </a>
                </div>
                <div>
                    <?php if ($user['id'] === $_SESSION['login_user_id']): ?>
                        <span>あなた</span>
                    <?php elseif (!empty($user['relationship_id'])): ?>
                        <span style="color: green;">フォロー中</span>
                        <a href="./unfollow.php?target=<?= $user['id'] ?>">[解除]</a>
                    <?php else: ?>
                        <a href="./follow.php?target=<?= $user['id'] ?>">フォローする</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>

