<?php

require_once('MailFormError.php');

/**
 * メールフォームのセッティングを行う
 * 
 * @author Daisuke.Itani
 * @version 1.0
 * @package com.irohacreative.form.mailForm
 */
abstract class MailFormSetting {

    protected $debugMode = FALSE;
    protected $formScheme;
    protected $debugAdminMail;
    protected $adminMail;
    protected $adminMailSubject;
    protected $returnMailName;
    protected $returnMailFromAddress;
    protected $returnMailFromText;
    protected $returnMailSubject;
    protected $thanksPageURL = './thanks.html';
    protected $isReturnMail = TRUE;
    protected $requiredString = '<strong>※</strong>';

    abstract function __construct();

    //page mode delegate
    public function willInit($mailForm) {
        
    }

    public function didInit($mailForm) {
        
    }

    public function willEdit($mailForm) {
        
    }

    public function didEdit($mailForm) {
        
    }

    public function willConfirm($mailForm) {
        
    }

    public function didConfirm($mailForm) {
        
    }

    public function willSubmit($mailForm) {
        
    }

    public function DidSubmit($mailForm) {
        
    }

    //error messege
    public function getErrorMessageSetting() {
        $errorMessageSetting = $this->getErrorMessageDefultSettng();
        foreach ($this->getAdditionalErrorMessages() as $key => $value) {
            $errorMessageSetting[$key] = $value;
        }
        return $errorMessageSetting;
    }

    protected function getErrorMessageDefultSettng() {
        $messages = array();
        $messages['required'] = '#1#が未入力です。';
        $messages['requiredSelect'] = '#1#が選択されていません。';
        $messages['invalidType'] = '#1#が不正です。';
        $messages['IllegalChar'] = '#1#に不正な文字が含まれています。';
        $messages['tooLong'] = '#1#は#2#文字以内で入力してください。';
        $messages['tooShort'] = '#1#は#2#文字以上で入力してください。';
        $messages['length'] = '#1#は#2#文字で入力してください。';
        return $messages;
    }

    /**
     * エラーメッセージ用の配列を置換・追加する
     * 
     * @return array 
     */
    protected function getAdditionalErrorMessages() {
        return array();
    }

    // getter/setter
    public function getMailFormItemClassName() {
        return 'MailFormItem';
    }

    public function getFormScheme() {
        return $this->formScheme;
    }

    public function setFormScheme($formScheme) {
        $this->formScheme = $formScheme;
    }

    public function getAdminMailSubject() {
        return $this->adminMailSubject;
    }

    public function setAdminMailSubject($adminMailSubject) {
        $this->adminMailSubject = $adminMailSubject;
    }

    public function getDebugAdminMail() {
        return $this->debugAdminMail;
    }

    public function setDebugAdminMail($debugAdminMail) {
        $this->debugAdminMail = $debugAdminMail;
    }

    public function getAdminMail() {
        return $this->adminMail;
    }

    public function setAdminMail($adminMail) {
        $this->adminMail = $adminMail;
    }

    public function getReturnMailFromAddress() {
        return $this->returnMailFromAddress;
    }

    public function setReturnMailFromAddress($returnMailFromAddress) {
        $this->returnMailFromAddress = $returnMailFromAddress;
    }

    public function getReturnMailName() {
        return $this->returnMailName;
    }

    public function setReturnMailName($returnMailName) {
        $this->returnMailName = $returnMailName;
    }

    public function getReturnMailFromText() {
        return $this->returnMailFromText;
    }

    public function setReturnMailFromText($returnMailFromText) {
        $this->returnMailFromText = $returnMailFromText;
    }

    public function getReturnMailSubject() {
        return $this->returnMailSubject;
    }

    public function setReturnMailSubject($returnMailSubject) {
        $this->returnMailSubject = $returnMailSubject;
    }

    public function getReturnMailBody() {
        return $this->returnMailBody;
    }

    public function setReturnMailBody($returnMailBody) {
        $this->returnMailBody = $returnMailBody;
    }

    public function getAdminMailBody() {
        return $this->adminMailBody;
    }

    public function setAdminMailBody($adminMailBody) {
        $this->adminMailBody = $adminMailBody;
    }

    public function getThanksPageURL() {
        return $this->thanksPageURL;
    }

    public function setThanksPageURL($thanksPageURL) {
        $this->thanksPageURL = $thanksPageURL;
    }

    public function isReturnMail() {
        return $this->isReturnMail;
    }

    public function setIsReturnMail($isReturnMail) {
        $this->isReturnMail = $isReturnMail;
    }

    public function getRequiredString() {
        return $this->requiredString;
    }

    public function setRequiredString($requiredString) {
        $this->requiredString = $requiredString;
    }

    public function setDebugMode($flg) {
        if ($flg !== TRUE && $flg !== FALSE) {
            die('デバックモードの指定が不正です');
        }
        $this->debugMode = $flg;
    }

    public function isDebugMode() {
        return $this->debugMode;
    }

}