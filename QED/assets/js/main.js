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
                    var $load = $('<img src="../assets/images/loading.png" alt="uploading">');
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

function scaleLayout() {
    var el = document.querySelector('.layout');
    if (!el) return;
    el.style.transform = '';
    el.style.left = '';
    el.style.top = '';
    var W = el.offsetWidth;
    var H = el.offsetHeight;
    if (!W || !H) return;
    var s = Math.min(window.innerWidth / W, window.innerHeight / H);
    el.style.transformOrigin = 'top left';
    el.style.transform = 'scale(' + s + ')';
    el.style.left = ((window.innerWidth  - W * s) / 2) + 'px';
    el.style.top  = ((window.innerHeight - H * s) / 2) + 'px';
}
window.scaleLayout = scaleLayout;

var $globalFooter = null;

$(function() {
    if ($('.site-footer').length) {
        $globalFooter = $('.site-footer').detach();
    }

    loadRoute(location.hash || '#/');
    $(window).on('hashchange', function() { loadRoute(location.hash); });

    $(document).on('click', '#btn_logout', function() {
        $.post('ajax_logout.php').done(function() { location.reload(); });
    });
});

function toggleAudio(btn) {
    var $item = $(btn).closest('.audio-item');
    var url = $item.data('url');
    if (!$item.data('audio')) {
        var audio = new Audio(url);
        $item.data('audio', audio);
        audio.addEventListener('timeupdate', function() {
            if (!audio.duration) return;
            var pct = (audio.currentTime / audio.duration) * 100;
            $item.find('.audio-progress').css('width', pct + '%');
            $item.find('.audio-handle').css('left', pct + '%');
            var m = Math.floor(audio.currentTime / 60);
            var s = Math.floor(audio.currentTime % 60);
            $item.find('.audio-time').text(m + ':' + (s < 10 ? '0' : '') + s);
        });
        audio.addEventListener('ended', function() {
            $(btn).text('▶');
            $item.find('.audio-progress').css('width', '0%');
            $item.find('.audio-handle').css('left', '0%');
            $item.find('.audio-time').text('0:00');
        });
        $item.find('.audio-bar-wrap').on('click', function(e) {
            if (!audio.duration) return;
            var rect = this.getBoundingClientRect();
            var pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            audio.currentTime = pct * audio.duration;
        });
    }
    var audio = $item.data('audio');
    if (audio.paused) {
        $('.audio-item').each(function() {
            var a = $(this).data('audio');
            if (a && !a.paused) { a.pause(); $(this).find('.audio-play-btn').text('▶'); }
        });
        audio.play();
        $(btn).text('⏸');
    } else {
        audio.pause();
        $(btn).text('▶');
    }
}

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

    if (base === '') {
        $('.site-logo').hide();
    } else {
        $('.site-logo').show();
    }

    $('.nav-item').removeClass('active');
    var $nav = $('#nav_' + base);
    if (!$nav.length) $nav = $('#nav_main');
    $nav.addClass('active');
}

function routeToUrl(path) {
    if (!path) return 'main_content.php';
    var parts = path.split('/');
    var page = parts[0], sub = parts[1], id = encodeURIComponent(parts[2] || '');
    if (sub === 'detail') return page + '_detail.php?id=' + id;
    if (sub === 'upload') return page + '_form.php';
    if (sub === 'edit')   return page + '_form.php?id=' + id;
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
