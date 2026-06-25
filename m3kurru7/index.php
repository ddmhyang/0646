<?php require_once __DIR__ . '/includes/db.php'; ?>
<?php
$isAdmin  = isset($_SESSION['admin']);
$isUser   = isset($_SESSION['user']);
$isGuest  = !$isAdmin && !$isUser;
$nickName = $isUser
    ? htmlspecialchars($_SESSION['user']['nickname'])
    : ($isAdmin ? htmlspecialchars($_SESSION['admin_nickname'] ?? $_SESSION['admin']) : null);

$mc_content    = $mysqli->query("SELECT content FROM home_pages WHERE slug='main_content' LIMIT 1")->fetch_assoc()['content'] ?? '';
$dday_date     = $mysqli->query("SELECT value FROM home_settings WHERE `key`='dday_date' LIMIT 1")->fetch_assoc()['value'] ?? '';
$profile_image = $mysqli->query("SELECT value FROM home_settings WHERE `key`='profile_image' LIMIT 1")->fetch_assoc()['value'] ?? '';

$secretWhere = $isGuest ? ' WHERE is_secret = 0' : '';
$m3_rows   = $mysqli->query("SELECT id,title,content,thumbnail,collapsed,is_secret FROM home_road_M3_posts{$secretWhere} ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$kuru_rows = $mysqli->query("SELECT id,title,content,thumbnail,collapsed,is_secret FROM home_road_KURU_posts{$secretWhere} ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메쿠루</title>
    <link rel="icon" href="assets/images/logo.jpeg">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div id="randomGlitchText" class="glitch-text-overlay">WAKE UP!</div>

    <div class="container">
        <div class="main_layout">

            <div class="xp-window window-1">
                <div class="xp-titlebar">
                    <div class="xp-title"></div>
                    <div class="xp-controls">
                        <button class="xp-control-btn minimize"><span>_</span></button>
                        <button class="xp-control-btn maximize"><span>□</span></button>
                        <button class="xp-control-btn close"><span>X</span></button>
                    </div>
                </div>
                <div class="xp-body">
                    <div class="wp1_date" id="dday-display" data-date="<?= htmlspecialchars($dday_date) ?>"></div>
                    <div class="wp1_profile" id="wp1_profile">
                        <?php if ($profile_image): ?><img src="<?= htmlspecialchars($profile_image) ?>" alt=""><?php endif; ?>
                    </div>
                    <div class="wp1_content post-content" id="wp1_content_view"><?= $mc_content ?></div>
                    <?php if ($isAdmin): ?>
                    <button class="xp-btn-small wp1_edit_btn" onclick="openEditOverlay()">수정</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="xp-window window-2">
                <div class="xp-titlebar">
                    <div class="xp-title"></div>
                    <div class="xp-controls">
                        <button class="xp-control-btn minimize"><span>_</span></button>
                        <button class="xp-control-btn maximize"><span>□</span></button>
                        <button class="xp-control-btn close"><span>X</span></button>
                    </div>
                </div>
                <div class="xp-body">
                    <div class="wp2_search">
                        <input type="text" class="search-input" id="search_q" placeholder="검색" onkeydown="if(event.key==='Enter')openSearchOverlay()">
                        <button class="search-btn" onclick="openSearchOverlay()"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    <div class="wp2_road" id="wp2_road">
                        <?php foreach ($m3_rows as $row):
                            $col = !empty($row['collapsed']); ?>
                        <div class="road_item rb-post" id="rb-post-M3-<?= $row['id'] ?>" data-id="<?= $row['id'] ?>" data-road="M3">
                            <div class="rb-post-header">
                                <div class="rb-post-left">
                                    <?php if (!empty($row['is_secret'])): ?><i class="fa-solid fa-lock" style="font-size:10px;opacity:0.6;margin-right:3px"></i><?php endif; ?>
                                    <span class="rb-post-no" data-title="<?= htmlspecialchars($row['title'] ?? '') ?>"><?= htmlspecialchars($row['title'] ?? '') ?></span>
                                    <button class="rb-collapse-btn" onclick="rbToggle(<?= $row['id'] ?>,'M3')"><i class="fa-solid <?= $col ? 'fa-angle-down' : 'fa-angle-up' ?>"></i></button>
                                </div>
                                <?php if ($isAdmin): ?>
                                <div class="rb-post-right">
                                    <button class="xp-btn-small" onclick="openRoadEdit(<?= $row['id'] ?>,'M3')">수정</button>
                                    <button class="xp-btn-small" onclick="rbDel(<?= $row['id'] ?>,'M3')">삭제</button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="rb-post-body" id="rb-body-M3-<?= $row['id'] ?>"<?= $col ? ' style="display:none"' : '' ?>>
                                <div class="rb-post-content"><?= $row['content'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($isAdmin): ?>
                    <div class="wp2_roadBar">
                        <button class="xp-btn-small" style="width:24px;padding:2px;" onclick="openRoadWrite('M3')">+</button>
                        <input type="text" class="wp2_input" id="m3_quick_title" placeholder="제목">
                        <button class="xp-btn-small" style="padding:2px 10px;" onclick="quickRoadSubmit('M3')">전송</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="xp-window window-3">
                <div class="xp-titlebar">
                    <div class="xp-title"></div>
                    <div class="xp-controls">
                        <button class="xp-control-btn minimize"><span>_</span></button>
                        <button class="xp-control-btn maximize"><span>□</span></button>
                        <button class="xp-control-btn close"><span>X</span></button>
                    </div>
                </div>
                <div class="xp-body">
                    <?php if ($isAdmin): ?>
                    <div class="kuru-admin-bar">
                        <button class="xp-btn-small" onclick="openRoadWrite('KURU')">+ 새 글</button>
                    </div>
                    <?php endif; ?>
                    <div class="wp3_road" id="wp3_road">
                    <?php foreach ($kuru_rows as $row): $col = !empty($row['collapsed']); ?>
                    <div class="road_item rb-post" id="rb-post-KURU-<?= $row['id'] ?>" data-id="<?= $row['id'] ?>" data-road="KURU">
                        <div class="rb-post-header">
                            <div class="rb-post-left">
                                <?php if (!empty($row['is_secret'])): ?><i class="fa-solid fa-lock" style="font-size:10px;opacity:0.6;margin-right:3px"></i><?php endif; ?>
                                <span class="rb-post-no" data-title="<?= htmlspecialchars($row['title'] ?? '') ?>"><?= htmlspecialchars($row['title'] ?? '') ?></span>
                                <button class="rb-collapse-btn" onclick="rbToggle(<?= $row['id'] ?>,'KURU')"><i class="fa-solid <?= $col ? 'fa-angle-down' : 'fa-angle-up' ?>"></i></button>
                            </div>
                            <?php if ($isAdmin): ?>
                            <div class="rb-post-right">
                                <button class="xp-btn-small" onclick="openRoadEdit(<?= $row['id'] ?>,'KURU')">수정</button>
                                <button class="xp-btn-small" onclick="rbDel(<?= $row['id'] ?>,'KURU')">삭제</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="rb-post-body" id="rb-body-KURU-<?= $row['id'] ?>"<?= $col ? ' style="display:none"' : '' ?>>
                            <div class="rb-post-content"><?= $row['content'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="sub_layout">
            <div class="xp-window window-4">
                <div class="xp-titlebar">
                    <div class="xp-title"></div>
                    <div class="xp-controls">
                        <button class="xp-control-btn minimize"><span>_</span></button>
                        <button class="xp-control-btn maximize"><span>□</span></button>
                        <button class="xp-control-btn close"><span>X</span></button>
                    </div>
                </div>
                <div class="xp-body" id="bgm-body"></div>
            </div>
        </div>

        <div class="xp-window window-5">
            <div class="xp-titlebar">
                <div class="xp-title"></div>
                <div class="xp-controls">
                    <button class="xp-control-btn minimize win5-minimize"><span>_</span></button>
                    <button class="xp-control-btn maximize win5-maximize"><span>□</span></button>
                    <button class="xp-control-btn close win5-close"><span>X</span></button>
                </div>
            </div>
            <div class="xp-body" id="guestbook-body">
                <div id="gb-list"></div>
                <div class="gb-form">
                    <textarea id="gb-content" placeholder="방명록을 남겨주세요..." maxlength="1000"></textarea>
                    <div class="gb-form-footer">
                        <button class="xp-btn-small" onclick="submitGuestbook()">남기기</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="overlay-edit" class="xp-overlay">
        <div class="xp-window overlay-window">
            <div class="xp-body overlay-body">
                <div class="profile-edit-row">
                    <div class="profile-preview" id="profile-preview">
                        <img id="profile-preview-img" src="<?= htmlspecialchars($profile_image) ?>" alt=""<?= $profile_image ? '' : ' style="display:none"' ?>>
                        <div class="profile-no-img" id="profile-no-img"<?= $profile_image ? ' style="display:none"' : '' ?>>No Image</div>
                    </div>
                    <label class="xp-btn-small" style="cursor:pointer;flex-shrink:0;">
                        이미지 변경<input type="file" id="inp_profile_img" accept="image/*" style="display:none">
                    </label>
                </div>
                <div style="flex:1;min-height:0;overflow:hidden;">
                    <div id="edit-summernote"></div>
                </div>
                <div class="overlay-footer">
                    <button class="xp-btn-small" onclick="saveMainContent()">저장</button>
                    <button class="xp-btn-small" onclick="closeEditOverlay()">취소</button>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay-search" class="xp-overlay">
        <div class="xp-window overlay-window">
            <div class="xp-body overlay-body">
                <div class="wp2_search" style="flex-shrink:0;">
                    <input type="text" class="search-input" id="overlay_search_q" placeholder="검색어를 입력하세요" onkeydown="if(event.key==='Enter')doSearch()">
                    <button class="search-btn" onclick="doSearch()"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <button class="xp-btn-small" onclick="closeSearchOverlay()" style="margin-left:6px;">✕</button>
                </div>
                <div id="search-results" style="flex:1;overflow-y:auto;padding:8px;"></div>
            </div>
        </div>
    </div>

    <div id="overlay-road" class="xp-overlay">
        <div class="xp-window overlay-window">
            <div class="xp-body overlay-body">
                <input type="hidden" id="road_id">
                <input type="hidden" id="road_type">
                <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;margin-bottom:6px;">
                    <input type="text" class="wp2_input" id="road_title" placeholder="제목" style="flex:1;">
                    <label style="font-size:11px;display:flex;align-items:center;gap:3px;color:var(--win-black);flex-shrink:0;">
                        <input type="checkbox" id="road_secret"> 비밀글
                    </label>
                </div>
                <div style="flex:1;min-height:0;overflow:hidden;">
                    <div id="road-summernote"></div>
                </div>
                <div class="overlay-footer">
                    <button class="xp-btn-small" onclick="saveRoadPost()">저장</button>
                    <button class="xp-btn-small" onclick="closeRoadOverlay()">취소</button>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay-login" class="xp-overlay">
        <div class="xp-window" style="width:260px;">
            <div class="xp-titlebar">
                <div class="xp-title"></div>
                <div class="xp-controls">
                    <button class="xp-control-btn close" onclick="closeLoginOverlay()"><span>X</span></button>
                </div>
            </div>
            <div class="xp-body" style="padding:12px;flex-direction:column;gap:6px;align-items:stretch;justify-content:flex-start;">
                <input type="text" id="login_user" placeholder="아이디" class="login-input" onkeydown="if(event.key==='Enter')doLogin()">
                <input type="password" id="login_pass" placeholder="비밀번호" class="login-input" onkeydown="if(event.key==='Enter')doLogin()">
                <p id="login-msg" style="font-size:10px;color:#c00;min-height:12px;"></p>
                <div style="display:flex;gap:6px;justify-content:flex-end;">
                    <button class="xp-btn-small" onclick="doLogin()">로그인</button>
                    <button class="xp-btn-small" onclick="closeLoginOverlay()">취소</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    var _isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
    var _nickName = <?= $nickName ? json_encode($nickName) : 'null' ?>;
    var _profileImage = <?= json_encode($profile_image) ?>;
    var _mcContent = <?= json_encode($mc_content) ?>;
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
    <script src="assets/js/main.js"></script>
    <div style="position:absolute;left:-9999px;top:-9999px;width:200px;height:200px;"><div id="bgm-yt-frame"></div></div>
</body>
</html>
