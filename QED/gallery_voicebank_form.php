<?php
ob_start();
require_once __DIR__ . '/includes/db.php';
ob_clean();
if (!isset($_SESSION['admin'])) { echo '<p>권한이 없습니다.</p>'; exit; }

$id   = (int)($_GET['id'] ?? 0);
$post = null;
$audios = [];

if ($id) {
    $stmt = $mysqli->prepare('SELECT * FROM `home_gallery_voicebank` WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    if (!$post) { echo '<p>게시물을 찾을 수 없습니다.</p>'; exit; }

    $a_stmt = $mysqli->prepare('SELECT title, url FROM `home_gallery_voicebank_audio` WHERE post_id = ? ORDER BY sort_order, id');
    $a_stmt->bind_param('i', $id);
    $a_stmt->execute();
    $audios = $a_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$thumbUrl    = htmlspecialchars($post['thumbnail'] ?? '');
$downloadVal = htmlspecialchars($post['download_link'] ?? '');
$titleVal    = htmlspecialchars($post['title'] ?? '');
$profileVal  = htmlspecialchars($post['profile'] ?? '');
$cancelHref  = $id ? '#/gallery_voicebank/detail/' . $id : '#/gallery_voicebank';
?>
<div class="vb-upload-wrap">
  <div class="introduce-box">
    <div class="intro-left vb-illust-zone" id="illust_zone" onclick="document.getElementById('inp_thumb').click()">
      <?php if ($post && $post['thumbnail']): ?>
      <img id="illust_preview" src="<?= $thumbUrl ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
      <span id="illust_hint" style="display:none">클릭하여<br>일러스트 교체</span>
      <?php else: ?>
      <img id="illust_preview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
      <span id="illust_hint">클릭하여<br>일러스트 업로드</span>
      <?php endif; ?>
      <input type="file" id="inp_thumb" accept="image/*" style="display:none;" onchange="previewIllust(this)">
      <input type="hidden" id="illust_url" value="<?= $thumbUrl ?>">
    </div>
    <div class="intro-right vb-form-fields">
      <div class="form-group" style="flex-shrink:0">
        <label>이름</label>
        <input type="text" id="inp_title" value="<?= $titleVal ?>" placeholder="보이스뱅크 이름" required>
      </div>
      <div class="form-group" style="flex:1;min-height:0;display:flex;flex-direction:column;">
        <label style="flex-shrink:0">프로필</label>
        <textarea class="summernote_vb"><?= $profileVal ?></textarea>
      </div>
      <div class="form-group" style="flex-shrink:0">
        <label>다운로드 링크</label>
        <input type="text" id="inp_download" value="<?= $downloadVal ?>" placeholder="https://...">
      </div>
    </div>
  </div>
  <div class="vb-audio-section">
    <div class="vb-audio-header">
      <span>음원 목록</span>
      <button type="button" onclick="addAudioRow()">+ 추가</button>
    </div>
    <div id="audio_rows"></div>
  </div>
  <div class="vb-form-actions">
    <button type="button" id="vb_save_btn" onclick="saveVB(<?= $id ?>)">저장</button>
    <a href="<?= $cancelHref ?>" class="btn btn-secondary">취소</a>
  </div>
</div>
<script>
initSummernote('.summernote_vb', { height: 120, toolbar: [
  ['font', ['bold', 'italic', 'underline', 'clear']],
  ['fontsize', ['fontsize']],
  ['color', ['color']],
  ['para', ['paragraph']],
]});

(function() {
  var existing = <?= json_encode($audios) ?>;
  existing.forEach(function(a) { addAudioRow(a.title, a.url); });
})();

var _pendingUploads = 0;

function _uploadBusy(delta) {
  _pendingUploads += delta;
  document.getElementById('vb_save_btn').disabled = _pendingUploads > 0;
}

async function previewIllust(input) {
  var file = input.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var img = document.getElementById('illust_preview');
    img.src = e.target.result;
    img.style.display = 'block';
    document.getElementById('illust_hint').style.display = 'none';
  };
  reader.readAsDataURL(file);
  document.getElementById('illust_url').value = '';
  _uploadBusy(1);
  try {
    var f = await compressIfNeeded(file, 18);
    var fd = new FormData();
    fd.append('image', f);
    var data = await $.ajax({ url: 'ajax_upload_image.php', method: 'POST', data: fd, processData: false, contentType: false });
    if (data.success) document.getElementById('illust_url').value = data.url;
    else alert('이미지 업로드 실패: ' + (data.message || ''));
  } catch(e) {
    alert('이미지 업로드 실패: ' + (e.responseText ? e.responseText.substring(0, 300) : String(e)));
  }
  _uploadBusy(-1);
}

var _arIdx = 0;
function addAudioRow(titleVal, urlVal) {
  var idx = _arIdx++;
  var $row = $('<div class="audio-upload-row" draggable="false" data-idx="' + idx + '">' +
    '<span class="drag-handle">⋮</span>' +
    '<input type="text" class="ar-title" placeholder="음원 제목" value="' + (titleVal ? $('<span>').text(titleVal).html() : '') + '">' +
    '<label class="ar-file-label">파일 선택<input type="file" class="ar-file" accept="audio/*" onchange="uploadAudio(this,' + idx + ')"></label>' +
    '<span class="ar-status">' + (urlVal ? '등록됨' : '-') + '</span>' +
    '<input type="hidden" class="ar-url" value="' + (urlVal ? $('<span>').text(urlVal).html() : '') + '">' +
    '<button type="button" style="flex-shrink:0" onclick="$(this).closest(\'.audio-upload-row\').remove()">×</button>' +
  '</div>');
  $('#audio_rows').append($row);
}

function uploadAudio(input, idx) {
  var $row = $('.audio-upload-row[data-idx=' + idx + ']');
  var file = input.files[0];
  if (!file) return;
  $row.find('.ar-url').val('');
  $row.find('.ar-status').text('0%');
  _uploadBusy(1);
  var xhr = new XMLHttpRequest();
  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) $row.find('.ar-status').text(Math.round(e.loaded / e.total * 100) + '%');
  });
  xhr.addEventListener('load', function() {
    _uploadBusy(-1);
    try {
      var d = JSON.parse(xhr.responseText);
      if (d.success) { $row.find('.ar-url').val(d.url); $row.find('.ar-status').text(file.name); }
      else $row.find('.ar-status').text('실패: ' + (d.message || ''));
    } catch(e) { $row.find('.ar-status').text('오류(' + xhr.status + '): ' + xhr.responseText.substring(0, 100)); }
  });
  xhr.addEventListener('error', function() { _uploadBusy(-1); $row.find('.ar-status').text('오류'); });
  xhr.open('POST', 'ajax_upload_audio.php');
  var fd = new FormData();
  fd.append('audio', file);
  xhr.send(fd);
}

(function() {
  var $c = $('#audio_rows'), dragSrc = null;
  $c.on('mousedown', '.drag-handle', function() {
    $(this).closest('.audio-upload-row').attr('draggable', 'true');
  });
  $c.on('dragstart', '.audio-upload-row', function(e) {
    dragSrc = this;
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/plain', '');
    var s = this;
    setTimeout(function() { $(s).addClass('dragging'); }, 0);
  });
  $c.on('dragend', '.audio-upload-row', function() {
    this.draggable = false;
    $(this).removeClass('dragging');
    $c.find('.drag-over').removeClass('drag-over');
    dragSrc = null;
  });
  $c.on('dragover', '.audio-upload-row', function(e) {
    e.preventDefault();
    if (!dragSrc || this === dragSrc) return;
    var mid = $(this).offset().top + $(this).outerHeight() / 2;
    if (e.originalEvent.clientY <= mid) $(dragSrc).insertBefore(this);
    else $(dragSrc).insertAfter(this);
  });
  $c.on('drop', '.audio-upload-row', function(e) { e.preventDefault(); });
})();

function saveVB(editId) {
  if (_pendingUploads > 0) { alert('업로드 중입니다. 완료 후 저장하세요.'); return; }
  var title = $('#inp_title').val().trim();
  if (!title) { alert('이름을 입력하세요.'); return; }
  var profile = $('.summernote_vb').summernote('code');
  var downloadLink = $('#inp_download').val().trim();
  var thumbUrl = document.getElementById('illust_url').value;
  var audios = [];
  $('#audio_rows .audio-upload-row').each(function() {
    var t = $(this).find('.ar-title').val().trim();
    var u = $(this).find('.ar-url').val().trim();
    if (u) audios.push({ title: t, url: u });
  });
  var data = { title: title, profile: profile, download_link: downloadLink, thumbnail: thumbUrl, audio_json: JSON.stringify(audios) };
  if (editId) data.id = editId;
  $.post('ajax_save_gallery_voicebank.php', data)
   .done(function(d) { if (d.success) location.hash = '#/gallery_voicebank/detail/' + d.id; else alert(d.message); });
}
</script>
