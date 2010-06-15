<?php
/*
| -------------------------------------------------------------------
  Email configuration settings
  See: http://codeigniter.com/user_guide/libraries/email.html
| -------------------------------------------------------------------
*/
$config['useragent'] = 'CodeIgniter';   //The "user agent".
$config['protocol'] = 'mail';   //mail, sendmail, or smtp	The mail sending protocol.
$config['mailpath'] = '/usr/sbin/sendmail';   //The server path to Sendmail.

/*
| -------------------------------------------------------------------
| SMTP Settings
| Use if protocol set to smtp above; otherwise ignore
| -------------------------------------------------------------------
*/

$config['smtp_host'] = '';		//SMTP Server Address.
$config['smtp_user'] = '';		//SMTP Username.
$config['smtp_pass'] = '';		//SMTP Password.
$config['smtp_port'] = '25';	// SMTP Port.
$config['smtp_timeout'] = '5';	// SMTP Timeout (in seconds).

/*
| -------------------------------------------------------------------
| Formatting
| -------------------------------------------------------------------
*/

$config['wordwrap'] = TRUE;   //TRUE or FALSE (boolean)	Enable word-wrap.
$config['wrapchars'] = 76;   //	Character count to wrap at.
$config['mailtype'] = 'text';   //text or html	Type of mail. If you send HTML email you must send it as a complete web page. Make sure you don't have any relative links or relative image paths otherwise they will not work.
$config['charset'] = 'utf-8';   //	Character set (utf-8, iso-8859-1, etc.).
$config['validate'] = FALSE;   //TRUE or FALSE (boolean)	Whether to validate the email address.
$config['priority'] = 3;   //1, 2, 3, 4, 5	Email Priority. 1 = highest. 5 = lowest. 3 = normal.
$config['crlf'] = "\n";   //"\r\n" or "\n" or "\r"	Newline character. (Use "\r\n" to comply with RFC 822).
$config['newline'] = "\n";				//"\r\n" or "\n" or "\r"	Newline character. (Use "\r\n" to comply with RFC 822).

$config['bcc_batch_mode'] = FALSE;		//Enable BCC Batch Mode.
$config['bcc_batch_size'] = 200;		//Number of emails in each BCC batch.
