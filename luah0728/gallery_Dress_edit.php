<?php
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['admin'])) { echo '<p>권한이 없습니다.</p>'; exit; }
$id = (int)($_GET['id'] ?? 0);
$stmt = $mysqli->prepare('SELECT * FROM `home_gallery_Dress` WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) { echo '<p>게시물을 찾을 수 없습니다.</p>'; exit; }
?>
<div class="container_gallery_Dress_edit">
<div class="form-wrap">
  <h2>Dress — 수정</h2>
  <form id="form_edit">
    <input type="hidden" id="inp_id" value="<?= $post['id'] ?>">
    <div class="form-group">
      <label>제목</label>
      <input type="text" id="inp_title" value="<?= htmlspecialchars($post['title']) ?>" required>
    </div>
    <div class="form-group">
      <label>내용</label>
      <textarea class="summernote"><?= $post['content'] ?></textarea>
    </div>
    <div class="form-group">
      <label>썸네일 <small style="color:#888;font-weight:normal">(새 파일 선택 시 교체 / 미선택 시 기존 유지, 기존 없으면 본문 첫 이미지 자동 사용)</small></label>
      <?php if ($post['thumbnail']): ?>
      <div style="margin-bottom:6px"><img src="<?= htmlspecialchars($post['thumbnail']) ?>" style="max-height:100px;border-radius:6px;border:1px solid #ddd" onerror="this.style.display='none'"></div>
      <?php endif; ?>
      <input type="file" id="inp_thumb" accept="image/*">
    </div>
    <div class="form-group">
      <label><input type="checkbox" id="inp_private" value="1" <?= $post['is_private'] ? 'checked' : '' ?>> 비밀글</label>
    </div>
    <div class="form-group" id="pw_wrap" style="<?= $post['is_private'] ? '' : 'display:none' ?>">
      <label>비밀번호 변경 (기존 유지 시 비워두기, 신규 비밀글은 1234 기본값)</label>
      <input type="password" id="inp_password" style="max-width:240px">
    </div>
    <div class="form-actions">
      <button type="submit">저장</button>
      <a href="#/gallery_Dress/detail/<?= $post['id'] ?>" class="btn btn-secondary">취소</a>
    </div>
  </form>
</div>
<script>
initSummernote('.summernote');
$('#inp_private').on('change', function(){ $('#pw_wrap').toggle(this.checked); });
$('#form_edit').on('submit', function(e) {
  e.preventDefault();
  var isPrivate = $('#inp_private').is(':checked');
  var content = $('.summernote').summernote('code');
  function doSave(thumbUrl) {
    var data = { id: $('#inp_id').val(), title: $('#inp_title').val(), content: content, is_private: isPrivate ? 1 : 0, password: isPrivate ? ($('#inp_password').val() || '') : '', thumbnail: thumbUrl };
    $.post('ajax_save_gallery_Dress.php', data)
     .done(function(d) { if(d.success) location.hash='#/gallery_Dress/detail/'+d.id; else alert(d.message); });
  }
  var f = $('#inp_thumb')[0].files[0];
  if (f) {
    var fd = new FormData(); fd.append('image', f);
    $.ajax({ url: 'ajax_upload_image.php', type: 'POST', data: fd, processData: false, contentType: false })
     .done(function(d){ doSave(d.url||''); })
     .fail(function(){ doSave(''); });
  } else {
    doSave('');
  }
});
</script>
</div>