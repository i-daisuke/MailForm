<?php

require_once('libs/MailFormSetting.php');
require_once('libs/MailFormItem.php');

/**
 * @package com.irohacreative.form.mailForm
 */
class MySetting extends MailFormSetting {

    function __construct() {
        //type:input inputs tel post textarea select radio checkbox todofuken
        //validate:required mail tel post items todofuken strlenMax_20 strlenMin_20 length
        $formScheme = array(
            'name' => array(
                'label' => 'お名前',
                'labelAddtionalTxt' => '',
                'type' => 'input',
                'numberOfInput' => 2,
                'inputsTexts' => array('姓','名'),
                'inputsGlue' => ' ',
                'trAttribute' => '',
                'thAttribute' => '',
                'tdAttribute' => '',
                'group' => 0,
                'validate' => array('required'),
                //'validateFnc' => array($this,'errorCheck'),
                'size' => 10,
                'maxlength' => 10,
                'attribute' => '',
                'aftetTxt' => '',
                'firstTxt' => '',
                'rows' => 10,
                'cols' => 50,
                'optionFirst' => '選択してください',
                'items' => array(
                    '1' => 'テスト1',
                    '2' => 'テスト2',
                ),
                'default' => 'テスト1',
                'noDisplayEdit' => FALSE,
                'noDisplayMail' => FALSE,
                'noDisplayConfirm' => FALSE,
            ),
            'email' => array(
                'label' => 'メール',
                'labelAddtionalTxt' => '',
                'type' => 'input',
                'size' => 50,
                'validate' => array('required'),
                'noDisplayMail' => TRUE,
            ),
            'txt' => array(
                'label' => '本文',
                'type' => 'textarea',
                'validate' => array('required'),
                'rows' => 10,
                'cols' => 80,
            ),
        );

        //管理者メール
        $adminMailSubject = "お問い合わせがありました";
        $debugAdminMail = 'mail address';
        $adminMail = 'mail address';
        $adminMailBody = <<<MAIL
以下の内容のお問い合わせがありました。
--------------------------------------------------------------------
##formContent##
--------------------------------------------------------------------
MAIL;

        //自動返信メール
        $returnMailName = 'email';
        $returnMailFromAddress = 'mail address';
        $returnMailFromText = "from txt";
        $returnMailSubject = "お問い合わせ受付のお知らせ";
        $returnMailBody = <<<MAIL
※※※※※※※※ このメールは自動的に送信しています ※※※※※※※※
====================================================================
お問い合わせ受付のお知らせ
--------------------------------------------------------------------
##value_name## 様

このたびはお問い合わせをいただき、誠にありがとうございます。
誠にありがとうございます。

後ほど当社の担当者より改めてご連絡いたしますので、
どうぞよろしくお願いいたします。

以下に、お客様のお問い合わせ内容が記載されておりますので、ご確認
ください。
--------------------------------------------------------------------
##formContent##
--------------------------------------------------------------------
MAIL;

        //set
        $this->setFormScheme($formScheme);
        $this->setAdminMailSubject($adminMailSubject);
        $this->setAdminMail($adminMail);
        $this->setDebugAdminMail($debugAdminMail);
        $this->setAdminMailBody($adminMailBody);
        $this->setReturnMailName($returnMailName);
        $this->setReturnMailFromAddress($returnMailFromAddress);
        $this->setReturnMailFromText($returnMailFromText);
        $this->setReturnMailSubject($returnMailSubject);
        $this->setReturnMailBody($returnMailBody);
        $this->setRequiredString('<string>(必須)</string>');
    }

    /**
     *
     * @param MailFormItem $mailFormItem
     * @return type string
     */
    public function testRenderEdit($mailFormItem) {
        return $mailFormItem->label;
    }

    public function errorCheck() {
        return MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, 'テスト', '#1#のエラーです');
    }

    /**
     * エラーメッセージ用の配列を置換・追加する
     * 
     * @return array 
     */
    protected function getAdditionalErrorMessages() {
        //$additionalMessages['required'] = 'エラーです。';
        //return $additionalMessages;
        return array();
    }

}