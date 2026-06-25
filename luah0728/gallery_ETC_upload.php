<?php
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['admin'])) { echo '<p>권한이 없습니다.</p>'; exit; }
?>
<div class="container_gallery_ETC_upload">
<div class="form-wrap">
  <h2>ETC — 새 글쓰기</h2>
  <form id="form_upload">
    <div class="form-group">
      <label>제목</label>
      <input type="text" id="inp_title" required>
    </div>
    <div class="form-group">
      <label>내용</label>
      <textarea class="summernote"></textarea>
    </div>
    <div class="form-group">
      <label>썸네일 <small style="color:#888;font-weight:normal">(선택 — 미지정 시 본문 첫 이미지 자동 사용)</small></label>
      <input type="file" id="inp_thumb" accept="image/*">
    </div>
    <div class="form-group">
      <label><input type="checkbox" id="inp_private" value="1"> 비밀글</label>
    </div>
    <div class="form-group" id="pw_wrap" style="display:none">
      <label>비밀번호</label>
      <input type="password" id="inp_password" style="max-width:240px">
    </div>
    <div class="form-actions">
      <button type="submit">저장</button>
      <a href="#/gallery_ETC" class="btn btn-secondary">취소</a>
    </div>
  </form>
</div>
<script>
initSummernote('.summernote');
$('#inp_private').on('change', function(){ $('#pw_wrap').toggle(this.checked); });
$('#form_upload').on('submit', function(e) {
  e.preventDefault();
  var isPrivate = $('#inp_private').is(':checked');
  var content = $('.summernote').summernote('code');
  function doSave(thumbUrl) {
    var data = { title: $('#inp_title').val(), content: content, is_private: isPrivate ? 1 : 0, password: isPrivate ? ($('#inp_password').val() || '1234') : '', thumbnail: thumbUrl };
    $.post('ajax_save_gallery_ETC.php', data)
     .done(function(d) { if(d.success) location.hash='#/gallery_ETC/detail/'+d.id; else alert(d.message); });
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