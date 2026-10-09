<?php require __DIR__.'/../inc/bootstrap.php';logit('logout');session_destroy();header('Location: /login.php');
