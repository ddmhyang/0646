<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$is_member = isset($_SESSION['user']);
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo '<p>잘못된 요청입니다.</p>'; exit; }
$stmt = $mysqli->prepare('SELECT * FROM `home_gallery_SD` WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) { echo '<p>게시물을 찾을 수 없습니다.</p>'; exit; }
if ($post['is_private'] && !$is_admin) {
    $sess_key = 'post_access_SD_' . $id;
    if (!isset($_SESSION[$sess_key]) || $_SESSION[$sess_key] < time()) {
        echo '<div class="form-wrap form-secret" style="max-width:360px">';
        echo '<h3><i class="fa-solid fa-lock"></i>  비밀글</h3>';
        echo '<p style="margin:12px 0;color:#888">비밀번호를 입력하세요.</p>';
        echo '<div style="display:flex;gap:8px">';
        echo '<input type="password" id="pw_input" placeholder="비밀번호" style="flex:1;padding:8px 12px;border:1px solid #ddd;border-radius:6px" onkeydown="if(event.key===\'Enter\')verifyPw(' . $id . ')">';
        echo '<button onclick="verifyPw(' . $id . ')">확인</button>';
        echo '</div>';
        echo '<p id="pw-msg" style="color:red;margin-top:8px;font-size:13px"></p>';
        echo '</div>';
        echo '<script>';
        echo 'function verifyPw(id){';
        echo '  $.post("ajax_verify_password_SD.php",{id:id,password:$("#pw_input").val()})';
        echo '  .done(function(d){if(d.success)location.reload();else $("#pw-msg").text(d.message);});';
        echo '}';
        echo '</script>';
        exit;
    }
}
?>
<div class="container_gallery_SD_detail">
<div class="post-header" style="display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
  </div>
  <?php if ($is_admin): ?>
  <div style="display:flex;gap:8px;">
    <a href="#/gallery_SD/edit/<?= $post['id'] ?>" class="btn">수정</a>
    <button class="btn btn-danger" onclick="delPost(<?= $post['id'] ?>)">삭제</button>
  </div>
  <?php endif; ?>
</div>
<div class="post-content"><?= $post['content'] ?></div>
<div class="post-actions">
  <a href="#/gallery_SD" class="btn btn-list">목록</a>
</div>
<?php if ($is_admin): ?>
<script>
function delPost(id) {
  if (!confirm('삭제하시겠습니까?')) return;
  $.post('ajax_delete_gallery_SD.php', { id: id })
   .done(function(d) { if(d.success) location.hash='#/gallery_SD'; else alert(d.message); });
}
</script>
<?php endif; ?></div>
