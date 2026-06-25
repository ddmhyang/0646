<?php require_once __DIR__ . '/includes/db.php'; ?>
<div class="container_todo">
  <div class="todo-layout">
    <div class="todo-left">
      <div class="todo-cal-header">
        <button class="todo-cal-nav" id="td-prev">&#8249;</button>
        <span class="todo-cal-title" id="td-month-title"></span>
        <button class="todo-cal-nav" id="td-next">&#8250;</button>
      </div>
      <div class="todo-cal-grid" id="td-cal-dow">
        <div class="todo-cal-dow">월</div><div class="todo-cal-dow">화</div>
        <div class="todo-cal-dow">수</div><div class="todo-cal-dow">목</div>
        <div class="todo-cal-dow">금</div>
        <div class="todo-cal-dow sat">토</div><div class="todo-cal-dow sun">일</div>
      </div>
      <div class="todo-cal-grid" id="td-cal-days"></div>
    </div>

    <div class="todo-right">
      <div class="td-date-label" id="td-date-label"></div>
      <div class="todo-cats" id="td-cats"></div>
      <?php if (isset($_SESSION['admin'])): ?>
      <div class="todo-add-cat" id="td-add-cat-wrap">
        <input type="text" id="td-new-cat" placeholder="+ 카테고리 추가 (Enter)">
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
(function(){
  var isAdmin = <?= isset($_SESSION['admin']) ? 'true' : 'false' ?>;
  var CAT_COLORS = ['#ff9a9e','#a18cd1','#ffecd2','#84fab0','#a1c4fd','#fbc2eb','#f9ca24','#6ab04c'];
  var today = new Date();
  var cur = { y: today.getFullYear(), m: today.getMonth() };
  var selDate = fmtDate(today);
  var monthSummary = {};
  var longPressTimer = null;

  function fmtDate(d) {
    return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
  }
  function fmtYM(y,m) { return y+'년 '+(m+1)+'월'; }
  function fmtDisplay(dt) {
    var p = dt.split('-');
    return p[0]+'년 '+parseInt(p[1])+'월 '+parseInt(p[2])+'일';
  }

  function loadMonth() {
    var ym = cur.y+'-'+String(cur.m+1).padStart(2,'0');
    $.get('ajax_get_todo.php',{action:'month',ym:ym},function(d){
      monthSummary = d.summary || {};
      renderCal();
    });
  }

  function renderCal() {
    $('#td-month-title').text(fmtYM(cur.y, cur.m));
    var $days = $('#td-cal-days').empty();
    var first = new Date(cur.y, cur.m, 1);
    var dow = (first.getDay()+6)%7;
    var daysInMonth = new Date(cur.y, cur.m+1, 0).getDate();

    var prevDays = new Date(cur.y, cur.m, 0).getDate();
    for (var i = dow-1; i >= 0; i--) {
      var d = prevDays - i;
      var dt = (cur.m === 0 ? cur.y-1 : cur.y)+'-'+String(cur.m === 0 ? 12 : cur.m).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      $days.append(dayCell(d, dt, true));
    }
    for (var d = 1; d <= daysInMonth; d++) {
      var dt = cur.y+'-'+String(cur.m+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      $days.append(dayCell(d, dt, false));
    }
    var total = dow + daysInMonth;
    var remain = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (var d2 = 1; d2 <= remain; d2++) {
      var dt2 = (cur.m === 11 ? cur.y+1 : cur.y)+'-'+String(cur.m === 11 ? 1 : cur.m+2).padStart(2,'0')+'-'+String(d2).padStart(2,'0');
      $days.append(dayCell(d2, dt2, true));
    }
  }

  function dayCell(d, dt, otherMonth) {
    var todayStr = fmtDate(today);
    var info = monthSummary[dt] || {total:0, checked:0};
    var cls = 'todo-cal-day';
    if (otherMonth) cls += ' other-month';
    if (dt === todayStr) cls += ' today';
    if (dt === selDate) cls += ' selected';
    var dayOfWeek = new Date(dt).getDay();
    if (dayOfWeek === 6) cls += ' sat';
    if (dayOfWeek === 0) cls += ' sun';
    var boxCls = 'todo-day-box';
    var boxContent = '';
    if (info.total > 0) {
      if (info.checked === info.total) { boxCls += ' all-done'; boxContent = '✓'; }
      else { boxCls += ' has-items'; boxContent = (info.total - info.checked); }
    }
    var badge = '<div class="'+boxCls+'">'+boxContent+'</div>';
    return $('<div>').addClass(cls).attr('data-date', dt).html('<div class="td-inner"><span>'+d+'</span>'+badge+'</div>');
  }

  function updateDateLabel() {
    $('#td-date-label').text(fmtDisplay(selDate));
  }

  function loadDay() {
    updateDateLabel();
    $.get('ajax_get_todo.php',{action:'day',date:selDate},function(d){
      renderCats(d.categories || [], d.items || {});
    });
  }

  function renderCats(cats, itemsBycat) {
    var $cats = $('#td-cats').empty();
    cats.forEach(function(cat) {
      var $cat = $('<div class="todo-cat">');
      var $hdr = $('<div class="todo-cat-header">');
      var $name = $('<span class="todo-cat-name">').text(cat.name).css('color', cat.color);
      $hdr.append($name);
      if (isAdmin) {
        var $add = $('<button class="todo-cat-add" title="항목 추가">').text('+').attr('data-cat', cat.id);
        var $colorBtn = $('<button class="todo-cat-color-btn" title="색상 변경">').text('✦').attr('data-cat', cat.id);
        var $colorInp = $('<input type="color" class="todo-cat-color-inp">').val(cat.color).attr('data-cat', cat.id);
        $hdr.append($add, $colorBtn, $colorInp);
      }
      $cat.append($hdr);

      var items = itemsBycat[cat.id] || [];
      var $list = $('<div class="todo-items-list">');
      items.forEach(function(item) { $list.append(renderItem(item, cat)); });
      if (isAdmin) {
        var $inp = $('<input type="text" placeholder="항목 추가...">').attr('data-cat', cat.id)
          .css({width:'100%',padding:'4px 6px',marginTop:'4px',border:'1px dashed #aaa',borderRadius:'6px',background:'rgba(255,255,255,.2)',color:'inherit',fontSize:'12px',fontFamily:'inherit'});
        $list.append($inp);
      }
      $cat.append($list);

      if (isAdmin) {
        $hdr.on('mousedown touchstart', function() {
          longPressTimer = setTimeout(function() {
            if (confirm('카테고리 "'+cat.name+'"을(를) 삭제하시겠습니까?\n(모든 항목이 함께 삭제됩니다)')) {
              $.post('ajax_delete_todo_category.php',{id:cat.id}).done(function(){ loadMonth(); loadDay(); });
            }
          }, 600);
        }).on('mouseup mouseleave touchend touchmove', function() {
          clearTimeout(longPressTimer);
        });
      }
      $cats.append($cat);
    });
  }

  function renderItem(item, cat) {
    var $item = $('<div class="todo-item">');
    var $chk = $('<input type="checkbox" class="todo-item-check">').prop('checked', item.is_checked == 1);
    var $txt = $('<span class="todo-item-text">').text(item.content).css('color', item.text_color || '#000');
    if (item.is_checked == 1) $txt.addClass('done');
    if (isAdmin) {
      $chk.on('change', function() {
        $.post('ajax_toggle_todo_item.php',{id:item.id,checked:this.checked?1:0}).done(function(){ loadMonth(); loadDay(); });
      });
      var $del = $('<button class="todo-item-del">✕</button>');
      $del.on('click', function(e) {
        e.stopPropagation();
        $.post('ajax_delete_todo_item.php',{id:item.id}).done(function(){ loadMonth(); loadDay(); });
      });
      $item.append($chk, $txt, $del);
    } else {
      $item.append($chk.prop('disabled', true), $txt);
    }
    return $item;
  }

  $(document).off('.td');

  // 날짜 클릭
  $(document).on('click.td', '.todo-cal-day', function() {
    selDate = $(this).attr('data-date');
    renderCal();
    loadDay();
  });

  // 월 이동
  $('#td-prev').on('click.td', function() {
    cur.m--; if (cur.m < 0) { cur.m = 11; cur.y--; }
    loadMonth();
  });
  $('#td-next').on('click.td', function() {
    cur.m++; if (cur.m > 11) { cur.m = 0; cur.y++; }
    loadMonth();
  });

  // 카테고리 추가
  $(document).on('keydown.td', '#td-new-cat', function(e) {
    if (e.key !== 'Enter') return;
    var name = $(this).val().trim();
    if (!name) return;
    var color = CAT_COLORS[Math.floor(Math.random() * CAT_COLORS.length)];
    $.post('ajax_save_todo_category.php',{name:name,color:color}).done(function(d){
      if (d.success) { $('#td-new-cat').val(''); loadDay(); loadMonth(); }
    });
  });

  // 항목 추가 (+ 버튼 → 인풋 포커스)
  $(document).on('click.td', '.todo-cat-add', function(e) {
    e.stopPropagation();
    var catId = $(this).data('cat');
    $('[data-cat="'+catId+'"]').filter('input[type=text]').focus();
  });

  // 항목 추가 (Enter)
  $(document).on('keydown.td', '.todo-items-list input[type=text]', function(e) {
    if (e.key !== 'Enter') return;
    var content = $(this).val().trim();
    if (!content) return;
    var catId = $(this).data('cat');
    $.post('ajax_save_todo_item.php',{category_id:catId,date:selDate,content:content}).done(function(d){
      if (d.success) { $(e.target).val(''); loadMonth(); loadDay(); }
    });
  });

  // 색상 버튼 → color input 트리거
  $(document).on('click.td', '.todo-cat-color-btn', function(e) {
    e.stopPropagation();
    $(this).siblings('.todo-cat-color-inp').trigger('click');
  });

  // 색상 변경
  $(document).on('change.td', '.todo-cat-color-inp', function() {
    $.post('ajax_update_todo_cat_color.php',{id:$(this).data('cat'),color:$(this).val()}).done(function(d){
      if (d.success) loadDay();
    });
  });

  loadMonth();
  loadDay();
})();
</script>