<?php require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['admin'])) { echo '<p>권한이 없습니다.</p>'; exit; }
$id   = (int)($_GET['id'] ?? 0);
$post = $mysqli->query("SELECT * FROM home_road_M3_posts WHERE id=$id")->fetch_assoc();
if (!$post) { echo '<p>글을 찾을 수 없습니다.</p>'; exit; }
?>
<div class="container_roadbee">
  <div class="form-wrap">
  <h2>글 수정</h2>
  <form id="form_rb_edit">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-group"><label>제목</label><input name="title" type="text" value="<?= htmlspecialchars($post['title']) ?>" required></div>
    <div class="form-group"><label>내용</label><div id="rb_editor"></div></div>
    <div class="form-group">
      <label>썸네일 <small>(선택 — 미지정 시 본문 첫 이미지 자동 사용)</small></label>
      <?php if ($post['thumbnail']): ?>
      <div id="thumb-preview" style="margin-bottom:8px;">
        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" alt="" style="max-width:160px;border-radius:8px;">
        <button type="button" class="btn btn-secondary" id="btn_clear_thumb" style="margin-left:8px;">삭제</button>
      </div>
      <?php endif; ?>
      <input type="file" id="inp_thumb" accept="image/*">
      <input type="hidden" id="hid_thumb" value="<?= htmlspecialchars($post['thumbnail'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label><input type="checkbox" id="inp_secret" value="1" <?= $post['is_secret'] ? 'checked' : '' ?>> 비밀글</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">저장</button>
      <button type="button" class="btn btn-secondary" onclick="history.back()">취소</button>
    </div>
  </form>
  </div>
</div>
<script>
initSummernote('#rb_editor');
$('#rb_editor').summernote('code',<?= json_encode($post['content']) ?>);
$('#btn_clear_thumb').on('click',function(){ $('#hid_thumb').val(''); $('#thumb-preview').remove(); });
$('#form_rb_edit').on('submit',function(e){
  e.preventDefault();
  var title=$('[name=title]').val().trim();
  var content=$('#rb_editor').summernote('code');
  var isSecret=$('#inp_secret').is(':checked')?1:0;
  if(!title){alert('제목을 입력하세요.');return;}
  function doSave(thumbUrl){
    $.post('ajax_save_road.php',{road:'M3',id:<?= $id ?>,title:title,content:content,thumbnail:thumbUrl,is_secret:isSecret}).done(function(d){
      if(d.success) location.hash='#/road_M3';
      else alert(d.message);
    });
  }
  var f=$('#inp_thumb')[0].files[0];
  if(f){
    var fd=new FormData(); fd.append('image',f);
    $.ajax({url:'ajax_upload_image.php',type:'POST',data:fd,processData:false,contentType:false})
     .done(function(d){doSave(d.url||$('#hid_thumb').val());}).fail(function(){doSave($('#hid_thumb').val());});
  } else { doSave($('#hid_thumb').val()); }
});
</script>