<?php
session_start();
mb_language("Japanese");
mb_internal_encoding("UTF-8");
require_once('libs/MailForm.php');
require_once('MySetting.php');
$MailFormSetting = new MySetting();
$MailFormSetting->setDebugMode(TRUE);
$MailForm = new MailForm($MailFormSetting);
$result = $MailForm->doAction();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="content-language" content="ja" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta name="keywords" content="****"  />
<meta name="description" content="*****"  />
<title>メールフォーム</title>

<style>
body{
    font-size: 12px;
}
table.formTable{
    width:800px;
    border-collapse:collapse;   
    border:1px solid #000;
    border-spacing:0;
    empty-cells:show;
    margin:0 0 15px 0;
}
	
table.formTable th,
table.formTable td{
    padding:5px;
    border:1px solid #000;
}

table.formTable th{
    background-color:#eee;
    font-weight:bold;
    width:200px;
    text-align:left;
}
</style>

</head>

<body>

<?php
if($result->hasError()){
echo('<ul class="error" id="errorList">');
echo($result->getErrorsHTML('<li>','</li>'));
echo('</ul>');
}
?>


<?php if($result->getPageMode() == 'edit'){ ?>
<h4 class="contactSubTitle">お問い合わせフォーム</h4>
<?php } ?>

<?php if($result->getPageMode() == 'confirm'){ ?>
<h4 class="contactSubTitle">入力内容のご確認</h4>
<?php } ?>

<?php if($result->getPageMode() == 'edit'){ ?>
<p><strong>※</strong>は必須項目となっています。必ずご記入下さい。</p>
<?php } ?>

<form action="" method="post" >

<table border="0" cellspacing="0" cellpadding="0" class="formTable">
<?php
echo($result->getRenderResultHTMLWithGroupID(0));
?>
</table>

<table border="0" cellspacing="0" cellpadding="0" class="formTable">
<?php
echo($result->getRenderResultHTMLWithGroupID(1));
?>
</table>

<?php if($result->getPageMode() == 'edit'){ ?>
<p class="btn"><input type="submit" name="confirm" value="確認" /></p>
<?php } ?>

<?php if($result->getPageMode() == 'confirm'){ ?>
<p class="btn"><input type="submit" name="submit" value="送信" />　<input type="submit" name="back" value="戻る" /></p>
<?php } ?>


<?php
if($result->getPageMode() == 'confirm'){
    echo($result->getValueForHiddenHTML());
}
?>


</form>

</body>
</html>