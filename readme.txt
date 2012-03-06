--------------------------------------------------------
1.settingオブジェクトの準備
--------------------------------------------------------
管理者メールやフォームの要素などを設定するため、まずMailFormSettingクラスを用意する必要があります。
MailFormSettingクラスはMailFormSettingを継承している必要があります。
MySetting.phpを例に各項目を解説します。

MailFormSettingクラスにはフォームの結果を送信する管理アドレスや自動返信メールの有無、自動返信の送信元情報などのメール関連に加え、フォームの項目を定義するフォームスキーム、独自のエラーや表示処理を行うためのコールバックメソッドを記述します。

MailFormSettingクラスには以下のセッターが用意されています。

setFormScheme(array); //フォームスキームをセットします。詳しくは[2.formSchemeの仕様]をご覧ください。
setAdminMailSubject(string); //管理者宛のメールの件名を指定します。
setAdminMail(string); //管理者のアドレスを指定します。
setDebugAdminMail(string); //デバックモードの時の管理者アドレスを指定します。
setDebugMode(bool); //デバックモードを指定します。
setAdminMailBody(string); //管理者宛メールの本文を指定します。
setReturnMailName(string); //自動返信メールを送る際に宛先となるname値をformSchemeから指定します。
setIsReturnMail(bool); //自動返信メールを送るかを指定します。
setReturnMailFromAddress(string); //自動返信メールのfrom欄のアドレスを指定します。
setReturnMailFromText(string); //自動返信メールのfrom欄のテキストを指定します。
setReturnMailSubject(string); //自動返信メールの件名を指定します。
setReturnMailBody(string); //自動返信メールの本文を指定します。
setRequiredString(string); //必須項目につくマークを指定します。
setThanksPageURL(string); //送信完了後に表示されるページのURLを指定します。


※メールの本文指定について
メール本文では以下のタグを使用できます。
##formContent## //フォームに記載された内容一覧に置き換わります。
##value_[name値]## //フォームに記載された内容個別に置き換わります。


--------------------------------------------------------
2.formSchemeの仕様
--------------------------------------------------------
formSchomeはkyeにフォームのname値を記載し、さらに配列でフォームのパラメーターを設定します。
以下の項目を指定できます。

=共通
'label' 項目名
'labelAddtionalTxt' 項目名に追加するテキスト(自動返信メールなどには記載されません)
'type' 要素のタイプ input inputs tel post textarea select radio checkbox todofuken
'trAttribute' trのAttribute
'thAttribute' thのAttribute
'tdAttribute' tdのAttribute
'attribute' フォーム要素のAttribute
'group' フォームのグループ resultクラスで結果を受ける時にグループごと別々に取得できます。
'validate' エラー条件 required mail tel post items todofuken strlenMax_20 strlenMin_20 length
'validateFnc' ユーザー定義エラーコールバック
'noDisplayEdit' 編集画面に自動的に表示しない場合はTRUE
'noDisplayMail' メールに自動的に表示しない場合はTRUE
'noDisplayConfirm' 確認画面に自動的に表示しない場合はTRUE
'renderEditFnc' 入力画面でのTD内の要素を返すコールバック関数
'valueToStringFnc' 確認画面・メールなどフォームのValueを文字列にして表示する際のコールバック関数
'size' size要素の値
'maxlength' maxlength要素の値
'firstTxt' フォーム要素直前の文字列
'aftetTxt' フォーム要素直後の文字列

==inputs
'numberOfInput' input要素の数
'inputsTexts' それぞれのinput要素の直前の文字列を配列で渡す
'inputsGlue'input同士の間に入る文字列

==textarea
'rows' rows要素の値
'cols' cols要素の値

==select
'optionFirst' selectの先頭にくるopution "選択してください"などを記載

==select radio checkbox
'items' 選択肢の配列をkey(vakue値)value(表示文字列)の形で渡す
'default' デフォルトで選択されている値を指定

--------------------------------------------------------
3.セットアップ
--------------------------------------------------------
session_start(); //セッションをスタート
mb_language("Japanese");
mb_internal_encoding("UTF-8");
require_once('libs/MailForm.php'); //基本クラスを読み込み
require_once('MySetting.php'); //settingクラスを読み込み
$MailFormSetting = new MySetting(); //settingオブジェクトを生成
$MailFormSetting->setDebugMode(TRUE); //debugモード
$MailForm = new MailForm($MailFormSetting); //基本オブジェクトを生成
$result = $MailForm->doAction(); //実行

--------------------------------------------------------
4.resultクラスについて
--------------------------------------------------------
MailForm->doActionを実行することでレンダリング結果やエラーなどを含むresultオブジェクトが返ります。
このオブジェクトを使用し、HTMLやエラーを表示させます。

例:フォームを表示
<form action="" method="post" >
<table border="0" cellspacing="0" cellpadding="0" class="formTable">
<?php
echo($result->getRenderResultHTML());
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

例:エラーを表示
if($result->hasError()){
echo('<ul class="error" id="errorList">');
echo($result->getErrorsHTML('<li>','</li>'));
echo('</ul>');
}

例:グループ分けをしてフォームを表示
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

例:現在のフォームの状態ごとにタイトルなどを変更
<?php if($result->getPageMode() == 'edit'){ ?>
<h4 class="contactSubTitle">お問い合わせフォーム</h4>
<?php } ?>
<?php if($result->getPageMode() == 'confirm'){ ?>
<h4 class="contactSubTitle">入力内容のご確認</h4>
<?php } ?>
<?php if($result->getPageMode() == 'edit'){ ?>
<p><strong>※</strong>は必須項目となっています。必ずご記入下さい。</p>
<?php } ?>

例)フォームを表示

--------------------------------------------------------
5.独自エラーチェックについて
--------------------------------------------------------
formSchemeのvalidateFncにてコールバックを指定することで独自のエラーチェックを実装することができます。
エラーがない場合はTRUEをエラーはある場合はMailFormErrorオブジェクトを返します。
MailFormErrorは以下のように生成します。
MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, 'テスト', '#1#のエラーです');
参照：Documentation/com-irohacreative-form-mailForm/MailFormError.html#methodinitError

--------------------------------------------------------
6.エラーメッセージの編集について
--------------------------------------------------------
settingクラスのエラーメッセージ一覧の配列を返すgetAdditionalErrorMessages()を実装します。
初期状態では以下のメッセージが定義されているので、必要に応じて追加、上書きを行います。
$messages['required'] = '#1#が未入力です。';
$messages['requiredSelect'] = '#1#が選択されていません。';
$messages['invalidType'] = '#1#が不正です。';
$messages['IllegalChar'] = '#1#に不正な文字が含まれています。';
$messages['tooLong'] = '#1#は#2#文字以内で入力してください。';
$messages['tooShort'] = '#1#は#2#文字以上で入力してください。';
$messages['length'] = '#1#は#2#文字で入力してください。';

--------------------------------------------------------
7.拡張
--------------------------------------------------------
(準備中)