<?php require __DIR__.'/../inc/bootstrap.php';
if($_SERVER['REQUEST_METHOD']=='POST'){$u=q('SELECT * FROM users WHERE username=?',[trim($_POST['username'])])->fetch();
if($u&&password_verify($_POST['password'],$u['password'])){session_regenerate_id(true);$_SESSION['u']=['id'=>$u['id'],'role'=>$u['role'],'name'=>$u['name'],'username'=>$u['username']];logit('login',$u['role'].' logged in');
if($u['role']=='student')q('UPDATE applications SET temp_pass=NULL WHERE username=?',[$u['username']]);go('/'.$u['role'].'.php');}
logit('login_failed','username tried: '.trim($_POST['username']));flash('Wrong username or password.');}
head('Log in');?>
<form method=post class="card narrow"><label>Username<input name=username required autofocus></label><label>Password<input type=password name=password required></label><button class="btn big">Log in</button><p class=muted>No account yet? <a href=/apply.php>Apply now</a></p></form><?php foot();
