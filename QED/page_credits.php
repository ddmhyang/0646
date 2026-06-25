<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$slug_key = 'credits';
$stmt = $mysqli->prepare('SELECT content FROM home_pages WHERE slug=? LIMIT 1');
$stmt->bind_param('s', $slug_key);
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc()['content'] ?? '';
?>
<div class="container_page_credits">
<div class="page-view">
  <div id="page_view" class="post-content"><?= $content ?></div>
  <?php if ($is_admin): ?>
  <button id="btn_edit" class="btn" style="margin-bottom:12px">편집</button>
  <div id="editor_wrap" style="display:none">
    <textarea class="summernote"><?= $content ?></textarea>
    <div class="form-actions" style="margin-top:10px">
      <button onclick="savePage()">저장</button>
      <button class="btn-secondary" onclick="cancelEdit()">취소</button>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php if ($is_admin): ?>
<script>
$('#btn_edit').on('click', function() {
  initSummernote('.summernote');
  $('#editor_wrap').show();
  $('#page_view,#btn_edit').hide();
});
function savePage() {
  var html = $('.summernote').summernote('code');
  $.post('ajax_save_page_credits.php', { content: html })
   .done(function(d) {
     if (d.success) { $('#page_view').html(html).show(); $('#editor_wrap').hide(); $('#btn_edit').show(); }
     else alert(d.message);
   });
}
function cancelEdit() {
  $('.summernote').summernote('destroy');
  $('#editor_wrap').hide();
  $('#page_view,#btn_edit').show();
}
</script>
<?php endif; ?>
</div>
