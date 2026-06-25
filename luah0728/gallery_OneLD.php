<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$stmt = $mysqli->prepare('SELECT id,title,thumbnail,is_private,created_at FROM `home_gallery_OneLD` ORDER BY id DESC');
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="container_gallery_OneLD">
<div class="gallery-header">
  <h2>OneLD</h2>
  <?php if ($is_admin): ?>
  <a href="#/gallery_OneLD/upload" class="btn">글쓰기</a>
  <?php endif; ?>
</div>
<?php if (empty($posts)): ?>
<p style="color:#aaa">게시물이 없습니다.</p>
<?php else: ?>
<div class="gallery-grid">
  <?php foreach ($posts as $p): ?>
  <div class="gallery-card" onclick="location.hash='#/gallery_OneLD/detail/<?= $p['id'] ?>'">
    <?php if ($p['thumbnail']): ?><img src="<?= htmlspecialchars($p['thumbnail']) ?>" alt=""><?php else: ?><div class="no-thumb"></div><?php endif; ?>
    <div class="card-body">
      <div class="card-title">
        <?php if ($p['is_private']): ?><span class="private-badge"><i class="fa-solid fa-lock"></i> </span><?php endif; ?>
        <?= htmlspecialchars($p['title']) ?>
      </div>
      <div class="card-meta"><?= date('Y.m.d', strtotime($p['created_at'])) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
