<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo '<p>잘못된 요청입니다.</p>'; exit; }
$stmt = $mysqli->prepare('SELECT * FROM `home_gallery_voicebank` WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) { echo '<p>게시물을 찾을 수 없습니다.</p>'; exit; }
if ($post['is_private'] && !$is_admin) {
    $sess_key = 'post_access_voicebank_' . $id;
    if (!isset($_SESSION[$sess_key]) || $_SESSION[$sess_key] < time()) {
        echo '<div class="form-wrap form-secret" style="max-width:360px">';
        echo '<h3><i class="fa-solid fa-lock"></i>  비밀글</h3>';
        echo '<p style="margin:12px 0;color:#888">비밀번호를 입력하세요.</p>';
        echo '<div style="display:flex;gap:8px">';
        echo '<input type="password" id="pw_input" placeholder="비밀번호" style="flex:1;padding:8px 12px;border:1px solid #ddd" onkeydown="if(event.key===\'Enter\')verifyPw(' . $id . ')">';
        echo '<button onclick="verifyPw(' . $id . ')">확인</button>';
        echo '</div>';
        echo '<p id="pw-msg" style="color:red;margin-top:8px;font-size:13px"></p>';
        echo '</div>';
        echo '<script>function verifyPw(id){$.post("ajax_verify_password_voicebank.php",{id:id,password:$("#pw_input").val()}).done(function(d){if(d.success)location.reload();else $("#pw-msg").text(d.message);});}</script>';
        exit;
    }
}
$a_stmt = $mysqli->prepare('SELECT title, url FROM `home_gallery_voicebank_audio` WHERE post_id = ? ORDER BY sort_order, id');
$a_stmt->bind_param('i', $id);
$a_stmt->execute();
$audios = $a_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="detail-wrap">
  <div class="introduce-box" style="position:relative">
    <?php if ($is_admin): ?>
    <div style="position:absolute;top:10px;right:10px;display:flex;gap:6px;z-index:10">
      <a href="#/gallery_voicebank/edit/<?= $post['id'] ?>" class="btn">수정</a>
      <button class="btn btn-danger" onclick="delVBPost(<?= $post['id'] ?>)">삭제</button>
    </div>
    <?php endif; ?>
    <div class="intro-left">
      <?php if ($post['thumbnail']): ?>
      <img src="<?= htmlspecialchars($post['thumbnail']) ?>" alt="">
      <?php endif; ?>
    </div>
    <div class="intro-right">
      <h2 class="intro-title"><?= htmlspecialchars($post['title']) ?></h2>
      <div class="intro-scroll">
        <div class="intro-profile post-content"><?= $post['profile'] ?? '' ?></div>
        <?php if (!empty($post['download_link'])): ?>
        <?php
          $dl_url = $post['download_link'];
          if (!preg_match('/^https?:\/\//i', $dl_url)) $dl_url = 'https://' . $dl_url;
        ?>
        <div style="padding-top:10px">
          <a href="<?= htmlspecialchars($dl_url) ?>" class="btn" target="_blank" rel="noopener">다운로드</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if (!empty($audios)): ?>
  <div class="audio-list">
    <?php foreach ($audios as $audio): ?>
    <div class="audio-item" data-url="<?= htmlspecialchars($audio['url']) ?>">
      <span class="audio-title"><?= htmlspecialchars($audio['title']) ?></span>
      <button class="audio-play-btn" onclick="toggleAudio(this)">▶</button>
      <div class="audio-bar-wrap">
        <div class="audio-progress"></div>
        <div class="audio-handle"></div>
      </div>
      <span class="audio-time">0:00</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div style="text-align:right;padding:8px 14px;background:#f5f5f5">
    <a href="#/gallery_voicebank" class="btn btn-list">목록</a>
  </div>
</div>
<?php if ($is_admin): ?>
<script>
function delVBPost(id) {
  if (!confirm('삭제하시겠습니까?')) return;
  $.post('ajax_delete_gallery_voicebank.php', { id: id })
   .done(function(d) { if(d.success) location.hash='#/gallery_voicebank'; else alert(d.message); });
}
</script>
<?php endif; ?>
