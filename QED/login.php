<div class="container_login">
<div class="page-login" style="max-width:320px">
  <h2>로그인</h2>
  <form id="form_login" style="margin-top:16px">
    <div class="form-group">
      <input type="text"     name="username" placeholder="아이디" required style="width:100%">
    </div>
    <div class="form-group">
      <input type="password" name="password" placeholder="비밀번호" required style="width:100%">
    </div>
    <button type="submit">로그인</button>
  </form>
  <p id="login-msg" class="error-msg"></p>
</div>
</div>
<script>
$(document).off('submit','#form_login').on('submit','#form_login',function(e){
    e.preventDefault();
    $.post('ajax_login.php',$(this).serialize())
     .done(function(d){
         if(d.success) location.hash='#/';
         else $('#login-msg').text(d.message);
     });
});
</script>