<?php require __DIR__.'/../inc/bootstrap.php';head('Check application status');
echo '<form method=get class="card row"><input name=ref placeholder="Reference number" value="'.e($_GET['ref']??'').'"><input type=email name=email placeholder="Email used to apply" value="'.e($_GET['email']??'').'"><button class=btn>Check status</button></form>';
if(!empty($_GET['ref'])){$a=q('SELECT * FROM applications WHERE id=? AND email=?',[(int)ltrim($_GET['ref'],'#'),$_GET['email']??''])->fetch();
if(!$a)echo '<div class="alert err">No application matches that reference and email.</div>';
else{echo '<div class=card><h3>'.e($a['full_name']).'</h3><p>Status: <span class="tag '.$a['status'].'">'.e($a['status']).'</span></p>';
if($a['status']=='approved'){echo $a['temp_pass']?'<p>Congratulations! Your login:</p><p class=cred>Username: <b>'.e($a['username']).'</b><br>Password: <b>'.e($a['temp_pass']).'</b></p><a class=btn href=/login.php>Go to login</a>':'<p>Your account is active. Please <a href=/login.php>log in</a>.</p>';}
elseif($a['status']=='pending')echo '<p class=muted>The admin is reviewing your application.</p>';else echo '<p class=muted>Your application was not approved. Contact the admissions office.</p>';echo '</div>';}}
foot();
