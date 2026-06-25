<?php require_once __DIR__ . '/includes/db.php'; ?>
<div id="ask-form-area">
  <div class="ask-form-wrap">
    <textarea id="ask_question" placeholder="궁금한 점을 자유롭게 적어주세요.&#10;익명으로 전송됩니다." rows="8"></textarea>
    <button class="ask-send-btn" onclick="submitAsk()">전송</button>
  </div>
</div>
<div id="ask-success-area" style="display:none" class="ask-success-msg">
  <p>질문이 전송되었습니다.</p>
  <span>답변이 달리면 게시판에 공개됩니다.</span>
</div>
<script>
function submitAsk() {
  var q = $('#ask_question').val().trim();
  if (!q) { alert('내용을 입력해주세요.'); return; }
  $.post('ajax_submit_ask.php', { question: q })
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