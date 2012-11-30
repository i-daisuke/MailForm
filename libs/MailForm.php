<?php

require_once('MailFormResult.php');
require_once('MailFormItem.php');

/**
 * メールフォームを自動生成し、入力からメール送信までの一連の流れを行う
 * 
 * @author Daisuke.Itani
 * @version 1.0
 * @package com.irohacreative.form.mailForm
 */
class MailForm {
    
    /**
     * @var MailFormSetting 
     */
    protected $mailFormSetting;
    
    /**
     * MailFormErrorを格納する
     * 
     * @var array 
     */
    protected $errors;
    
    /**
     * $_POSTをラッピング
     * 
     * @var array
     */
    protected $post;
    
    /**
     * @var MailFormResult 
     */
    protected $result;

    /**
     * コンストラクタ
     * 
     * @param MailFormSetting $mailFormSetting
     */
    public function __construct(MailFormSetting $mailFormSetting) {
        $this->mailFormSetting = $mailFormSetting;
        $this->result = new MailFormResult($mailFormSetting);
        if ($this->mailFormSetting->isDebugMode() === TRUE) {
            error_reporting(E_ALL);
        }
        else {
            error_reporting(0);
        }
    }
    
        
    public function getAllPost(){
        return $this->post;
    }

    /**
     * フォーム関連処理を行う前処理を行う
     */
    protected function onInit() {
        if (method_exists($this->mailFormSetting, 'willInit')) {
            $this->mailFormSetting->willInit($this);
        }
        if (method_exists($this->mailFormSetting, 'didInit')) {
            $this->mailFormSetting->didInit($this);
        }
    }

    /**
     * フォーム入力画面表示時の処理を行う
     */
    protected function onEdit() {
        if (method_exists($this->mailFormSetting, 'willEdit')) {
            $this->mailFormSetting->willEdit($this);
        }
        $this->preRenderFrom();
        if (method_exists($this->mailFormSetting, 'didEdit')) {
            $this->mailFormSetting->didEdit($this);
        }
    }

    /**
     * フォーム確認画面表示時の処理を行う
     */
    protected function onConfirm() {
        if (method_exists($this->mailFormSetting, 'willConfirm')) {
            $this->mailFormSetting->willConfirm($this);
        }
        $this->preRenderConfirm();
        if (method_exists($this->mailFormSetting, 'didConfirm')) {
            $this->mailFormSetting->didConfirm($this);
        }
    }

    /**
     * フォーム送信時の処理を行う
     */
    protected function onSubmit() {
        if (method_exists($this->mailFormSetting, 'willSubmit')) {
            $this->mailFormSetting->willSubmit($this);
        }

        //メール送信処理
        $returnMailBody = $this->getMailBodyWithTemplate($this->mailFormSetting->getReturnMailBody());
        $adminMailBody = $this->getMailBodyWithTemplate($this->mailFormSetting->getAdminMailBody());

        //メール送信を実行
        //デバッグ用のアドレスが指定されている場合は上書き
        if ($this->mailFormSetting->isDebugMode() === TRUE && $this->mailFormSetting->getDebugAdminMail() !== FALSE) {
            $adminMail = $this->mailFormSetting->getDebugAdminMail();
        }
        else {
            $adminMail = $this->mailFormSetting->getAdminMail();
        }

        //メールの送信先 複数指定の場合
        if (is_array($adminMail)) {
            $selectForMailKey = $this->getPost($adminMail['selKey']);
            if (isset($adminMail['mailList'][$selectForMailKey])) {
                $fixedAdminMail = $adminMail['mailList'][$selectForMailKey];
            }
            elseif (isset($adminMail['mailList']['default'])) {
                $fixedAdminMail = $adminMail['mailList']['default'];
            }
            else {
                die("メールの送信に失敗しました。");
            }
        }
        else {
            $fixedAdminMail = $adminMail;
        }
        $this->mailFormSetting->setAdminMail($fixedAdminMail);
        $this->sendMailReturn($returnMailBody);
        $this->sendMailAdmin($adminMailBody);
		
		if (method_exists($this->mailFormSetting, 'didSubmit')) {
            $this->mailFormSetting->didSubmit($this);
        }

        if ($this->mailFormSetting->getThanksPageURL() !== FALSE) {
            $thanksPageURL = $this->mailFormSetting->getThanksPageURL();
            header("Location:{$thanksPageURL}");
            exit();
        }
        else {
            die("完了ページのURLが指定されていません");
        }

        
    }

    /**
     * 自動返信メールを送信する
     * 
     * @param string $body 
     */
    protected function sendMailReturn($body) {
        //自動返信
        if ($this->mailFormSetting->isReturnMail() === TRUE && $this->getPost($this->mailFormSetting->getReturnMailName()) !== FALSE) {
            //送信先メールアドレス
            $adminMails = explode(',', $this->mailFormSetting->getAdminMail());
            if ($this->mailFormSetting->getReturnMailFromAddress() == FALSE) {
                $fromAddress = $adminMails[0];
            }
            else {
                $fromAddress = $this->mailFormSetting->getReturnMailFromAddress();
            }
            $formTxt = $this->mailFormSetting->getReturnMailFromText();
            
            $subject = html_entity_decode($this->mailFormSetting->getReturnMailSubject(),ENT_QUOTES,'UTF-8');
            $body = html_entity_decode($body,ENT_QUOTES,'UTF-8');
            
            if (!mb_send_mail($this->getPost($this->mailFormSetting->getReturnMailName()), $subject, $body, "From: " . mb_encode_mimeheader("{$formTxt}") . " <{$fromAddress}>")) {
                die("メールの送信に失敗しました。");
            }
        }
    }

    /**
     * 管理者宛メールを送信する
     * 
     * @param string $body 
     */
    protected function sendMailAdmin($body) {
        //管理者へ送信
        $subject = html_entity_decode($this->mailFormSetting->getAdminMailSubject(),ENT_QUOTES,'UTF-8');
        $body = html_entity_decode($body,ENT_QUOTES,'UTF-8');
        if (!mb_send_mail($this->mailFormSetting->getAdminMail(), $subject, $body, "From: <" . $this->getPost($this->mailFormSetting->getReturnMailName()) . ">")) {
            die("メールの送信に失敗しました。");
        }
    }

    /**
     * 通常レンダリング
     */
    protected function preRenderFrom() {
        $this->renderForm();
    }

    /**
     * 確認画面レンダリング
     */
    protected function preRenderConfirm() {
        $this->renderConfirm();
        $this->setHiddenValue();
    }

    /**
     * フォームのの処理を行い結果を返す
     * 
     * @return MailFormResult 
     */
    public function doAction() {
        $this->onInit();
        if (isset($_POST)) {
            $this->setPost($_POST);
        }
        $this->errors = $this->check();
        //ticketチェック
        if ((isset($_SESSION['ticket']) && $this->getPost('ticket') !== FALSE) &&
                ($_SESSION['ticket'] != '' && $this->getPost('ticket') != '') &&
                $_SESSION['ticket'] == $this->getPost('ticket')
        ) {
            $isValidTicket = TRUE;
        }
        else {
            $isValidTicket = FALSE;
        }

        //確認画面
        if ($this->errors === FALSE && $this->getPost("confirm") !== FALSE) {
            $this->result->setPageMode('confirm');
            $this->onConfirm();
        }
        //送信完了
        elseif ($this->errors === FALSE && $this->getPost("submit") !== FALSE && $isValidTicket === TRUE) {
            $this->result->setPageMode('submit');
            $this->onSubmit();
        }
        //通常
        else {
            if ($this->getPost("back") !== FALSE || $this->getPost("confirm") === FALSE) {
                $this->errors = FALSE;
            }
            $this->result->setPageMode('edit');
            $this->onEdit();
        }
        return $this->result;
    }

    /**
     * フォーム入力画面を生成し、MailFormResultに結果をセットする
     */
    protected function renderForm() {
        $mailFormItemClassName = $this->mailFormSetting->getMailFormItemClassName();
        if ($this->errors !== FALSE) {
            foreach ($this->errors as $key => $errorArray) {
                $this->result->setErrorArray($key, $errorArray);
            }
        }
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $formItem = new $mailFormItemClassName;
            $formItem->setDataWithArray($element, $key, $this->getPost($key, TRUE), $this->mailFormSetting);
            if (!isset($element['group']) || $element['group'] == '') {
                $element['group'] = 0;
            }
            $this->result->setRenderResult($element['group'], $formItem->getEditHTML());
        }
    }

    /**
     * フォーム確認画面を生成し、MailFormResultに結果をセットする
     */
    protected function renderConfirm() {
        $mailFormItemClassName = $this->mailFormSetting->getMailFormItemClassName();
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $formItem = new $mailFormItemClassName;
            $formItem->setDataWithArray($element, $key, $this->getPost($key, TRUE), $this->mailFormSetting);
            if (!isset($element['group']) || $element['group'] == '' || is_null($element['group'])) {
                $element['group'] = 0;
            }
            $this->result->setRenderResult($element['group'], $formItem->getConfirmHTML());
        }
        $ticket = sha1(time() . '48dn0wj30^93n4i9f');
        $_SESSION['ticket'] = $ticket;
        $this->result->setTicket($ticket);
    }

    /**
     * MailFormResultにhidden用の値をセットする
     */
    protected function setHiddenValue() {
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $this->result->setValues($key, $this->getPost($key));
        }
    }

    /**
     * フォーム入力内容のチェックを実行する。エラーがない場合はFALSE、エラーがある場合はMailFormErrorオブジェクトを返す
     * 
     * @return MailFormError
     * @see checkImpl
     */
    protected function check() {
        return $this->checkImpl();
    }

    /**
     * mailFormSettingのエラーを確認し結果を返す
     * 
     * @return MailFormError 
     */
    protected function checkImpl() {
        $error = array();
        $mailFormItemClassName = $this->mailFormSetting->getMailFormItemClassName();
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $formItem = new $mailFormItemClassName;
            $formItem->setDataWithArray($element, $key, $this->getPost($key, TRUE), $this->mailFormSetting);
            $hasError = $formItem->hasError();
            if ($hasError !== FALSE) {
                $error[$key] = $formItem->hasError();
            }
        }
        foreach ($error as $itemError) {
            if ($itemError !== FALSE) {
                return $error;
            }
        }
        return FALSE;
    }

    /**
     * POST値をセットする
     * 
     * @param array $post 
     */
    protected function setPost($post) {
        $this->post = $post;
    }

    /**
     * 指定のPOST値を返す。
     * 
     * HTMLをエスケープしたい場合は第二引数にTRUEを渡す。
     * POST値が配列の場合第三引数でキーを渡すことで特定の値を取り出すことができる。
     * 
     * @param string $key
     * @param bool $htmlentitie
     * @param string $arrayKey
     * @return mixed 
     */
    protected function getPost($key, $htmlentitie = FALSE, $arrayKey = FALSE) {
        if (isset($this->post[$key])) {
            if (is_array($this->post[$key]) && $arrayKey !== FALSE) {
                if ($htmlentitie === TRUE) {
                    return htmlentities($this->post[$key][$arrayKey], ENT_QUOTES, "UTF-8");
                }
                return $this->post[$key][$arrayKey];
            }
            elseif (is_array($this->post[$key]) && $arrayKey === FALSE) {
                if ($htmlentitie === FALSE) {
                    return $this->post[$key];
                }
                else {
                    $returnArray = array();
                    foreach ($this->post[$key] as $ele) {
                        $returnArray[] = htmlentities($ele, ENT_QUOTES, "UTF-8");
                    }
                    return $returnArray;
                }
            }
            else {
                if ($htmlentitie === TRUE) {
                    return htmlentities($this->post[$key], ENT_QUOTES, "UTF-8");
                }
                return $this->post[$key];
            }
        }
        return FALSE;
    }


    /**
     * メール本文をテンプレートから生成する
     * 
     * @param string $template
     * @return string
     */
    protected function getMailBodyWithTemplate($template) {
        $mailBuff = '';
        $mailFormItemClassName = $this->mailFormSetting->getMailFormItemClassName();
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $formItem = new $mailFormItemClassName;
            $formItem->setDataWithArray($element, $key, $this->getPost($key, TRUE), $this->mailFormSetting);
            $mailBuff .= $formItem->getStringForMail();
        }
        foreach ($this->mailFormSetting->getFormScheme() as $key => $element) {
            $formItem = new $mailFormItemClassName;
            $formItem->setDataWithArray($element, $key, $this->getPost($key, TRUE), $this->mailFormSetting);
            $valueForTemplate = str_replace("\n", "", $formItem->getStringForMail(true));
            $template = mb_ereg_replace('##value_' . $key . '##', $valueForTemplate, $template);
        }
        return mb_ereg_replace("##formContent##", $mailBuff, $template);
    }

}