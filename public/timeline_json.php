<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
session_start();

if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    header("Content-Type: application/json");
    print(json_encode(['entries' => []]));
    return;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
    . ' FROM bbs_entries'
    . ' INNER JOIN users ON bbs_entries.user_id = users.id'
    . ' LEFT OUTER JOIN user_relationships ON bbs_entries.user_id = user_relationships.followee_user_id'
    . ' WHERE (user_relationships.follower_user_id = :me OR bbs_entries.user_id = :me)'
    . ' ORDER BY bbs_entries.created_at DESC'
    . ' LIMIT :limit OFFSET :offset';

$stmt = $dbh->prepare($sql);
$stmt->bindValue(':me', $_SESSION['login_user_id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

function bodyFilter($body) {
    return nl2br(htmlspecialchars($body));
}

$entries = [];
foreach ($stmt as $row) {
    $entries[] = [
        'id' => $row['id'],
        'user_name' => $row['user_name'],
        'user_icon_filename' => $row['user_icon_filename'],
        'user_profile_url' => '/profile.php?user_id=' . $row['user_id'],
        'body' => bodyFilter($row['body']),
        'image_filename' => $row['image_filename'],
        'created_at' => $row['created_at'],
    ];
}

header("Content-Type: application/json");
echo json_encode(['entries' => $entries]);
















