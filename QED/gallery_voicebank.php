<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$stmt = $mysqli->prepare('SELECT id,title,thumbnail,is_private,created_at FROM `home_gallery_voicebank` ORDER BY id DESC');
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="container_gallery_voicebank">
  <?php if ($is_admin): ?>
  <a href="#/gallery_voicebank/upload" class="gallery-upload-btn btn">+</a>
  <?php endif; ?>
  <?php if (empty($posts)): ?>
  <p class="gallery-empty">게시물이 없습니다.</p>
  <?php else: ?>
  <div class="gallery-box">
    <?php foreach ($posts as $p):
      $thumb = $p['thumbnail'] ? htmlspecialchars($p['thumbnail']) : '';
      $bgImg = $thumb
        ? "linear-gradient(135deg, rgba(26,26,26,0) 40%, rgba(26,26,26,1) 60%), url('{$thumb}')"
        : 'none';
    ?>
    <div class="gallery-item"
         style="background-image: <?= $bgImg ?>;"
         onclick="location.hash='#/gallery_voicebank/detail/<?= $p['id'] ?>'">
      <div class="gallery-item-meta">
        <?php if ($p['is_private']): ?><i class="fa-solid fa-lock gallery-item-lock"></i><?php endif; ?>
        <span class="gallery-item-title"><?= htmlspecialchars($p['title']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
