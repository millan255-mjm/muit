<?php require __DIR__.'/../inc/bootstrap.php';
if($_SERVER['REQUEST_METHOD']=='POST'){$f=['full_name','course_id','dob','gender','address','phone','email'];$ok=true;foreach($f as $k)if(trim($_POST[$k]??'')==='')$ok=false;
$c=up('cert');$ph=up('photo');$sl=up('slip');
if(!$ok||!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))flash('Please complete every field with a valid email.');
elseif(!$c||!$ph||!$sl)flash('Upload all three documents (JPG, PNG or PDF, max 3 MB each).');
else{q('INSERT INTO applications(full_name,course_id,dob,gender,address,phone,email,cert,photo,slip) VALUES(?,?,?,?,?,?,?,?,?,?)',[trim($_POST['full_name']),(int)$_POST['course_id'],$_POST['dob'],$_POST['gender'],$_POST['address'],$_POST['phone'],$_POST['email'],$c,$ph,$sl]);
flash('Application submitted. Your reference number is <b>#'.db()->lastInsertId().'</b>. Keep it and use it with your email on the <a href=/status.php>status page</a>.');go('/status.php');}}
head('Student registration application');?>
<form method=post enctype=multipart/form-data class="card form">
<label>Full legal name<input name=full_name required></label>
<label>Course / programme<select name=course_id required><?=opts('SELECT id,name FROM courses ORDER BY name')?></select></label>
<label>Date of birth (DD/MM/YYYY)<input type=date name=dob required></label>
<label>Gender<select name=gender><option>Male</option><option>Female</option><option>Other</option></select></label>
<label class=full>Physical home address<input name=address required></label>
<label>Valid phone number<input name=phone required></label>
<label>Primary email address<input type=email name=email required></label>
<label>Certificate / transcripts copy<input type=file name=cert accept=".jpg,.jpeg,.png,.pdf" required></label>
<label>Passport photograph<input type=file name=photo accept=".jpg,.jpeg,.png,.pdf" required></label>
<label class=full>Proof of paid application fee (slip)<input type=file name=slip accept=".jpg,.jpeg,.png,.pdf" required></label>
<div class=full><button class="btn big">Submit application</button></div></form><?php foot();
