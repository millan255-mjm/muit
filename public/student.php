<?php require __DIR__.'/../inc/bootstrap.php';need('student');$u=me();
$nav=['profile'=>'My Profile','details'=>'Personal Details','results'=>'My Results','assign'=>'Online Assignments','perm'=>'My Permissions','account'=>'Account Settings'];
$T=$nav+['hall'=>'QR Hall Ticket','report'=>'Report Card'];$p=$_GET['p']??'profile';
if($_SERVER['REQUEST_METHOD']=='POST'&&($_POST['do']??'')=='account'){$nu=trim($_POST['username']);$np=$_POST['new_password'];
if(!password_verify($_POST['current'],$u['password']))flash('Current password is wrong.');
elseif(!preg_match('/^[A-Za-z0-9_.]{4,30}$/',$nu))flash('Username must be 4-30 characters: letters, numbers, dot or underscore.');
elseif(q('SELECT 1 FROM users WHERE username=? AND id<>?',[$nu,$u['id']])->fetch())flash('That username is already taken.');
elseif($np!==''&&(strlen($np)<8||$np!==$_POST['confirm']))flash('New password must be at least 8 characters and match the confirmation.');
else{q('UPDATE applications SET username=? WHERE username=?',[$nu,$u['username']]);q('UPDATE users SET username=? WHERE id=?',[$nu,$u['id']]);if($np!=='')q('UPDATE users SET password=? WHERE id=?',[password_hash($np,PASSWORD_DEFAULT),$u['id']]);logit('account_updated','student changed own username/password');flash('Account updated successfully.');}
go('?p=account');}
if($_SERVER['REQUEST_METHOD']=='POST'){q('INSERT INTO submissions VALUES(?,?,?) ON DUPLICATE KEY UPDATE answer=VALUES(answer)',[(int)$_POST['aid'],$u['id'],$_POST['answer']]);flash('Submission saved.');go('?p=assign');}
$c=q('SELECT * FROM courses WHERE id=?',[$u['course_id']])->fetch();
$marks=q('SELECT m.*,c.name cn FROM marks m JOIN courses c ON c.id=m.course_id WHERE m.student_id=?',[$u['id']])->fetchAll();
head($T[$p]??'Student',$nav);
if($p=='profile'){echo '<div class="card prof"><div class=avatar>'.e(strtoupper($u['name'][0])).'</div><div><h2>'.e($u['name']).'</h2><p class=muted>'.e($u['reg_no']).'</p><p>'.e($c['name']??'').'</p><span class="tag '.($u['paid']?'approved':'pending').'">'.($u['paid']?'Fees paid':'Fees not paid').'</span></div></div>';
foreach(q('SELECT * FROM announcements ORDER BY id DESC LIMIT 3')->fetchAll() as $a)echo '<div class=card><b>'.e($a['title']).'</b><p>'.nl2br(e($a['body'])).'</p></div>';}
elseif($p=='details'){$a=q('SELECT * FROM applications WHERE username=?',[$u['username']])->fetch()?:[];
tbl(['Field','Detail'],[['Full name',e($u['name'])],['Registration No.',e($u['reg_no'])],['Course',e($c['name']??'')],['Date of birth',e($a['dob']??'')],['Gender',e($a['gender']??'')],['Address',e($a['address']??'')],['Phone',e($u['phone'])],['Email',e($u['email'])]]);}
elseif($p=='results'||$p=='report'){$r=[];foreach($marks as $m)$r[]=[e($m['cn']),e($m['assessment']),e($m['score']+0).' / '.e($m['max_score']+0),round($m['score']/max($m['max_score'],0.1)*100,1).'%'];
if($p=='report')echo '<div class=card><b>'.e($u['name']).'</b> &middot; '.e($u['reg_no']).' &middot; '.e($c['name']??'').'</div>';
tbl(['Course','Assessment','Score','Percent'],$r);
echo $p=='results'?'<a class="btn" href="?p=report">Download report card</a>':'<button class="btn" onclick="print()">Print / save as PDF</button>';}
elseif($p=='assign'){$as=q('SELECT a.*,s.answer FROM assignments a JOIN enrollments e ON e.course_id=a.course_id AND e.student_id=? LEFT JOIN submissions s ON s.assignment_id=a.id AND s.student_id=? ORDER BY a.id DESC',[$u['id'],$u['id']])->fetchAll();
foreach($as as $a)echo '<form method=post class=card><h3>'.e($a['title']).' <small class=muted>Due '.e($a['due']).'</small></h3><p>'.nl2br(e($a['body'])).'</p><input type=hidden name=aid value='.$a['id'].'><textarea name=answer rows=4 placeholder="Type your answer">'.e($a['answer']).'</textarea><button class="btn ok">'.($a['answer']?'Update answer':'Submit answer').'</button></form>';
if(!$as)echo '<div class=card><p class=muted>No assignments yet.</p></div>';}
elseif($p=='perm'){$ok=$u['paid']&&$u['hall_ok'];echo '<div class=grid3><div class=card><h3>QR hall ticket</h3><p class=muted>Your exam hall ticket with QR code, including your payment slip. Available when fees are paid and the admin allows it.</p><a class="btn '.($ok?'':'ghost').'" href="?p=hall">Open QR hall ticket</a></div></div>';}
elseif($p=='account'){echo '<form method=post class="card narrow" style="margin:0"><input type=hidden name=do value=account><label>Username<input name=username value="'.e($u['username']).'" required></label><label>New password <small class=muted>(leave empty to keep the current one)</small><input type=password name=new_password minlength=8 autocomplete=new-password></label><label>Confirm new password<input type=password name=confirm autocomplete=new-password></label><label>Current password <small class=muted>(required to save)</small><input type=password name=current required></label><button class="btn ok big">Save changes</button></form>';}
elseif($p=='hall'){$ok=$u['paid']&&$u['hall_ok'];
if(!$ok)echo '<div class="alert err">Your QR hall ticket is locked. Fees must be paid and the admin must allow access.</div>';
else{$txt="Reg:{$u['reg_no']}|Name:{$u['name']}|Course:".($c['name']??'')."|Payment:PAID|Date:".date('d/m/Y');
echo '<div class="card ticket"><h2>EXAMINATION HALL TICKET</h2><p><b>'.e($u['name']).'</b></p><p>Reg No: '.e($u['reg_no']).'<br>Course: '.e($c['name']??'').'<br>Payment: PAID<br>Issued: '.date('d/m/Y').'</p><div id=qr></div><script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script><script>new QRCode(document.getElementById("qr"),{text:'.json_encode($txt).',width:170,height:170})</script></div>';}
echo '<div class="card ticket"><h2>PAYMENT SLIP</h2><p><b>'.e($u['name']).'</b> &middot; '.e($u['reg_no']).'</p><p>Course: '.e($c['name']??'').'</p><p>Status: <span class="tag '.($u['paid']?'approved':'pending').'">'.($u['paid']?'PAID':'NOT PAID').'</span></p><p class=muted>Printed '.date('d/m/Y H:i').'</p></div><button class=btn onclick="print()">Print / save as PDF</button>';}
foot();
