<?php require __DIR__.'/../inc/bootstrap.php';need('admin');
$f=q('SELECT * FROM files WHERE id=?',[(int)($_GET['id']??0)])->fetch();
if(!$f){http_response_code(404);exit('File not found. Ask the applicant to apply again.');}
header('Content-Type: '.$f['mime']);header('Content-Disposition: inline; filename="'.str_replace('"','',$f['name']).'"');header('X-Content-Type-Options: nosniff');
echo base64_decode($f['data']);
