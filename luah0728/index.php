<?php require_once __DIR__ . '/includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUAH PORTFOLIO</title>
    <link rel="icon" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Luckiest Guy';
            src: url("assets/fonts/LuckiestGuy-Regular.ttf") format('truetype');
            font-weight: 900;
        }

        /* ── Index screen ─────────────────────────────────── */
        #index-screen {
            position: absolute;
            inset: 0;
            z-index: 10;
            pointer-events: none;
        }

        .index_box {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 62.5dvw; height: 68.89dvh;
            background: #fafafa;
            box-shadow: 4px 4px 0 0 #FF89B8;
        }

        @keyframes float {
            0%, 100% { transform: translate(-50%, -50%); }
            50%       { transform: translate(-50%, calc(-50% - 14px)); }
        }

        .index_logo {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 37.5dvw;
            height: calc(37.5dvw * 368 / 600);
            background: url("assets/images/logo.png") no-repeat center center / contain;
            filter: drop-shadow(0px 4px 0px #FF87BB);
            z-index: 11;
            display: block;
            text-decoration: none;
            animation: float 3s ease-in-out infinite;
            cursor: pointer;
        }

        /* ── Main layout (hidden until enter) ─────────────── */
        #main-screen {
            visibility: hidden;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .index_box {
                width: 70dvw;
                height: 70dvh;
            }
            .index_logo {
                width: 63.89dvw;
                height: calc(63.89dvw * 368 / 600);
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="background-deco">
        <div class="bd1"></div>
        <div class="bd2"></div>
        <div class="bd3"></div>
        <div class="bd4"></div>
        <div class="bd5"></div>
        <div class="bd6"></div>
    </div>

    <!-- ── Index landing screen ───────────────────────────── -->
    <div id="index-screen">
        <div class="index_box"></div>
    </div>
    <a class="index_logo" id="enter-btn" href="#"></a>

    <!-- ── Main layout ───────────────────────────────────── -->
    <div class="layout" id="main-screen">

        <nav class="side-nav">
            <a href="#/main" id="nav_main" class="nav-item" data-route="main">Main</a>
            <a href="#/gallery_Kitsch" id="nav_gallery_Kitsch" class="nav-item" data-route="gallery_Kitsch">ART</a>
            <a href="#/todo" id="nav_todo" class="nav-item" data-route="todo">To-Do</a>
        </nav>

        <div class="sub_menu">
            <?php if (isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                <button id="btn_logout" class="btn_auth"></button>
            <?php else: ?>
                <button id="btn_login" class="btn_auth" onclick="location.hash='#/login'"></button>
            <?php endif; ?>
        </div>

        <main id="content"></main>

        <svg id="prev_list" xmlns="http://www.w3.org/2000/svg" width="40" height="45" viewBox="0 0 40 45" fill="none" style="display:none">
            <path d="M3 18.8643C0.333465 20.4039 0.333467 24.2524 3 25.792L33 43.1123C35.6667 44.6519 39 42.7276 39 39.6484L39 5.00781C39 1.92861 35.6667 0.00434217 33 1.54394L3 18.8643Z" fill="#FAFAFA" stroke="#FF87BB" stroke-width="2"/>
        </svg>
        <svg id="next_list" xmlns="http://www.w3.org/2000/svg" width="40" height="45" viewBox="0 0 40 45" fill="none" style="display:none">
            <path d="M37 18.8643C39.6665 20.4039 39.6665 24.2524 37 25.792L7 43.1123C4.33333 44.6519 0.999998 42.7276 0.999998 39.6484L0.999999 5.00781C1 1.92861 4.33333 0.00434217 7 1.54394L37 18.8643Z" fill="#FAFAFA" stroke="#FF87BB" stroke-width="2"/>
        </svg>

        <div class="music_layout">
            <div class="music_box">
                <div class="music_main">
                    <div id="music_title">너머의 세계를 그리는 법</div>
                    <div class="music_time">
                        <div class="mt_bar">
                            <div class="mt_dot"></div>
                        </div>
                    </div>
                    <div class="music_times">
                        <span class="music_theTime">0:00</span>
                        <span class="music_remainTime">-0:00</span>
                    </div>
                    <div class="music_btn">
                        <svg id="bgm-play" xmlns="http://www.w3.org/2000/svg" width="18" height="21" viewBox="0 0 18 21" fill="none">
                            <path class="play-path" d="M17.25 9.2287C17.9167 9.6136 17.9167 10.5759 17.25 10.9608L1.5 20.054C0.833333 20.4389 -9.77521e-07 19.9578 -9.43872e-07 19.188L-1.48913e-07 1.00146C-1.15264e-07 0.231658 0.833334 -0.249466 1.5 0.135434L17.25 9.2287Z" fill="#FF89B8"/>
                        </svg>
                    </div>
                </div>
                <div class="vol_control">
                    <div class="vol_track">
                        <div class="vol_dot"></div>
                    </div>
                </div>
            </div>
            <div class="music_tape"></div>
        </div>

    </div><!-- /.layout #main-screen -->
</div><!-- /.container -->

<div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;"><div id="bgm-yt-frame"></div></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<script src="assets/js/main.js"></script>

<!-- Override main.js BGM system — placed after so it wins over function declarations -->
<script>
window.initBgmPlayer    = function() {};
window.fetchBgmPlaylist = function(cb) { if (cb) cb(); };
</script>

<script>
/* ── Skip index screen if hash exists (refresh / direct link) ── */
(function() {
    if (!location.hash) return;
    document.getElementById('index-screen').style.display = 'none';
    document.getElementById('enter-btn').style.display    = 'none';
    var ms = document.getElementById('main-screen');
    ms.style.visibility    = 'visible';
    ms.style.pointerEvents = 'auto';
})();

/* ── Gallery nav ─────────────────────────────────────────── */
$(window).on('hashchange.galleryNav', function() {
    var base = (location.hash || '').replace(/^#\/?/, '').split('/')[0];
    if (base && base.indexOf('gallery_') === 0) {
        $('.nav-item').removeClass('active');
        $('#nav_gallery_Kitsch').addClass('active');
    }
}).trigger('hashchange.galleryNav');

var GALLERY_ORDER = ['gallery_Kitsch','gallery_LD','gallery_OneLD','gallery_SD','gallery_Charcter','gallery_Dress','gallery_ETC'];

function currentGalleryIdx() {
    var base = (location.hash || '').replace(/^#\/?/, '').split('/')[0];
    return GALLERY_ORDER.indexOf(base);
}

function updateGalleryNavBtns() {
    $('#prev_list, #next_list').css('display', currentGalleryIdx() >= 0 ? 'block' : 'none');
}

$(window).on('hashchange.galleryBtn', updateGalleryNavBtns);
updateGalleryNavBtns();

$('#prev_list').on('click', function() {
    var idx = currentGalleryIdx();
    if (idx < 0) return;
    location.hash = '#/' + GALLERY_ORDER[(idx - 1 + GALLERY_ORDER.length) % GALLERY_ORDER.length];
});
$('#next_list').on('click', function() {
    var idx = currentGalleryIdx();
    if (idx < 0) return;
    location.hash = '#/' + GALLERY_ORDER[(idx + 1) % GALLERY_ORDER.length];
});

/* ── Custom BGM player ──────────────────────────────────── */
(function() {
    var BGM     = 'assets/bgm/bgm.mp3';
    var SPECIAL = 'assets/bgm/special.mp3';

    // BGM: special이 꺼져 있을 때 재생
    var bgm = new Audio(BGM);
    bgm.loop   = true;
    bgm.volume = 0.10;

    // Special: 재생 버튼으로만 제어
    var special        = new Audio(SPECIAL);
    special.loop       = false;
    special.volume     = 1.0;
    var specialPlaying = false;
    var specialActive  = false; // 재생 중이거나 일시정지 상태

    special.addEventListener('ended', function() {
        specialPlaying = false;
        specialActive  = false;
        special.currentTime = 0;
        updatePlayBtn(false);
        updateBar();
        bgm.play().catch(function(){});
    });
    special.addEventListener('timeupdate', updateBar);
    special.addEventListener('loadedmetadata', updateBar);
    special.addEventListener('durationchange', updateBar);

    function updateBar() {
        var cur = specialActive ? (special.currentTime || 0) : 0;
        var dur = specialActive && isFinite(special.duration) ? special.duration : 0;
        var theTime = document.querySelector('.music_theTime');
        var remTime = document.querySelector('.music_remainTime');
        var dot     = document.querySelector('.mt_dot');
        if (theTime) theTime.textContent = fmt(cur);
        if (remTime) remTime.textContent = '-' + fmt(Math.max(0, dur - cur));
        if (dot) dot.style.left = (dur > 0 ? (cur / dur * 100) : 0) + '%';
    }

    function fmt(sec) {
        sec = Math.max(0, Math.floor(sec));
        return Math.floor(sec / 60) + ':' + (sec % 60 < 10 ? '0' : '') + (sec % 60);
    }

    function updatePlayBtn(playing) {
        var svg = document.getElementById('bgm-play');
        if (!svg) return;
        if (playing) {
            svg.innerHTML =
                '<rect x="1" y="0" width="5" height="21" fill="#FF89B8" rx="1.5"/>' +
                '<rect x="10" y="0" width="5" height="21" fill="#FF89B8" rx="1.5"/>';
        } else {
            svg.innerHTML =
                '<path class="play-path" d="M17.25 9.2287C17.9167 9.6136 17.9167 10.5759 17.25 10.9608L1.5 20.054C0.833333 20.4389 -9.77521e-07 19.9578 -9.43872e-07 19.188L-1.48913e-07 1.00146C-1.15264e-07 0.231658 0.833334 -0.249466 1.5 0.135434L17.25 9.2287Z" fill="#FF89B8"/>';
        }
        svg.setAttribute('viewBox', '0 0 18 21');
    }

    document.getElementById('bgm-play').addEventListener('click', function() {
        if (specialPlaying) {
            // special 일시정지 → BGM 재개
            special.pause();
            specialPlaying = false;
            updatePlayBtn(false);
            bgm.play().catch(function(){});
        } else {
            // special 재생 시작 → BGM 정지
            bgm.pause();
            if (!specialActive) special.currentTime = 0;
            special.play().catch(function(){});
            specialPlaying = true;
            specialActive  = true;
            updatePlayBtn(true);
        }
    });

    var bar = document.querySelector('.mt_bar');
    if (bar) {
        var seeking = false;
        function seekAt(e) {
            if (!specialActive || !isFinite(special.duration)) return;
            var rect  = bar.getBoundingClientRect();
            var ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            special.currentTime = ratio * special.duration;
        }
        bar.addEventListener('mousedown',  function(e) { seeking = true; seekAt(e); });
        document.addEventListener('mousemove', function(e) { if (seeking) seekAt(e); });
        document.addEventListener('mouseup',   function()  { seeking = false; });
        bar.addEventListener('touchstart', function(e) { seeking = true; seekAt(e.touches[0]); }, { passive: true });
        document.addEventListener('touchmove', function(e) { if (seeking) seekAt(e.touches[0]); }, { passive: true });
        document.addEventListener('touchend',  function()  { seeking = false; });
    }

    // 볼륨 슬라이더 (세로)
    var volTrack = document.querySelector('.vol_track');
    var volDot   = document.querySelector('.vol_dot');
    if (volTrack && volDot) {
        var volDragging = false;
        function setVolFromY(clientY) {
            var rect  = volTrack.getBoundingClientRect();
            var ratio = Math.max(0, Math.min(1, (clientY - rect.top) / rect.height));
            var vol   = 1 - ratio;
            bgm.volume = vol;
            volDot.style.top = (ratio * 100) + '%';
        }
        volTrack.addEventListener('mousedown',  function(e) { volDragging = true; setVolFromY(e.clientY); });
        document.addEventListener('mousemove',  function(e) { if (volDragging) setVolFromY(e.clientY); });
        document.addEventListener('mouseup',    function()  { volDragging = false; });
        volTrack.addEventListener('touchstart', function(e) { volDragging = true; setVolFromY(e.touches[0].clientY); }, { passive: true });
        document.addEventListener('touchmove',  function(e) { if (volDragging) setVolFromY(e.touches[0].clientY); }, { passive: true });
        document.addEventListener('touchend',   function()  { volDragging = false; });
    }

    // 로고 클릭 시 BGM 시작 (사용자 제스처 컨텍스트 내에서 호출됨)
    window._startBgm = function() {
        bgm.play().catch(function(){});
    };
})();

/* ── Layout scaler ──────────────────────────────────────── */
(function() {
    function fitLayout() {
        var el = document.querySelector('.layout');
        if (!el) return;
        el.style.transform = '';
        el.style.left = '';
        el.style.top  = '';
        var W = el.offsetWidth;
        var H = el.offsetHeight;
        if (!W || !H) return;
        var scale = Math.min(window.innerWidth / W, window.innerHeight / H);
        el.style.transformOrigin = 'top left';
        el.style.transform = 'scale(' + scale + ')';
        el.style.left = ((window.innerWidth  - W * scale) / 2) + 'px';
        el.style.top  = ((window.innerHeight - H * scale) / 2) + 'px';
    }
    window.fitLayout = fitLayout;
    window.addEventListener('resize', fitLayout);
    fitLayout();
})();

/* ── Index → Main transition ────────────────────────────── */
document.getElementById('enter-btn').addEventListener('click', function(e) {
    e.preventDefault();
    window._startBgm();

    var indexScreen = document.getElementById('index-screen');
    var logo        = this;
    var mainScreen  = document.getElementById('main-screen');

    indexScreen.style.transition = 'opacity 0.4s';
    logo.style.transition        = 'opacity 0.4s';
    indexScreen.style.opacity    = '0';
    logo.style.opacity           = '0';

    setTimeout(function() {
        indexScreen.style.display = 'none';
        logo.style.display        = 'none';
        mainScreen.style.visibility   = 'visible';
        mainScreen.style.pointerEvents = 'auto';
        window.fitLayout();
        if (!location.hash) history.replaceState(null, '', '#/main');
        loadRoute(location.hash || '#/main');
    }, 400);
});
</script>
</body>
</html>
