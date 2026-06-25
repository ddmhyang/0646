$(function() {
    var _dispatch = $.event.dispatch;
    $.event.dispatch = function() {
        try {
            return _dispatch.apply(this, arguments);
        } catch(e) {
            if (e instanceof TypeError && e.message && e.message.indexOf("'top'") !== -1) return;
            throw e;
        }
    };
});

$(document).on('contextmenu dragstart', 'img', function(e) {
    if ($(this).closest('.note-editable').length) return;
    e.preventDefault();
    return false;
});

var _snSelectedImg = null;
$(document).on('click', '.note-editable img', function() { _snSelectedImg = this; });

window.initSummernote = function(selector, extraOptions) {
    var imgLinkBtn = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="note-icon-link"></i>',
            tooltip: '이미지 링크',
            click: function() {
                var $img = $(_snSelectedImg);
                if (!$img.length || !$img.closest('.note-editable').length) return;
                var existing = $img.parent('a').attr('href') || '';
                var url = prompt('이미지 링크 URL:', existing);
                if (url === null) return;
                if (url.trim() === '') {
                    $img.unwrap('a');
                } else if ($img.parent('a').length) {
                    $img.parent('a').attr('href', url.trim()).attr('target', '_blank');
                } else {
                    $img.wrap('<a href="' + url.trim() + '" target="_blank"></a>');
                }
            }
        }).render();
    };

    var toggleBlockBtn = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="fa fa-chevron-right"></i>',
            tooltip: '접기 블록',
            click: function() {
                var html = '<details><summary>펼치기</summary><div class="toggle-body"><p><br></p></div></details><p><br></p>';
                context.invoke('editor.pasteHTML', html);
            }
        }).render();
    };

    var applyIndent = function(context, dir) {
        var STEP = 0.5;
        var s = window.getSelection();
        var $ed = context.layoutInfo.editable;
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
        var sb = nearestBlock(sn);
        var eb = nearestBlock(en) || sb;
        if (!sb) return;
        var children = Array.from($ed[0].children);
        var si = children.indexOf(sb), ei = children.indexOf(eb);
        if (si > ei) { var tmp = si; si = ei; ei = tmp; }
        if (si < 0) si = 0;
        if (ei < 0) ei = si;
        for (var i = si; i <= ei; i++) {
            var cur = parseFloat(children[i].style.paddingLeft) || 0;
            var nxt = cur + dir * STEP;
            if (nxt < 0) nxt = 0;
            children[i].style.paddingLeft = nxt > 0 ? nxt + 'em' : '';
        }
    };

    var indentPara = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="fa fa-indent"></i>',
            tooltip: '들여쓰기 추가',
            click: function() { applyIndent(context, 1); }
        }).render();
    };

    var outdentPara = function(context) {
        return $.summernote.ui.button({
            contents: '<i class="fa fa-outdent"></i>',
            tooltip: '들여쓰기 제거',
            click: function() { applyIndent(context, -1); }
        }).render();
    };

    var letterSpacingBtn = function(context) {
        var values = ['normal', '-0.05em', '-0.02em', '0.02em', '0.05em', '0.08em', '0.1em', '0.15em', '0.2em', '0.3em'];
        return $.summernote.ui.buttonGroup([
            $.summernote.ui.button({
                className: 'dropdown-toggle',
                contents: 'LS<span class="note-icon-caret"></span>',
                tooltip: '자간 (Letter Spacing)',
                data: { toggle: 'dropdown' }
            }),
            $.summernote.ui.dropdown({
                items: values,
                template: function(v) { return v; },
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
                        span.appendChild(frag);
                        range.insertNode(span);
                        s.removeAllRanges();
                        var nr = document.createRange();
                        nr.setStartAfter(span);
                        nr.collapse(true);
                        s.addRange(nr);
                    } catch(err) {}
                }
            })
        ]).render();
    };

    var defaults = {
        lang: 'ko-KR',
        height: 400,
        disableDragAndDrop: true,
        callbacks: {
            onPaste: function(e) {
                var cd = (e.originalEvent || e).clipboardData || window.clipboardData;
                var text = (cd.getData('Text') || cd.getData('text/plain') || '').trim();
                var m = text.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|shorts\/|v\/|.*[?&]v=))([\w-]{11})/);
                if (m && m[1]) {
                    e.preventDefault();
                    var editor = $(this);
                    setTimeout(function() {
                        var html = '<div style="width:50%;margin:0 auto 15px auto;">' +
                            '<div style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;">' +
                            '<iframe src="https://www.youtube.com/embed/' + m[1] + '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>' +
                            '</div></div>';
                        editor.summernote('insertNode', $(html)[0]);
                        editor.summernote('insertNode', $('<p><br></p>')[0]);
                    }, 10);
                }
            },
            onImageUpload: async function(files) {
                for (var i = 0; i < files.length; i++) {
                    var $ed   = $(this);
                    var $load = $('<img src="assets/images/loading.png" alt="uploading">');
                    $ed.summernote('insertNode', $load[0]);
                    var file = await compressIfNeeded(files[i], 18);
                    var fd = new FormData();
                    fd.append('image', file);
                    try {
                        var data = await $.ajax({ url: 'ajax_upload_image.php', method: 'POST', data: fd, processData: false, contentType: false });
                        if (data.success) $load.attr('src', data.url).removeAttr('alt');
                        else { $load.remove(); alert('업로드 실패: ' + data.message); }
                    } catch(e) { $load.remove(); }
                }
            }
        },
        toolbar: [
            ['history',  ['undo', 'redo']],
            ['style',    ['style']],
            ['font',     ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize', 'letterSpacing']],
            ['color',    ['color']],
            ['para',     ['paragraph', 'indentPara', 'outdentPara']],
            ['height',   ['height']],
            ['table',    ['table']],
            ['insert',   ['link', 'picture', 'hr', 'imgLink', 'toggleBlock']],
            ['code',     ['codeBlock']],
            ['view',     ['fullscreen', 'codeview']],
        ],
        styleTags: ['p', 'h1', 'h2', 'h3', 'blockquote', 'pre'],
        lineHeights: ['1.0', '1.1', '1.2', '1.3', '1.4', '1.5', '1.8', '2.0', '2.5', '3.0'],
        fontSizes: ['4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '16', '18', '20', '24', '28', '32', '36', '40', '44', '48', '52', '56', '60', '68', '80'],
        fontNames: [
            'Freesentation',
            'Racing Sans One',
            'Sekuya',
            'Arial', 'Arial Black', 'Comic Sans MS',
            'Courier New', 'Georgia', 'Impact',
            'Tahoma', 'Times New Roman', 'Verdana'
        ],
        fontNamesIgnoreCheck: ['Freesentation', 'Racing Sans One', 'Sekuya'],
        buttons: { imgLink: imgLinkBtn, toggleBlock: toggleBlockBtn, letterSpacing: letterSpacingBtn, indentPara: indentPara, outdentPara: outdentPara },
        popover: { image: [], link: [], air: [], table: [], media: [] },
    };
    $(selector).summernote($.extend(true, {}, defaults, extraOptions || {}));
};
function adjustLayout() {
    var vvp = window.visualViewport;
    var safeBottom = (vvp) ? Math.max(0, Math.round(window.innerHeight - vvp.height - (vvp.offsetTop || 0))) : 0;
    document.documentElement.style.setProperty('--safe-bottom', safeBottom + 'px');
    document.documentElement.style.setProperty('--real-vh', '100dvh');
}
window.addEventListener('resize', adjustLayout);
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', adjustLayout);
}
var $globalFooter = null;

$(function() {
    if ($('.site-footer').length) {
        $globalFooter = $('.site-footer').detach();
    }

    adjustLayout();
    loadRoute(location.hash || '#/');
    $(window).on('hashchange', function() { loadRoute(location.hash); });

    $(document).on('click', '#btn_logout', function() {
        $.post('ajax_logout.php').done(function() { location.reload(); });
    });
});

function loadRoute(hash) {
    var path = (hash || '').replace(/^#\/?/, '');
    var url  = routeToUrl(path);
    $('#content').html('<div class="loading">로딩 중...</div>');
    $.get(url)
     .done(function(html) {
         $('#content').html(html);
         if ($globalFooter && $globalFooter.length) {
             $('#content').append($globalFooter);
         }
         if (typeof hljs !== 'undefined') hljs.highlightAll();
     })
     .fail(function() { $('#content').html('<p>페이지를 불러올 수 없습니다.</p>'); });

    var base = path.split('/')[0] || '';
    $('.nav-item').removeClass('active');
    var navBase = (base.indexOf('gallery_') === 0) ? 'gallery_Kitsch' : base;
    var $nav = $('#nav_' + navBase);
    if (!$nav.length) $nav = $('#nav_main');
    $nav.addClass('active');
}

function routeToUrl(path) {
    if (!path || path === 'main') return 'main_content.php';
    var parts = path.split('/');
    var page = parts[0], sub = parts[1], id = encodeURIComponent(parts[2] || '');
    if (sub === 'detail') return page + '_detail.php?id=' + id;
    if (sub === 'upload') return page + '_upload.php';
    if (sub === 'edit')   return page + '_edit.php?id=' + id;
    if (sub && /^\d+$/.test(sub)) return page + '.php?page=' + sub;
    return page + '.php';
}

function loadComments(type, name, targetId) {
    var params = { type: type, name: name, id: targetId || 0 };
    $.get('ajax_get_comments.php', params, function(data) {
        var $list = $('#comment-list');
        $list.empty();
        data.items.forEach(function(c) {
            var del = data.is_admin
                ? '<button class="btn-danger" style="font-size:11px;padding:2px 8px" onclick="delComment('+c.id+',\''+type+'\',\''+name+'\','+targetId+')">삭제</button>'
                : '';
            $list.append('<div class="comment-item"><div class="comment-author">'+escHtml(c.author)+' · '+c.created_at+del+'</div><div>'+escHtml(c.content)+'</div></div>');
        });
    });
}
function submitComment(type, name, targetId) {
    var author  = $('#cmt_author').val().trim();
    var content = $('#cmt_content').val().trim();
    if (!author || !content) { alert('이름과 내용을 입력하세요.'); return; }
    $.post('ajax_save_comment.php', { type: type, name: name, id: targetId || 0, author: author, content: content })
     .done(function(d) {
         if (d.success) { $('#cmt_author').val(''); $('#cmt_content').val(''); loadComments(type, name, targetId); }
         else alert(d.message);
     });
}
function delComment(id, type, name, targetId) {
    if (!confirm('댓글을 삭제하시겠습니까?')) return;
    $.post('ajax_delete_comment.php', { id: id })
     .done(function() { loadComments(type, name, targetId); });
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function saveListItem(id, slug) {
    var title    = $('#li_title').val().trim();
    var subtitle = $('#li_subtitle').val().trim();
    if (!title) { alert('제목을 입력하세요.'); return; }
    var fd = new FormData();
    if (id) fd.append('id', id);
    fd.append('title', title);
    fd.append('subtitle', subtitle);
    var file = $('#li_image')[0].files[0];
    if (!id && !file) { alert('이미지를 등록해주세요.'); return; }
    if (file) fd.append('image', file);
    $.ajax({
        url: 'ajax_save_' + slug + '.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false
    }).done(function(d) {
        if (d.success) location.hash = '#/' + slug;
        else alert(d.message || '저장 실패');
    });
}

function saveGoodsItem(id, slug) {
    var title = $('#gd_title').val().trim();
    var link  = $('#gd_link').val().trim();
    if (!title) { alert('제목을 입력하세요.'); return; }
    var fd = new FormData();
    if (id) fd.append('id', id);
    fd.append('title', title);
    fd.append('link', link);
    var file = $('#gd_image')[0].files[0];
    if (!id && !file) { alert('이미지를 등록해주세요.'); return; }
    if (file) fd.append('image', file);
    $.ajax({
        url: 'ajax_save_' + slug + '.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false
    }).done(function(d) {
        if (d.success) location.hash = '#/' + slug;
        else alert(d.message || '저장 실패');
    });
}

async function compressIfNeeded(file, maxMB) {
    var maxBytes = maxMB * 1024 * 1024;
    if (file.size <= maxBytes) return file;
    return new Promise(function(resolve) {
        var img = new Image();
        var url = URL.createObjectURL(file);
        img.onload = function() {
            URL.revokeObjectURL(url);
            var ratio = Math.sqrt(maxBytes / file.size);
            var canvas = document.createElement('canvas');
            canvas.width  = Math.floor(img.width  * ratio);
            canvas.height = Math.floor(img.height * ratio);
            canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(function(blob) {
                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }));
            }, 'image/jpeg', 0.88);
        };
        img.src = url;
    });
}
var bgmPlaylist = [];
var _bgmInited  = false;

function fetchBgmPlaylist(callback) {
    $.getJSON('ajax_get_bgm_playlist.php', function(d) {
        var list = (d.items || []).map(function(t) { return { type: t.type, title: t.title, src: t.src }; });
        if (!list.length) list = [{ type: 'youtube', title: '', src: '' }];
        bgmPlaylist = list;
        if (callback) callback();
    }).fail(function() {
        bgmPlaylist = [{ type: 'youtube', title: '', src: '' }];
        if (callback) callback();
    });
}

function initBgmPlayer() {
    var playerHtml =
        '<div id="bgm-wrap">' +
        '<div id="bgm-playlist"></div>' +
        '<div id="bgm-player">' +
        '<button id="bgm-list"    title="목록">&#9776;</button>' +
        '<button id="bgm-shuffle" title="셔플">&#8644;</button>' +
        '<button id="bgm-prev"    title="이전">&#9664;&#9664;</button>' +
        '<button id="bgm-play"    title="재생/멈춤">&#9654;</button>' +
        '<button id="bgm-next"    title="다음">&#9654;&#9654;</button>' +
        '<span   id="bgm-title">-</span>' +
        '</div>' +
        '</div>';
    $('.container').append(playerHtml);

    var order     = bgmPlaylist.map(function(_,i){ return i; });
    var curIdx    = 0;
    var isPlaying = false;
    var shuffled  = false;
    var isOneShot = false;  // 기본(0번) 외 트랙을 클릭했을 때 true → 끝나면 0번으로 복귀
    var ytReady   = false;
    var ytPlayer  = null;
    var pendingPlay = false;

    var audio = new Audio();
    audio.addEventListener('ended', nextTrack);

    function createYTPlayer() {
        ytPlayer = new YT.Player('bgm-yt-frame', {
            playerVars: { autoplay: 0, controls: 0, playsinline: 1, disablekb: 1 },
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
        $('#bgm-title').text(t.title);
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
        updateBtn();
        renderPlaylist();
    }

    function updateBtn() {
        $('#bgm-play').html(isPlaying ? '&#9646;&#9646;' : '&#9654;');
        $('#bgm-shuffle').toggleClass('on', shuffled);
    }

    function nextTrack() {
        if (isOneShot) {
            // 게스트 트랙 종료 → 0번 트랙으로 복귀해서 루프
            isOneShot = false;
            curIdx = order.indexOf(0);
            if (curIdx < 0) curIdx = 0;
        } else {
            curIdx = (curIdx + 1) % order.length;
        }
        loadTrack(true);
    }
    function prevTrack() {
        isOneShot = false;
        curIdx = (curIdx - 1 + order.length) % order.length;
        loadTrack(true);
    }

    function renderPlaylist() {
        var $list = $('#bgm-playlist').empty();
        bgmPlaylist.forEach(function(t, realIdx) {
            var $item = $('<div class="bgm-pl-item"></div>').text(t.title);
            if (order[curIdx] === realIdx) $item.addClass('active');
            $item.on('click', function() {
                var ordIdx = order.indexOf(realIdx);
                if (ordIdx < 0) { order.push(realIdx); ordIdx = order.length - 1; }
                isOneShot = (realIdx !== 0);
                curIdx = ordIdx;
                loadTrack(true);
            });
            $list.append($item);
        });
    }

    $('#bgm-list').on('click', function() {
        var $pl = $('#bgm-playlist');
        if ($pl.is(':visible')) { $pl.hide(); }
        else { renderPlaylist(); $pl.show(); }
    });

    $('#bgm-play').on('click', function() {
        if (isPlaying) {
            var t = currentTrack();
            if (t.type === 'mp3') audio.pause();
            else if (ytReady && ytPlayer) ytPlayer.pauseVideo();
            isPlaying = false;
        } else {
            var t = currentTrack();
            if (t.type === 'mp3') audio.play().catch(function(){});
            else if (ytReady && ytPlayer) ytPlayer.playVideo();
            else pendingPlay = true;
            isPlaying = true;
        }
        updateBtn();
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
        updateBtn();
    });

    // 브라우저 autoplay 정책 대응: 첫 클릭 시 자동 재생 시작
    $('body').one('click', function() {
        if (!isPlaying) {
            var t = currentTrack();
            if (t.type === 'mp3') { audio.play().catch(function(){}); }
            else if (ytReady && ytPlayer) { ytPlayer.playVideo(); }
            else { pendingPlay = true; }
            isPlaying = true;
            updateBtn();
        }
    });

    loadTrack(false);

    window.bgmReloadPlaylist = function() {
        fetchBgmPlaylist(function() {
            order = bgmPlaylist.map(function(_,i){ return i; });
            curIdx = 0; isOneShot = false; loadTrack(false);
        });
    };
}

fetchBgmPlaylist(function() {
    if (!_bgmInited) { _bgmInited = true; initBgmPlayer(); }
});
