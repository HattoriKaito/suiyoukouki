<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: /login.php");
    return;
}
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$user_stmt = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$user_stmt->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_stmt->fetch();

if (isset($_POST['body'])) {
    $image_filename = null;
    if (!empty($_POST['image_base64'])) {
        $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);
        $image_binary = base64_decode($base64);
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
        file_put_contents('/var/www/public/image/' . $image_filename, $image_binary);
    }
    $stmt = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:uid, :body, :img)");
    $stmt->execute([
        ':uid' => $_SESSION['login_user_id'],
        ':body' => $_POST['body'],
        ':img' => $image_filename
    ]);
    header("Location: ./timeline.php");
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タイムライン</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; padding: 10px; max-width: 800px; margin: 0 auto; }
        textarea { width: 100%; box-sizing: border-box; }
        .entry { border-bottom: 1px solid #ccc; padding: 15px 0; }
        img { max-width: 100%; }
        .pagination { margin: 20px 0; text-align: center; }
        .pagination a { padding: 10px 20px; border: 1px solid #ccc; text-decoration: none; margin: 0 5px; background: #f9f9f9; color: #333; }
        .pagination a:hover { background: #eee; }
        @media (max-width: 600px) { body { padding: 5px; } }
    </style>
</head>
<body>
    <h1>タイムライン</h1>
    <div style="margin-bottom: 15px;">
        現在: <?= htmlspecialchars($user['name']) ?><br>
        <a href="/users.php">会員一覧</a> | <a href="/setting/index.php">設定</a> | <a href="/logout.php">ログアウト</a>
    </div>

    <form method="POST">
        <textarea name="body" required rows="3" placeholder="投稿内容"></textarea>
        <div style="margin: 10px 0;">
            画像添付: <input type="file" id="imageInput" accept="image/*">
        </div>
        <input type="hidden" name="image_base64" id="imageBase64Input">
        <canvas id="imageCanvas" style="display: none;"></canvas>
        <button type="submit">投稿する</button>
    </form>
    <hr>

    <div id="entriesRenderArea"></div>
    
    <div id="paginationArea" class="pagination"></div>

    <div id="entryTemplate" class="entry" style="display: none;">
        <div>
            <a href="" data-role="userLink" style="text-decoration: none; color: #333; font-weight: bold;">
                <img data-role="userIcon" style="width: 30px; height: 30px; border-radius: 50%; vertical-align: middle; display: none;">
                <span data-role="userName"></span>
            </a>
            <span data-role="createdAt" style="color: #888; font-size: 0.8em; margin-left: 10px;"></span>
        </div>
        <div data-role="body" style="margin-top: 5px;"></div>
        <div data-role="imageArea" style="margin-top: 10px; display: none;">
            <img data-role="image" style="max-height: 200px;">
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const renderArea = document.getElementById('entriesRenderArea');
    const template = document.getElementById('entryTemplate');
    const paginationArea = document.getElementById('paginationArea');
    
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(urlParams.get('page')) || 1;

    const loadEntries = () => {
        const url = '/timeline_json.php?page=' + page;

        const req = new XMLHttpRequest();
        req.open('GET', url, true);
        req.responseType = 'json';
        req.onload = (e) => {
            const data = e.target.response;

            if (!data || !data.entries) return;

            data.entries.forEach(entry => {
                const el = template.cloneNode(true);
                el.style.display = 'block';
                el.removeAttribute('id');

                el.querySelector('[data-role="userName"]').innerText = entry.user_name;
                el.querySelector('[data-role="userLink"]').href = entry.user_profile_url;
                el.querySelector('[data-role="createdAt"]').innerText = entry.created_at;
                el.querySelector('[data-role="body"]').innerHTML = entry.body;

                if (entry.user_icon_filename) {
                    const icon = el.querySelector('[data-role="userIcon"]');
                    icon.src = '/image/' + entry.user_icon_filename;
                    icon.style.display = 'inline-block';
                }

                if (entry.image_filename) {
                    const imgArea = el.querySelector('[data-role="imageArea"]');
                    const img = el.querySelector('[data-role="image"]');
                    img.src = '/image/' + entry.image_filename;
                    imgArea.style.display = 'block';
                }

                renderArea.appendChild(el);
            });

            renderPagination(data.entries.length);
        };
        req.send();
    };

    const renderPagination = (count) => {
        if (page > 1) {
            const prevLink = document.createElement('a');
            prevLink.href = '?page=' + (page - 1);
            prevLink.innerText = '<< 前のページ';
            paginationArea.appendChild(prevLink);
        }

        if (count >= 10) {
            const nextLink = document.createElement('a');
            nextLink.href = '?page=' + (page + 1);
            nextLink.innerText = '次のページ >>';
            paginationArea.appendChild(nextLink);
        }
    };

    loadEntries();

    const fileInput = document.getElementById('imageInput');
    const hiddenInput = document.getElementById('imageBase64Input');
    const canvas = document.getElementById('imageCanvas');

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const MAX_SIZE = 800;
                let w = img.width;
                let h = img.height;
                
                if (w > h) {
                    if (w > MAX_SIZE) { h *= MAX_SIZE / w; w = MAX_SIZE; }
                } else {
                    if (h > MAX_SIZE) { w *= MAX_SIZE / h; h = MAX_SIZE; }
                }

                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                hiddenInput.value = canvas.toDataURL(file.type);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>
</body>
</html>


