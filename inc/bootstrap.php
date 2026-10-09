<?php
session_start();ini_set('display_errors','0');ini_set('log_errors','1');
const UNI='MILLAN UNIVERSITY OF INFORMATION TECHNOLOGY';
function env($k,$d=''){$v=getenv($k);return($v===false||$v==='')?$d:$v;}
function db(){static $p;if($p)return $p;
$p=new PDO('mysql:host='.env('MYSQLHOST',env('DB_HOST','127.0.0.1')).';port='.env('MYSQLPORT','3306').';dbname='.env('MYSQLDATABASE',env('DB_NAME','muit')).';charset=utf8mb4',env('MYSQLUSER',env('DB_USER','root')),env('MYSQLPASSWORD',env('DB_PASS','')),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
install($p);return $p;}
function install($p){
foreach(["CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(60) UNIQUE,password VARCHAR(255),role VARCHAR(10),name VARCHAR(120),email VARCHAR(120),phone VARCHAR(30),reg_no VARCHAR(40),course_id INT,paid TINYINT DEFAULT 0,hall_ok TINYINT DEFAULT 0)",
"CREATE TABLE IF NOT EXISTS courses(id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(20),name VARCHAR(120),lecturer_id INT NULL)",
"CREATE TABLE IF NOT EXISTS applications(id INT AUTO_INCREMENT PRIMARY KEY,full_name VARCHAR(120),course_id INT,dob DATE,gender VARCHAR(10),address VARCHAR(255),phone VARCHAR(30),email VARCHAR(120),cert VARCHAR(120),photo VARCHAR(120),slip VARCHAR(120),status VARCHAR(10) DEFAULT 'pending',temp_pass VARCHAR(20),username VARCHAR(60),created TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
"CREATE TABLE IF NOT EXISTS enrollments(student_id INT,course_id INT,PRIMARY KEY(student_id,course_id))",
"CREATE TABLE IF NOT EXISTS attendance(id INT AUTO_INCREMENT PRIMARY KEY,course_id INT,student_id INT,day DATE,present TINYINT)",
"CREATE TABLE IF NOT EXISTS marks(id INT AUTO_INCREMENT PRIMARY KEY,course_id INT,student_id INT,assessment VARCHAR(60),score DECIMAL(5,1))",
"CREATE TABLE IF NOT EXISTS assignments(id INT AUTO_INCREMENT PRIMARY KEY,course_id INT,title VARCHAR(120),body TEXT,due DATE)",
"CREATE TABLE IF NOT EXISTS submissions(assignment_id INT,student_id INT,answer TEXT,PRIMARY KEY(assignment_id,student_id))",
"CREATE TABLE IF NOT EXISTS announcements(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(120),body TEXT,created TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"] as $s)$p->exec($s);
$p->exec('CREATE TABLE IF NOT EXISTS logs(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,username VARCHAR(60),action VARCHAR(40),detail VARCHAR(255),ip VARCHAR(45),created TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
$p->exec('CREATE TABLE IF NOT EXISTS files(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120),mime VARCHAR(60),data LONGTEXT)');
try{$p->exec('ALTER TABLE marks ADD COLUMN max_score DECIMAL(6,1) NOT NULL DEFAULT 100');}catch(Exception $e){}
if(!$p->query("SELECT 1 FROM users WHERE role='admin' LIMIT 1")->fetch()){
$p->prepare("INSERT INTO users(username,password,role,name) VALUES('admin',?,'admin','System Administrator')")->execute([password_hash(env('ADMIN_PASSWORD','admin123'),PASSWORD_DEFAULT)]);
foreach([['BIT','BSc Information Technology'],['BNE','BSc Network Engineering'],['BCS','BSc Cyber Security'],['DCN','Diploma in Computer Networking']] as $c)$p->prepare('INSERT INTO courses(code,name) VALUES(?,?)')->execute($c);}}
function logit($a,$d=''){try{q('INSERT INTO logs(user_id,username,action,detail,ip) VALUES(?,?,?,?,?)',[$_SESSION['u']['id']??null,$_SESSION['u']['username']??'guest',$a,mb_substr($d,0,250),$_SERVER['REMOTE_ADDR']??'']);}catch(Exception $e){}}
function q($s,$a=[]){$t=db()->prepare($s);$t->execute($a);return $t;}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES);}
function go($u){header("Location: $u");exit;}
function flash($m=null){if($m!==null){$_SESSION['f']=$m;return;}$m=$_SESSION['f']??'';unset($_SESSION['f']);return $m;}
function need($r){if(($_SESSION['u']['role']??'')!==$r)go('/login.php');return $_SESSION['u'];}
function me(){return q('SELECT * FROM users WHERE id=?',[$_SESSION['u']['id']])->fetch();}
function opts($sql,$a=[],$sel=null){$o='';foreach(q($sql,$a)->fetchAll() as $r){$r=array_values($r);$o.='<option value="'.e($r[0]).'"'.($sel==$r[0]?' selected':'').'>'.e($r[1]).'</option>';}return $o;}
function pf($do,$id,$l,$c='',$x=''){return '<form method=post class=inl><input type=hidden name=id value="'.e($id).'">'.$x.'<button class="btn sm '.$c.'" name=do value="'.$do.'"'.(strpos($c,'no')!==false?' onclick="return confirm(\'Are you sure?\')"':'').'>'.$l.'</button></form>';}
function tbl($h,$rows){echo '<div class=tw><table><tr>';foreach($h as $x)echo "<th>$x</th>";echo '</tr>';foreach($rows as $r){echo '<tr>';foreach($r as $c)echo "<td>$c</td>";echo '</tr>';}if(!$rows)echo '<tr><td colspan='.count($h).' class=muted>Nothing here yet.</td></tr>';echo '</table></div>';}
function up($k){if(empty($_FILES[$k]['name'])||$_FILES[$k]['error']||!is_uploaded_file($_FILES[$k]['tmp_name']))return null;$x=strtolower(pathinfo($_FILES[$k]['name'],PATHINFO_EXTENSION));$m=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','pdf'=>'application/pdf'];if(!isset($m[$x])||$_FILES[$k]['size']>3e6)return null;$d=file_get_contents($_FILES[$k]['tmp_name']);if($d===false)return null;q('INSERT INTO files(name,mime,data) VALUES(?,?,?)',[mb_substr(basename($_FILES[$k]['name']),0,120),$m[$x],base64_encode($d)]);return (string)db()->lastInsertId();}
function head($t,$nav=[],$h=true){$cur=$_GET['p']??array_key_first($nav);
echo '<!doctype html><html lang=en><head><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>'.e($t).' | MUIT</title><link rel=stylesheet href=/assets/style.css></head><body><header class=top><a class=brand href=/><span class=logo>M</span><span><b>MUIT</b><small>'.UNI.'</small></span></a><nav>';
if(isset($_SESSION['u']))echo '<span class=who>'.e($_SESSION['u']['name']).'</span><a class="btn sm out" href=/logout.php>Logout</a>';
else echo '<a class="btn sm ghost" href=/login.php>Login</a><a class="btn sm" href=/apply.php>Apply now</a>';
echo '</nav></header><div class=wrap>';
if($nav){echo '<aside class=side>';foreach($nav as $k=>$v)echo '<a class="'.($k==$cur?'on':'').'" href="?p='.$k.'">'.$v.'</a>';echo '</aside>';}
echo '<main class=main>';if($h)echo '<h1>'.e($t).'</h1>';
if($m=flash())echo '<div class=alert>'.$m.'</div>';}
function foot(){echo '</main></div><footer>&copy; '.date('Y').' '.UNI.'</footer></body></html>';}
