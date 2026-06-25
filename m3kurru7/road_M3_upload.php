<?php require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['admin'])) { echo '<p>권한이 없습니다.</p>'; exit; }
?>
<div class="container_roadbee">
  <div class="form-wrap">
  <h2>새 글 작성</h2>
  <form id="form_rb_upload">
    <div class="form-group">
      <label>제목</label>
      <input type="text" id="rb_title" required>
    </div>
    <div class="form-group">
      <label>내용</label>
      <div id="rb_editor"></div>
    </div>
    <div class="form-group">
      <label>썸네일 <small style="color:#888;font-weight:normal">(선택 — 미지정 시 본문 첫 이미지 자동 사용)</small></label>
      <input type="file" id="inp_thumb" accept="image/*">
    </div>
    <div class="form-group">
      <label><input type="checkbox" id="inp_secret" value="1"> 비밀글</label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">등록</button>
      <button type="button" class="btn btn-secondary" onclick="history.back()">취소</button>
    </div>
  </form>
  </div>
</div>
<script>
initSummernote('#rb_editor');
$('#form_rb_upload').on('submit',function(e){
  e.preventDefault();
  var title=$('#rb_title').val().trim();
  var content=$('#rb_editor').summernote('code');
  var isSecret=$('#inp_secret').is(':checked')?1:0;
  if(!title){alert('제목을 입력하세요.');return;}
  function doSave(thumbUrl){
    $.post('ajax_save_road.php',{road:'M3',title:title,content:content,thumbnail:thumbUrl,is_secret:isSecret}).done(function(d){
      if(d.success) location.hash='#/road_M3';
      else alert(d.message);
    });
  }
  var f=$('#inp_thumb')[0].files[0];
  if(f){
    var fd=new FormData(); fd.append('image',f);
    $.ajax({url:'ajax_upload_image.php',type:'POST',data:fd,processData:false,contentType:false})
     .done(function(d){doSave(d.url||'');}).fail(function(){doSave('');});
  } else { doSave(''); }
});
</script>