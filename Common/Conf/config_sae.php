<?php
/*SAE配置*/
return array(
	'VERIFY_NKU' => 'Verify.NankaiEduOnline', 
	'SHOW_PAGE_TRACE' => 0, 
    'NKU_OPEN' => false,
    'MAIL_WAY' => 'sae', // or 'phpmailer'
	'FILE_UPLOAD_TYPE' => 'QINIU',
	 'TMPL_PARSE_STRING' => array(
	 	'__PUBLIC__' => '/Public', 
	 	'__CDNLIB__'=>'http://apps.bdimg.com/libs',
	 	'__UPLOAD__' => 'http://nkuprint-uploads.stor.sinaapp.com',
	 ),
);
