<?php
require_once __DIR__ . '/includes/db.php';
$is_admin = isset($_SESSION['admin']);
?>
<?php if ($is_admin): ?>
<script>location.hash = '#/ask_admin';</script>
<?php endif; ?>
<div class="askboard-wrap">
  <p class="ask-notice">기타 문의 시 하단의 이메일로 보내주세요.</p>
  <div id="ask-form-area" class="ask-form-wrap">
    <input type="text"  id="ask_name"     class="ask-input" placeholder="이름">
    <input type="email" id="ask_email"    class="ask-input" placeholder="이메일">
    <textarea           id="ask_question" placeholder="내용" rows="6"></textarea>
    <button class="ask-send-btn" onclick="submitAsk()">전송</button>
  </div>
  <div id="ask-success-area" style="display:none" class="ask-success-msg">
    <p>질문이 전송되었습니다.</p>
  </div>
</div>
<script>
function submitAsk() {
  var name  = $('#ask_name').val().trim();
  var email = $('#ask_email').val().trim();
  var q     = $('#ask_question').val().trim();
  if (!name || !q) { alert('이름과 내용을 입력해주세요.'); return; }
  $.post('ajax_submit_ask.php', { name: name, email: email, question: q })
   .done(function(d) {
     if (d.success) {
       $('#ask-form-area').hide();
       $('#ask-success-area').show();
     } else {
       alert(d.message || '전송에 실패했습니다.');
     }
   });
}
</script>
