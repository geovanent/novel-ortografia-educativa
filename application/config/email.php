<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$config['protocol']='smtp';
$config['smtp_host']=getenv('SMTP_HOST') ?: '';
$config['smtp_user']=getenv('SMTP_USER') ?: '';
$config['smtp_pass']=getenv('SMTP_PASSWORD') ?: '';
$config['smtp_port']=(int)(getenv('SMTP_PORT') ?: 587);
$config['smtp_crypto']=getenv('SMTP_CRYPTO')==='none' ? '' : (getenv('SMTP_CRYPTO') ?: 'tls');
$config['smtp_timeout']=10;
$config['mailtype']='text';
$config['charset']='utf-8';
$config['newline']="\r\n";
$config['crlf']="\r\n";
$config['validate']=TRUE;
