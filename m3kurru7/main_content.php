<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
$stmt = $mysqli->prepare("SELECT content FROM home_pages WHERE slug='main_content' LIMIT 1");
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc()['content'] ?? '';
$stmt2 = $mysqli->prepare("SELECT value FROM home_settings WHERE `key`='dday_date' LIMIT 1");
$stmt2->execute();
$dday_date = $stmt2->get_result()->fetch_assoc()['value'] ?? '';
?>
<div class="container_main_content">
<?php if (!empty($dday_date)): ?>
<div id="dday-display" class="dday-badge"></div>
<script>document.getElementById('dday-display').textContent = calcDday('<?= htmlspecialchars($dday_date) ?>');</script>
<?php endif; ?>
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
<div id="page_view" class="post-content"><?= $content ?></div>
<?php if ($is_admin): ?>
<script>
$('#btn_edit').on('click', function() {
  initSummernote('.summernote');
  $('#editor_wrap').show();
  $('#page_view,#btn_edit').hide();
});
function savePage() {
  var html = $('.summernote').summernote('code');
  $.post('ajax_save_page_main_content.php', { content: html })
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
