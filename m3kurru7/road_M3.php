<?php require_once __DIR__ . '/includes/db.php'; ?>
<?php
$isAdmin  = isset($_SESSION['admin']);
$isUser   = isset($_SESSION['user']);
$isGuest  = !$isAdmin && !$isUser;
$nickName = $isUser
    ? htmlspecialchars($_SESSION['user']['nickname'])
    : ($isAdmin ? htmlspecialchars($_SESSION['admin_nickname'] ?? $_SESSION['admin']) : null);
?>
<div class="container_roadbee">
  <div class="roadbee-header">
    <?php if ($isAdmin): ?>
    <a href="#/road_M3/upload" class="btn btn-primary">+</a>
    <?php endif; ?>
  </div>
  <div class="roadbee-feed">
    <?php
    $secretWhere = $isGuest ? ' WHERE is_secret = 0' : '';
    $result = $mysqli->query("SELECT id,title,content,collapsed,is_secret FROM home_road_M3_posts$secretWhere ORDER BY id DESC");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row):
      $isCollapsed = !empty($row['collapsed']);
    ?>
    <div class="rb-post" id="rb-post-<?= $row['id'] ?>" data-id="<?= $row['id'] ?>">
      <div class="rb-post-header">
        <div class="rb-post-left">
          <span class="rb-post-no"><?= !empty($row['is_secret']) ? '<i class="fa-solid fa-lock" style="font-size:11px;margin-right:5px;opacity:0.6"></i>' : '' ?><?= htmlspecialchars($row['title'] ?? '') ?></span>
          <button class="rb-collapse-btn" onclick="rbToggle(<?= $row['id'] ?>,'M3')">
            <i class="fa-solid <?= $isCollapsed ? 'fa-angle-down' : 'fa-angle-up' ?>"></i>
          </button>
        </div>
        <?php if ($isAdmin): ?>
        <div class="rb-post-right">
          <a href="#/road_M3/edit/<?= $row['id'] ?>" class="btn btn-secondary btn-sm">수정</a>
          <button class="btn btn-danger btn-sm" onclick="rbDel(<?= $row['id'] ?>,'M3')">삭제</button>
        </div>
        <?php endif; ?>
      </div>
      <div class="rb-post-body" id="rb-body-<?= $row['id'] ?>"<?= $isCollapsed ? ' style="display:none"' : '' ?>>
        <div class="rb-post-content"><?= $row['content'] ?></div>
        <div class="rb-post-comments">
          <div class="rb-cmt-list" id="rb-cmt-<?= $row['id'] ?>"></div>
          <div class="rb-comment-form">
            <?php if ($nickName): ?>
            <input type="text" id="rb-auth-<?= $row['id'] ?>" value="<?= $nickName ?>" disabled style="opacity:0.6">
            <?php else: ?>
            <input type="text" id="rb-auth-<?= $row['id'] ?>" placeholder="닉네임" maxlength="50">
            <?php endif; ?>
            <textarea placeholder="댓글 내용" rows="3" id="rb-cmt-inp-<?= $row['id'] ?>"></textarea>
            <button class="btn btn-primary" onclick="rbCmtSubmit(<?= $row['id'] ?>,'M3')">등록</button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
(function(){
  var isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
  function escH(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function loadComments(postId){
    $.get('ajax_get_road_comments.php',{road:'M3',id:postId},function(data){
      var $list=$('#rb-cmt-'+postId).empty();
      data.items.forEach(function(c){
        var canDel = data.is_admin || c.is_mine;
        var delBtn = canDel ? '<button class="rbc-del" data-id="'+c.id+'" data-post="'+postId+'" data-road="M3">✕</button>' : '';
        var editBtn = c.is_mine ? '<button class="rbc-del rbc-edit" data-id="'+c.id+'" data-post="'+postId+'" data-road="M3" data-content="'+escH(c.content)+'" style="margin-right:10px;font-size:18px;position:relative;top:3px;">*</button>' : '';
        var btns = (editBtn||delBtn) ? '<div class="rbc-btns">'+editBtn+delBtn+'</div>' : '';
        $list.append('<div class="rb-comment-item" data-id="'+c.id+'">'+btns+'<div class="rbc-author">'+(c.author?escH(c.author):'익명')+'</div><div class="rbc-date">'+c.created_at+'</div><div class="rbc-content">'+escH(c.content)+'</div></div>');
      });
    });
  }

  function delComment(id,postId,road){
    if(!confirm('댓글을 삭제하시겠습니까?'))return;
    $.post('ajax_delete_road_comment.php',{road:road,id:id}).done(function(d){
      if(d.success) loadComments(postId);
    });
  }

  function editComment(id,postId,road,oldContent){
    var $inp=$('#rb-cmt-inp-'+postId);
    var $btn=$('#rb-post-'+postId+' .rb-comment-form .btn');
    $inp.val(oldContent).trigger('input');
    setTimeout(function(){$inp[0].focus();},50);
    $btn.text('수정').removeAttr('onclick').off('click').on('click',function(){
      var newContent=$inp.val().trim();
      if(!newContent){alert('내용을 입력하세요.');return;}
      $.post('ajax_update_road_comment.php',{road:road,id:id,content:newContent}).done(function(d){
        if(d.success){
          $inp.val('').trigger('input');
          $btn.text('등록').off('click').attr('onclick','rbCmtSubmit('+postId+',\'M3\')');
          loadComments(postId);
        } else alert(d.message);
      });
    });
  }

  window.rbToggle=function(id,road){
    var $body=$('#rb-body-'+id);
    var $btn=$('#rb-post-'+id+' .rb-collapse-btn');
    var hidden=$body.is(':hidden');
    $body.toggle();
    $btn.html(hidden ? '<i class="fa-solid fa-angle-up"></i>' : '<i class="fa-solid fa-angle-down"></i>');
    if(isAdmin){
      $.post('ajax_toggle_road_collapse.php',{road:road,id:id,collapsed:hidden?0:1});
    }
  };

  window.rbDel=function(id,road){
    if(!confirm('글을 삭제하시겠습니까?'))return;
    $.post('ajax_delete_road.php',{road:road,id:id}).done(function(){$('#rb-post-'+id).remove();});
  };

  window.rbCmtSubmit=function(postId,road){
    var author=$('#rb-auth-'+postId).val().trim();
    var content=$('#rb-cmt-inp-'+postId).val().trim();
    if(!content){alert('내용을 입력하세요.');return;}
    $.post('ajax_save_road_comment.php',{road:road,post_id:postId,author:author,content:content}).done(function(d){
      if(d.success){$('#rb-auth-'+postId).val('');$('#rb-cmt-inp-'+postId).val('');loadComments(postId);}
      else alert(d.message);
    });
  };

  $(document).off('.rbComment');
  $(document).on('click.rbComment','.rbc-del:not(.rbc-edit)',function(){
    var id=$(this).data('id'), postId=$(this).data('post'), road=$(this).data('road');
    delComment(id,postId,road);
  });
  $(document).on('click.rbComment','.rbc-edit',function(){
    var id=$(this).data('id'), postId=$(this).data('post'), road=$(this).data('road'), content=$(this).data('content');
    editComment(id,postId,road,content);
  });
  $(document).on('input.rbComment','.rb-comment-form textarea',function(){
    this.style.height='auto';
    this.style.height=this.scrollHeight+'px';
  });

  $('.rb-post').each(function(){loadComments($(this).data('id'));});
})();
</script>