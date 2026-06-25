<?php require_once __DIR__ . '/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ELUCiD_QED</title>
  <link rel="icon" href="assets/images/favicon.png">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">

  <!-- 뷰포트 전체를 채우는 배경 — .layout 밖 -->
  <div class="background-deco">
    <div class="background-math"></div>
    <div class="background_box1"></div>
    <div class="background_box2"></div>
    <footer>
      <a class="footerTxt1" href="https://x.com/ELUCiD_QED">X</a>
      <span class="footerTxt2">ⓒ 2026 ELUCiD_QED. All rights reserved. ⓒ Created by d_dmhyang</span>
    </footer>
  </div>

  <div class="layout">
    <main id="content"></main>
    <div class="site-logo"></div>
    <nav class="side-nav">
      <a href="#/" id="nav_main" class="nav-item" data-route=""></a>
      <a href="#/gallery_voicebank" id="nav_gallery_voicebank" class="nav-item" data-route="gallery_voicebank"></a>
      <a href="#/page_GUIDELINE" id="nav_page_GUIDELINE" class="nav-item" data-route="page_GUIDELINE"></a>
      <a href="#/page_credits" id="nav_page_credits" class="nav-item" data-route="page_credits"></a>
      <a href="#/askboard" id="nav_askboard" class="nav-item" data-route="askboard"></a>
    </nav>
    <div class="sub_menu">
      <?php if (isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
        <button id="btn_logout" class="btn_auth"></button>
      <?php else: ?>
        <button id="btn_login" class="btn_auth" onclick="location.hash='#/login'"></button>
      <?php endif; ?>
    </div>
  </div>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
