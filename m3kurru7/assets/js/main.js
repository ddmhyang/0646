$(function() {
    var _dispatch = $.event.dispatch;
    $.event.dispatch = function() {
        try { return _dispatch.apply(this, arguments); }
        catch(e) {
            if (e instanceof TypeError && e.message && e.message.indexOf("'top'") !== -1) return;
            throw e;
        }
    };
});

$(document).on('contextmenu dragstart', 'img', function(e) {
    if ($(this).closest('.note-editable').length) return;
    e.preventDefault(); return false;
});

var _snSelectedImg = null;
$(document).on('click mousedown', '.note-editable img', function() { _snSelectedImg = this; });

window.initSummernote = function(selector, extraOptions) {
    var imgLinkBtn = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="note-icon-link"></i>', tooltip: '이미지 링크',
            click: function() {
                var $img = $(_snSelectedImg);
                if (!$img.length || !$img.closest('.note-editable').length) return;
                var existing = $img.parent('a').attr('href') || '';
                var url = prompt('이미지 링크 URL:', existing);
                if (url === null) return;
                if (url.trim() === '') { $img.unwrap('a'); }
                else if ($img.parent('a').length) { $img.parent('a').attr('href', url.trim()).attr('target', '_blank'); }
                else { $img.wrap('<a href="' + url.trim() + '" target="_blank"></a>'); }
            }
        }).render();
    };

    var toggleBlockBtn = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="fa fa-chevron-right"></i>', tooltip: '접기 블록',
            click: function() {
                context.invoke('editor.pasteHTML', '<details><summary>펼치기</summary><div class="toggle-body"><p><br></p></div></details><p><br></p>');
            }
        }).render();
    };

    var applyIndent = function(context, dir) {
        var STEP = 0.5, s = window.getSelection(), $ed = context.layoutInfo.editable;
        var nearestBlock = function(node) {
            while (node && node !== $ed[0]) {
                if (node.parentNode === $ed[0]) return node;
                node = node.parentNode;
            }
            return null;
        };
        if (!s || !s.rangeCount) return;
        var range = s.getRangeAt(0);
        var sn = range.startContainer.nodeType === 3 ? range.startContainer.parentNode : range.startContainer;
        var en = range.endContainer.nodeType === 3 ? range.endContainer.parentNode : range.endContainer;
        var sb = nearestBlock(sn), eb = nearestBlock(en) || sb;
        if (!sb) return;
        var children = Array.from($ed[0].children);
        var si = children.indexOf(sb), ei = children.indexOf(eb);
        if (si > ei) { var tmp = si; si = ei; ei = tmp; }
        if (si < 0) si = 0; if (ei < 0) ei = si;
        for (var i = si; i <= ei; i++) {
            var cur = parseFloat(children[i].style.paddingLeft) || 0;
            var nxt = cur + dir * STEP;
            children[i].style.paddingLeft = nxt > 0 ? nxt + 'em' : '';
        }
    };

    var indentPara  = function(c) { return $.summernote.ui.button({ contents:'<i class="fa fa-indent"></i>',  tooltip:'들여쓰기 추가', click: function(){ applyIndent(c, 1);  } }).render(); };
    var outdentPara = function(c) { return $.summernote.ui.button({ contents:'<i class="fa fa-outdent"></i>', tooltip:'들여쓰기 제거', click: function(){ applyIndent(c, -1); } }).render(); };

    var letterSpacingBtn = function(context) {
        var values = ['normal','-0.05em','-0.02em','0.02em','0.05em','0.08em','0.1em','0.15em','0.2em','0.3em'];
        return $.summernote.ui.buttonGroup([
            $.summernote.ui.button({ className:'dropdown-toggle', contents:'LS<span class="note-icon-caret"></span>', tooltip:'자간', data:{ toggle:'dropdown' } }),
            $.summernote.ui.dropdown({
                items: values, template: function(v){ return v; },
                click: function(e) {
                    e.preventDefault();
                    var val = $(e.target).closest('[data-value]').data('value');
                    if (val === undefined) val = $(e.target).text().trim();
                    if (!val) return;
                    var s = window.getSelection();
                    if (!s || !s.rangeCount) return;
                    var range = s.getRangeAt(0);
                    if (range.collapsed) return;
                    try {
                        var frag = range.extractContents();
                        var span = document.createElement('span');
                        if (val !== 'normal') span.style.letterSpacing = val;
                        span.appendChild(frag); range.insertNode(span);
                        s.removeAllRanges();
                        var nr = document.createRange(); nr.setStartAfter(span); nr.collapse(true); s.addRange(nr);
                    } catch(err) {}
                }
            })
        ]).render();
    };

    var defaults = {
        lang: 'ko-KR', height: 300, disableDragAndDrop: true,
        callbacks: {
            onInit: function() {
                var $editor = $(this).closest('.note-editor');
                $editor.on('mousedown', '.note-para .note-dropdown-menu button, .note-para .dropdown-menu button', function(e){ e.preventDefault(); });
            },
            onPaste: function(e) {
                var cd = (e.originalEvent || e).clipboardData || window.clipboardData;
                var text = (cd.getData('Text') || cd.getData('text/plain') || '').trim();
                var m = text.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|shorts\/|v\/|.*[?&]v=))([\w-]{11})/);
                if (m && m[1]) {
                    e.preventDefault();
                    var editor = $(this);
                    setTimeout(function() {
                        var html = '<div style="width:50%;margin:0 auto 15px auto;"><div style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;"><iframe src="https://www.youtube.com/embed/' + m[1] + '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe></div></div>';
                        editor.summernote('insertNode', $(html)[0]);
                        editor.summernote('insertNode', $('<p><br></p>')[0]);
                    }, 10);
                }
            },
            onImageUpload: async function(files) {
                for (var i = 0; i < files.length; i++) {
                    var $ed = $(this);
                    var $load = $('<img src="assets/images/loading.png" alt="uploading">');
                    $ed.summernote('insertNode', $load[0]);
                    var file = await compressIfNeeded(files[i], 18);
                    var fd = new FormData(); fd.append('image', file);
                    try {
                        var data = await $.ajax({ url:'ajax_upload_image.php', method:'POST', data:fd, processData:false, contentType:false });
                        if (data.success) $load.attr('src', data.url).removeAttr('alt');
                        else { $load.remove(); alert('업로드 실패: ' + data.message); }
                    } catch(err) { $load.remove(); }
                }
            }
        },
        toolbar: [
            ['history',  ['undo','redo']],
            ['style',    ['style']],
            ['font',     ['bold','italic','underline','strikethrough','clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize','letterSpacing']],
            ['color',    ['color']],
            ['para',     ['paragraph','indentPara','outdentPara']],
            ['height',   ['height']],
            ['table',    ['table']],
            ['insert',   ['link','picture','hr','imgLink','toggleBlock']],
            ['code',     ['codeBlock']],
            ['view',     ['fullscreen','codeview']],
        ],
        styleTags: ['p','h1','h2','h3','blockquote','pre'],
        lineHeights: ['1.0','1.1','1.2','1.3','1.4','1.5','1.8','2.0','2.5','3.0'],
        fontSizes: ['4','5','6','7','8','9','10','11','12','13','14','16','18','20','24','28','32','36','40','44','48','52','56','60','68','80'],
        fontNames: ['Freesentation','Racing Sans One','Sekuya','Arial','Arial Black','Comic Sans MS','Courier New','Georgia','Impact','Tahoma','Times New Roman','Verdana'],
        fontNamesIgnoreCheck: ['Freesentation','Racing Sans One','Sekuya'],
        buttons: { imgLink: imgLinkBtn, toggleBlock: toggleBlockBtn, letterSpacing: letterSpacingBtn, indentPara: indentPara, outdentPara: outdentPara },
        popover: { image:[], link:[], air:[], table:[], media:[] },
    };
    $(selector).summernote($.extend(true, {}, defaults, extraOptions || {}));
};

function calcDday(dateStr) {
    var target = new Date(dateStr);
    var today  = new Date();
    today.setHours(0, 0, 0, 0);
    var diff = Math.floor((today - target) / 86400000);
    return diff === 0 ? 'D-Day' : diff > 0 ? 'D+' + diff : 'D' + diff;
}

async function compressIfNeeded(file, maxMB) {
    var maxBytes = maxMB * 1024 * 1024;
    if (file.size <= maxBytes) return file;
    return new Promise(function(resolve) {
        var img = new Image(), url = URL.createObjectURL(file);
        img.onload = function() {
            URL.revokeObjectURL(url);
            var ratio = Math.sqrt(maxBytes / file.size);
            var canvas = document.createElement('canvas');
            canvas.width  = Math.floor(img.width  * ratio);
            canvas.height = Math.floor(img.height * ratio);
            canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(function(blob) {
                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type:'image/jpeg' }));
            }, 'image/jpeg', 0.88);
        };
        img.src = url;
    });
}

function cropToSquare(file) {
    return new Promise(function(resolve) {
        var img = new Image(), url = URL.createObjectURL(file);
        img.onload = function() {
            URL.revokeObjectURL(url);
            var size = Math.min(img.width, img.height);
            var canvas = document.createElement('canvas');
            canvas.width = canvas.height = size;
            canvas.getContext('2d').drawImage(img, (img.width - size) / 2, (img.height - size) / 2, size, size, 0, 0, size, size);
            canvas.toBlob(function(blob) {
                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type:'image/jpeg' }));
            }, 'image/jpeg', 0.92);
        };
        img.src = url;
    });
}

function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

var bgmPlaylist = [], _bgmInited = false;

function fetchBgmPlaylist(callback) {
    $.getJSON('ajax_get_bgm_playlist.php', function(d) {
        var list = (d.items || []).map(function(t) { return { type:t.type, title:t.title, src:t.src }; });
        if (!list.length) list = [{ type:'youtube', title:'hoon - gum', src:'9K1ZW5WFq04' }];
        bgmPlaylist = list;
        if (callback) callback();
    }).fail(function() {
        bgmPlaylist = [{ type:'youtube', title:'hoon - gum', src:'9K1ZW5WFq04' }];
        if (callback) callback();
    });
}

function initBgmPlayer() {
    var playerHtml =
        '<button id="bgm-shuffle" title="셔플">⇄</button>' +
        '<button id="bgm-prev"    title="이전">◀◀</button>' +
        '<button id="bgm-play"    title="재생/멈춤">▶</button>' +
        '<button id="bgm-next"    title="다음">▶▶</button>' +
        '<button id="bgm-loop"    title="반복">↻</button>';
    $('#bgm-body').html('<div id="bgm-player">' + playerHtml + '</div>');

    var order = bgmPlaylist.map(function(_,i){ return i; });
    var curIdx = 0, isPlaying = false, shuffled = false, looped = false;
    var ytReady = false, ytPlayer = null, pendingPlay = false;

    var audio = new Audio();
    audio.addEventListener('ended', function() { nextTrack(); });

    function createYTPlayer() {
        ytPlayer = new YT.Player('bgm-yt-frame', {
            playerVars: { autoplay:0, controls:0, playsinline:1, disablekb:1 },
            events: {
                onReady: function() {
                    ytReady = true;
                    var t = currentTrack();
                    if (t.type === 'youtube') {
                        if (pendingPlay) { pendingPlay = false; ytPlayer.loadVideoById(t.src); }
                        else { ytPlayer.cueVideoById(t.src); }
                    }
                },
                onStateChange: function(e) { if (e.data === 0) nextTrack(); }
            }
        });
    }

    if (window.YT && window.YT.Player) {
        createYTPlayer();
    } else {
        window.onYouTubeIframeAPIReady = createYTPlayer;
        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
    }

    function currentTrack() { return bgmPlaylist[order[curIdx]]; }

    function loadTrack(play) {
        var t = currentTrack();
        audio.pause(); audio.src = '';
        if (ytReady && ytPlayer && typeof ytPlayer.stopVideo === 'function') ytPlayer.stopVideo();
        if (t.type === 'mp3') {
            audio.src = t.src;
            if (play) audio.play().catch(function(){});
        } else {
            if (ytReady && ytPlayer) {
                if (play) { ytPlayer.loadVideoById(t.src); }
                else      { ytPlayer.cueVideoById(t.src); }
            } else { pendingPlay = play; }
        }
        isPlaying = play;
        updateBtns();
    }

    function updateBtns() {
        $('#bgm-play').text(isPlaying ? '▮▮' : '▶');
        $('#bgm-shuffle').toggleClass('on', shuffled);
        $('#bgm-loop').toggleClass('on', looped);
    }

    function nextTrack() {
        if (looped) { loadTrack(true); return; }
        curIdx = (curIdx + 1) % order.length;
        loadTrack(true);
    }
    function prevTrack() {
        curIdx = (curIdx - 1 + order.length) % order.length;
        loadTrack(true);
    }

    $('#bgm-play').on('click', function() {
        var t = currentTrack();
        if (isPlaying) {
            if (t.type === 'mp3') audio.pause();
            else if (ytReady && ytPlayer) ytPlayer.pauseVideo();
            isPlaying = false;
        } else {
            if (t.type === 'mp3') audio.play().catch(function(){});
            else if (ytReady && ytPlayer) ytPlayer.playVideo();
            else pendingPlay = true;
            isPlaying = true;
        }
        updateBtns();
    });

    $('#bgm-prev').on('click', prevTrack);
    $('#bgm-next').on('click', nextTrack);

    $('#bgm-shuffle').on('click', function() {
        shuffled = !shuffled;
        var cur = order[curIdx];
        order = bgmPlaylist.map(function(_,i){ return i; });
        if (shuffled) {
            for (var i = order.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                var tmp = order[i]; order[i] = order[j]; order[j] = tmp;
            }
        }
        curIdx = order.indexOf(cur);
        updateBtns();
    });

    $('#bgm-loop').on('click', function() {
        looped = !looped;
        updateBtns();
    });

    $('body').one('click', function() {
        if (!isPlaying) {
            var t = currentTrack();
            if (t.type === 'mp3') { audio.play().catch(function(){}); }
            else if (ytReady && ytPlayer) { ytPlayer.playVideo(); }
            else { pendingPlay = true; }
            isPlaying = true;
            updateBtns();
        }
    });

    loadTrack(false);

    window.bgmReloadPlaylist = function() {
        fetchBgmPlaylist(function() {
            order = bgmPlaylist.map(function(_,i){ return i; });
            curIdx = 0; loadTrack(false);
        });
    };
}

window.rbToggle = function(id, road) {
    var $body = $('#rb-body-' + road + '-' + id);
    var $btn  = $('#rb-post-' + road + '-' + id + ' .rb-collapse-btn');
    var hidden = $body.is(':hidden');
    $body.toggle();
    $btn.html(hidden ? '<i class="fa-solid fa-angle-up"></i>' : '<i class="fa-solid fa-angle-down"></i>');
    if (_isAdmin) $.post('ajax_toggle_road_collapse.php', { road:road, id:id, collapsed:hidden ? 0 : 1 });
};

window.rbDel = function(id, road) {
    if (!confirm('글을 삭제하시겠습니까?')) return;
    $.post('ajax_delete_road.php', { road:road, id:id }).done(function() { $('#rb-post-' + road + '-' + id).remove(); });
};

var _editSnInited = false, _pendingProfileUrl = null;

window.openEditOverlay = function() {
    if (!_editSnInited) {
        initSummernote('#edit-summernote', { height: 200 });
        _editSnInited = true;
    }
    $('#edit-summernote').summernote('code', _mcContent);
    _pendingProfileUrl = null;
    $('#overlay-edit').addClass('active');
};

window.closeEditOverlay = function() {
    $('#overlay-edit').removeClass('active');
    _pendingProfileUrl = null;
};

$('#inp_profile_img').on('change', async function() {
    var file = this.files[0]; if (!file) return;
    var cropped = await cropToSquare(file);
    var previewUrl = URL.createObjectURL(cropped);
    $('#profile-preview-img').attr('src', previewUrl).show();
    $('#profile-no-img').hide();
    _pendingProfileUrl = { file: cropped, previewUrl: previewUrl };
});

window.saveMainContent = function() {
    var content = $('#edit-summernote').summernote('code');

    function doSave(profileUrl) {
        var data = { content: content };
        if (profileUrl !== null) data.profile_image = profileUrl;
        $.post('ajax_save_profile.php', data).done(function(d) {
            if (d.success) {
                _mcContent = content;
                $('#wp1_content_view').html(content);
                if (profileUrl) {
                    $('#wp1_profile').html('<img src="' + profileUrl + '" alt="">');
                    _profileImage = profileUrl;
                }
                closeEditOverlay();
            } else alert(d.message || '저장 실패');
        });
    }

    if (_pendingProfileUrl) {
        var fd = new FormData(); fd.append('image', _pendingProfileUrl.file);
        $.ajax({ url:'ajax_upload_image.php', type:'POST', data:fd, processData:false, contentType:false })
         .done(function(d) {
             if (d.success) doSave(d.url);
             else alert('이미지 업로드 실패: ' + d.message);
         });
    } else {
        doSave(null);
    }
};

window.openSearchOverlay = function() {
    var q = $('#search_q').val().trim();
    $('#overlay_search_q').val(q);
    $('#search-results').empty();
    $('#overlay-search').addClass('active');
    if (q) doSearch();
};

window.closeSearchOverlay = function() { $('#overlay-search').removeClass('active'); };

window.doSearch = function() {
    var q = $('#overlay_search_q').val().trim();
    if (!q) return;
    $('#search-results').html('<div style="padding:10px;font-size:11px;">검색 중...</div>');
    $.getJSON('search.php', { q: q }, function(data) {
        var $res = $('#search-results').empty();
        if (!data.results || !data.results.length) {
            $res.html('<div style="padding:10px;font-size:11px;">검색 결과가 없습니다.</div>');
            return;
        }
        data.results.forEach(function(r) {
            $res.append('<div class="search-result-item" data-road="'+r.road+'" data-id="'+r.id+'"><div class="res-title">'+escH(r.title)+'</div><div class="res-snippet">'+escH(r.snippet||'')+'</div></div>');
        });
    }).fail(function() { $('#search-results').html('<div style="padding:10px;font-size:11px;color:#c00;">검색 오류</div>'); });
};

$(document).on('click', '.search-result-item', function() {
    var road = $(this).data('road'), id = $(this).data('id');
    closeSearchOverlay();
    var $target = $('#rb-post-' + road + '-' + id);
    if (!$target.length) return;
    if (road === 'KURU' && window.innerWidth <= 900) {
        document.querySelector('.container').classList.add('show-win3');
        document.querySelector('.window-3').classList.add('z-top');
        document.querySelector('.sub_layout').classList.remove('z-top');
    }
    setTimeout(function() { $target[0].scrollIntoView({ behavior:'smooth', block:'center' }); }, 150);
});

var _roadSnInited = false;

function openRoadOverlay() {
    if (!_roadSnInited) {
        initSummernote('#road-summernote', { height: 280 });
        _roadSnInited = true;
    }
    $('#overlay-road').addClass('active');
}

window.openRoadWrite = function(road) {
    $('#road_id').val(''); $('#road_type').val(road);
    $('#road_title').val(''); $('#road_secret').prop('checked', false);
    openRoadOverlay();
    $('#road-summernote').summernote('code', '');
};

window.openRoadEdit = function(id, road) {
    var $post = $('#rb-post-' + road + '-' + id);
    var title    = $post.find('.rb-post-no').first().data('title') || '';
    var content  = $post.find('.rb-post-content').first().html() || '';
    var isSecret = $post.find('> .rb-post-header .fa-lock').length > 0 ||
                   $post.find('> .gallery_info .fa-lock').length > 0;
    $('#road_id').val(id); $('#road_type').val(road);
    $('#road_title').val(title); $('#road_secret').prop('checked', isSecret);
    openRoadOverlay();
    $('#road-summernote').summernote('code', content);
};

window.closeRoadOverlay = function() { $('#overlay-road').removeClass('active'); };

function buildRoadItem(d) {
    var id = d.id, road = d.road, title = escH(d.title), content = d.content, secret = d.is_secret;
    var lockIcon = secret ? '<i class="fa-solid fa-lock" style="font-size:10px;opacity:0.6;margin-right:3px"></i>' : '';
    var adminBtns = _isAdmin
        ? '<div class="rb-post-right">' +
          '<button class="xp-btn-small" onclick="openRoadEdit(' + id + ',\'' + road + '\')">수정</button>' +
          '<button class="xp-btn-small" onclick="rbDel(' + id + ',\'' + road + '\')">삭제</button>' +
          '</div>' : '';
    return '<div class="road_item rb-post" id="rb-post-' + road + '-' + id + '" data-id="' + id + '" data-road="' + road + '">' +
        '<div class="rb-post-header">' +
          '<div class="rb-post-left">' + lockIcon +
            '<span class="rb-post-no" data-title="' + title + '">' + title + '</span>' +
            '<button class="rb-collapse-btn" onclick="rbToggle(' + id + ',\'' + road + '\')"><i class="fa-solid fa-angle-up"></i></button>' +
          '</div>' + adminBtns +
        '</div>' +
        '<div class="rb-post-body" id="rb-body-' + road + '-' + id + '">' +
          '<div class="rb-post-content">' + content + '</div>' +
        '</div>' +
        '</div>';
}

window.saveRoadPost = function() {
    var id = $('#road_id').val(), road = $('#road_type').val();
    var title = $('#road_title').val().trim();
    var content = $('#road-summernote').summernote('code');
    var isSecret = $('#road_secret').is(':checked') ? 1 : 0;
    if (!title) { alert('제목을 입력하세요.'); return; }
    var postData = { road:road, title:title, content:content, is_secret:isSecret };
    if (id) postData.id = id;
    $.post('ajax_save_road.php', postData).done(function(d) {
        if (!d.success) { alert(d.message || '저장 실패'); return; }
        closeRoadOverlay();
        var feed = road === 'M3' ? '#wp2_road' : '#wp3_road';
        if (d.is_edit) {
            var $item = $('#rb-post-' + road + '-' + d.id);
            $item.replaceWith(buildRoadItem(d));
        } else {
            $(feed).prepend(buildRoadItem(d));
        }
    });
};

window.quickRoadSubmit = function(road) {
    var title = $('#' + road.toLowerCase() + '_quick_title').val().trim();
    if (!title) return;
    $.post('ajax_save_road.php', { road:road, title:title, content:'<p>' + escH(title) + '</p>', is_secret:0 }).done(function(d) {
        if (!d.success) { alert(d.message || '저장 실패'); return; }
        var feed = road === 'M3' ? '#wp2_road' : '#wp3_road';
        $(feed).prepend(buildRoadItem(d));
        $('#' + road.toLowerCase() + '_quick_title').val('');
    });
};

/* ── 방명록 ── */
function loadGuestbook() {
    $.getJSON('ajax_get_guestbook.php', function(d) {
        var $list = $('#gb-list').empty();
        if (!d.items || !d.items.length) {
            $list.html('<div style="padding:10px;font-size:11px;opacity:0.5;">방명록이 없습니다.</div>');
            return;
        }
        d.items.forEach(function(item) {
            var date = (item.created_at || '').substring(0, 10);
            var adminClass = (item.is_admin == 1) ? ' gb-entry--admin' : '';
            $list.append(
                '<div class="gb-entry' + adminClass + '" id="gb-entry-' + item.id + '" data-id="' + item.id + '">' +
                escH(item.content) +
                '<div class="gb-entry-date">' + escH(date) + '</div>' +
                '</div>'
            );
        });
    });
}

// 관리자 전용: 꾹 누르기(600ms) / 우클릭으로 삭제
if (_isAdmin) {
    var _gbHoldTimer = null;

    function _gbStartHold(id, cancelEvent, threshold) {
        function cancel() {
            if (_gbHoldTimer) { clearTimeout(_gbHoldTimer); _gbHoldTimer = null; }
            $(document).off(cancelEvent + '.gbhold');
        }
        $(document).on(cancelEvent + '.gbhold', function(ev) {
            if (!threshold) { cancel(); return; }
            var dx = (ev.clientX !== undefined ? ev.clientX : (ev.originalEvent.touches && ev.originalEvent.touches[0] ? ev.originalEvent.touches[0].clientX : 0));
            cancel();
        });
        _gbHoldTimer = setTimeout(function() {
            cancel();
            if (confirm('이 방명록을 삭제하시겠습니까?')) deleteGuestbook(id);
        }, 600);
    }

    // 우클릭
    $(document).on('contextmenu', '.gb-entry', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('이 방명록을 삭제하시겠습니까?')) deleteGuestbook(id);
    });

    // 마우스 꾹 누르기
    $(document).on('mousedown', '.gb-entry', function(e) {
        if (e.button !== 0) return; // 좌클릭만
        var id = $(this).data('id');
        function cancel() {
            if (_gbHoldTimer) { clearTimeout(_gbHoldTimer); _gbHoldTimer = null; }
            $(document).off('mouseup.gbhold mousemove.gbhold');
        }
        $(document).on('mouseup.gbhold mousemove.gbhold', cancel);
        _gbHoldTimer = setTimeout(function() {
            cancel();
            if (confirm('이 방명록을 삭제하시겠습니까?')) deleteGuestbook(id);
        }, 600);
    });

    // 터치 꾹 누르기
    $(document).on('touchstart', '.gb-entry', function(e) {
        var id = $(this).data('id');
        var touch = e.originalEvent.touches[0];
        var startX = touch.clientX, startY = touch.clientY;
        function cancel() {
            if (_gbHoldTimer) { clearTimeout(_gbHoldTimer); _gbHoldTimer = null; }
            $(document).off('touchmove.gbhold touchend.gbhold');
        }
        $(document).on('touchmove.gbhold', function(ev) {
            var t = ev.originalEvent.touches[0];
            if (Math.abs(t.clientX - startX) > 10 || Math.abs(t.clientY - startY) > 10) cancel();
        });
        $(document).on('touchend.gbhold', cancel);
        _gbHoldTimer = setTimeout(function() {
            cancel();
            if (confirm('이 방명록을 삭제하시겠습니까?')) deleteGuestbook(id);
        }, 600);
    });
}

window.submitGuestbook = function() {
    var content = $('#gb-content').val().trim();
    if (!content) { alert('내용을 입력해주세요.'); return; }
    $.post('ajax_save_guestbook.php', { content: content }).done(function(d) {
        if (!d.success) { alert(d.message || '저장 실패'); return; }
        $('#gb-content').val('');
        loadGuestbook();
    });
};

window.deleteGuestbook = function(id) {
    if (!confirm('삭제하시겠습니까?')) return;
    $.post('ajax_delete_guestbook.php', { id: id }).done(function(d) {
        if (d.success) $('#gb-entry-' + id).remove();
    });
};

window.openLoginOverlay  = function() { $('#overlay-login').addClass('active'); };
window.closeLoginOverlay = function() { $('#overlay-login').removeClass('active'); $('#login-msg').text(''); };

window.doLogin = function() {
    var username = $('#login_user').val().trim(), password = $('#login_pass').val();
    if (!username || !password) { $('#login-msg').text('아이디와 비밀번호를 입력하세요.'); return; }
    $.post('ajax_login.php', { username:username, password:password }).done(function(d) {
        if (d.success) location.reload();
        else $('#login-msg').text(d.message);
    });
};

$(function() {
    var container  = document.querySelector('.container');
    var win3       = document.querySelector('.window-3');
    var win5       = document.querySelector('.window-5');
    var subLayout  = document.querySelector('.sub_layout');

    // 1, 2번 minimize: 로그인/로그아웃 (데스크톱+모바일 공통)
    document.querySelectorAll('.window-1 .minimize, .window-2 .minimize').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (_isAdmin || _nickName) {
                if (confirm('로그아웃 하시겠습니까?')) {
                    $.post('ajax_logout.php').done(function() { location.reload(); });
                }
            } else {
                openLoginOverlay();
            }
        });
    });

    // 모바일 전용: 3,4,5번 어느 창이든 ㅡ → 3,4,5 모두 숨김
    document.querySelectorAll('.window-3 .minimize, .window-4 .minimize, .window-5 .minimize').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (window.innerWidth > 900) return;
            container.classList.remove('show-win3', 'show-win4', 'show-win5');
            win3.classList.remove('z-top');
            subLayout.classList.remove('z-top');
        });
    });

    // 모바일 전용: 3,4,5번 어느 창이든 ㅁ → win3 맨 위로
    document.querySelectorAll('.maximize').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (window.innerWidth > 900) return;
            container.classList.add('show-win3');
            win3.classList.add('z-top');
            subLayout.classList.remove('z-top');
        });
    });

    // 모바일 전용: 3,4,5번 어느 창이든 X → win4+5 맨 위로
    document.querySelectorAll('.close').forEach(function(btn) {
        if ($(btn).closest('#overlay-login').length) return;
        btn.addEventListener('click', function() {
            if (window.innerWidth > 900) return;
            container.classList.add('show-win4', 'show-win5');
            subLayout.classList.add('z-top');
            win3.classList.remove('z-top');
        });
    });

    var _zWin = 10;
    [
        ['.window-1', '.window-1'],
        ['.window-2', '.window-2'],
        ['.window-3', '.window-3'],
        ['.window-4', '.sub_layout'],
        ['.window-5', '.window-5']
    ].forEach(function(pair) {
        var trigger = document.querySelector(pair[0]);
        var target  = document.querySelector(pair[1]);
        if (!trigger || !target) return;
        trigger.addEventListener('mousedown', function() {
            if (window.innerWidth <= 900) return;
            _zWin++;
            target.classList.remove('z-top');
            target.style.zIndex = _zWin;
        });
    });

    var phrases = ['Wake Up!','Who am I?','I Love You.','I See You.','friend?','Welcome!'];
    var textEl  = document.getElementById('randomGlitchText');
    function changeText() { textEl.innerText = phrases[Math.floor(Math.random() * phrases.length)]; }
    changeText(); setInterval(changeText, 20000);

    var $dday = $('#dday-display');
    if ($dday.length) $dday.text(calcDday($dday.data('date')));

    fetchBgmPlaylist(function() { if (!_bgmInited) { _bgmInited = true; initBgmPlayer(); } });

    loadGuestbook();
});
