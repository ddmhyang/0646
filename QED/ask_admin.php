<?php
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pending  = $mysqli->query('SELECT * FROM home_ask_board WHERE is_answered=0 ORDER BY created_at ASC')->fetch_all(MYSQLI_ASSOC);
$answered = $mysqli->query('SELECT * FROM home_ask_board WHERE is_answered=1 ORDER BY answered_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<div class="ask-admin-wrap">
  <div class="ask-section-title">미확인 (<?= count($pending) ?>)</div>
  <?php if (empty($pending)): ?>
  <div class="ask-empty" style="padding:20px 0">미확인 문의가 없습니다.</div>
  <?php endif; ?>
  <?php foreach ($pending as $item): ?>
  <div class="ask-admin-card" id="ask-card-<?= $item['id'] ?>">
    <div class="ask-item-menu">
      <button class="ask-item-menu-btn" onclick="toggleAskMenu(event, <?= $item['id'] ?>, false)">···</button>
    </div>
    <div class="ask-q-label">Q</div>
    <div class="ask-meta"><?= htmlspecialchars($item['name'] ?? '') ?><?= !empty($item['email']) ? ' · ' . htmlspecialchars($item['email']) : '' ?></div>
    <div class="ask-question"><?= htmlspecialchars($item['question']) ?></div>
    <button class="ask-answer-btn" onclick="confirmAsk(<?= $item['id'] ?>)">확인</button>
  </div>
  <?php endforeach; ?>

  <div class="ask-section-title" style="margin-top:32px">확인 완료 (<?= count($answered) ?>)</div>
  <?php if (empty($answered)): ?>
  <div class="ask-empty" style="padding:20px 0">확인 완료된 문의가 없습니다.</div>
  <?php endif; ?>
  <?php foreach ($answered as $item): ?>
  <div class="ask-admin-card" id="ask-card-<?= $item['id'] ?>">
    <div class="ask-item-menu">
      <button class="ask-item-menu-btn" onclick="toggleAskMenu(event, <?= $item['id'] ?>, true)">···</button>
    </div>
    <div class="ask-q-label" style="color:#aaa">Q</div>
    <div class="ask-meta"><?= htmlspecialchars($item['name'] ?? '') ?><?= !empty($item['email']) ? ' · ' . htmlspecialchars($item['email']) : '' ?></div>
    <div class="ask-question" style="color:#aaa"><?= htmlspecialchars($item['question']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="ask-ctx-menu" id="ask-ctx-menu"></div>
<script>
function toggleAskMenu(e, id, isConfirmed) {
  e.stopPropagation();
  var menu = $('#ask-ctx-menu');
  var html = '';
  if (isConfirmed) html += '<button onclick="unconfirmAsk('+id+')">미확인으로</button>';
  html += '<button class="ask-ctx-del" onclick="deleteAsk('+id+')">삭제</button>';
  menu.html(html);
  var rect = e.currentTarget.getBoundingClientRect();
  menu.css({ top: rect.bottom + 4, left: rect.right - menu.outerWidth() - 4 }).show();
}
$(document).on('click', function(e) {
  if (!$(e.target).closest('#ask-ctx-menu, .ask-item-menu-btn').length) $('#ask-ctx-menu').hide();
});
function confirmAsk(id) {
  $.post('ajax_answer_ask.php', { id: id })
   .done(function(d) {
     if (d.success) location.reload();
     else alert(d.message || '처리 실패');
   });
}
function unconfirmAsk(id) {
  $('#ask-ctx-menu').hide();
  $.post('ajax_edit_ask.php', { id: id })
   .done(function(d) {
     if (d.success) location.reload();
     else alert(d.message || '처리 실패');
   });
}
function deleteAsk(id) {
  $('#ask-ctx-menu').hide();
  if (!confirm('삭제하시겠습니까?')) return;
  $.post('ajax_delete_ask.php', { id: id })
   .done(function(d) {
     if (d.success) $('#ask-card-' + id).fadeOut(200, function() { $(this).remove(); });
     else alert(d.message || '삭제 실패');
   });
}
</script>
