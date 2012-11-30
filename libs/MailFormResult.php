<?php

require_once('MailFormError.php');
require_once('MailFormItem.php');

/**
 * メールフォームの処理を行った結果
 * 
 * @author Daisuke.Itani
 * @version 1.0
 * @package com.irohacreative.form.mailForm
 */
class MailFormResult {

    /**
     *
     * @var MailFormSetting
     */
    protected $mailFormSetting;
    
    /**
     *
     * @var array 
     */
    protected $errors = array();
    
    /**
     *
     * @var array
     */
    protected $renderResult = array();
    
    /**
     *
     * @var array
     */
    protected $values = array();
    
    /**
     *
     * @var string
     */
    protected $ticket = '';
    
    /**
     *
     * @var string 
     */
    protected $pageMode;

    /**
     * コンストラクタ
     * 
     * @param MailFormSetting $mailFormSetting 
     */
    public function __construct($mailFormSetting) {
        $this->mailFormSetting = $mailFormSetting;
    }

    /**
     * フォーム内容についてエラーが発生しているか
     * 
     * @return bool 
     */
    public function hasError() {
        if (count($this->errors) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * エラーをセットする
     * 
     * @param string $name
     * @param array $errorArray 
     */
    public function setErrorArray($name, $errorArray) {
        $this->errors[$name] = $errorArray;
    }
    
    /**
     * すべてのエラーを返す
     * @return array
     */
    public function getErros() {
        return $this->errors;
    }
    
    /**
     * errorsからエラーを表示するHTMLを返す
     * 
     * 第一引数と第二引数で挟まれた形で返される
     * 
     * @param string $startTag
     * @param string $endTag
     * @return string 
     * @see getErrorHTML
     */
    public function getErrorsHTML($startTag, $endTag) {
        $HTML = '';
        foreach ($this->errors as $key => $itemErrors) {
            foreach ($itemErrors as $mailFormError) {
                $HTML .= $this->getErrorHTML($key, $mailFormError, $startTag, $endTag);
            }
        }
        return $HTML;
    }

    /**
     * MailFormErrorをもとにHTML形式にしたエラーメッセージを返す
     * 
     * @param string $key
     * @param MailFormError $mailFormError
     * @param string $startTag
     * @param string $endTag
     * @return string 
     */
    protected function getErrorHTML($key, $mailFormError, $startTag, $endTag) {
        $erroeMesseageSetting = $this->mailFormSetting->getErrorMessageSetting();
        if (isset($erroeMesseageSetting[$mailFormError->errorCode])) {
            $formScheme = $this->mailFormSetting->getFormScheme();
            if (isset($mailFormError->originalErrorMessage) && $mailFormError->originalErrorMessage != FALSE) {
                $errorMessage = $mailFormError->originalErrorMessage;
            }
            else {
                $errorMessage = $erroeMesseageSetting[$mailFormError->errorCode];
            }
            $i = 1;
            foreach ($mailFormError->argument as $replaceValue) {
                $errorMessage = str_replace('#' . $i . '#', $replaceValue, $errorMessage);
                $i++;
            }
            $HTML = $startTag . $errorMessage . $endTag;
        }
        else {
            die('不正なエラーコードです');
        }
        return $HTML;
    }

    /**
     * HTMLの形にレンダリングした内容をセット
     * 
     * @param int $groupID
     * @param string $HTML 
     */
    public function setRenderResult($groupID, $HTML) {
        $this->renderResult[$groupID][] = $HTML;
    }

    /**
     * セットされたレンダリング結果をすべて返す
     * 
     * @return array 
     */
    public function getRenderResult() {
        return $this->renderResult;
    }

    /**
     * セットされたレンダリング結果をHTMLとして返す
     * 
     * @return string 
     */
    public function getRenderResultHTML() {
        $HTML = '';
        foreach ($this->renderResult as $groupResult) {
            foreach ($groupResult as $result) {
                $HTML .= $result;
            }
        }
        return $HTML;
    }

    /**
     * グループIDごとセットされたレンダリング結果をHTMLとして返す
     * 
     * @param int $groupID
     * @return string 
     */
    public function getRenderResultHTMLWithGroupID($groupID) {
        if (!isset($this->renderResult[$groupID])) {
            return FALSE;
        }
        $HTML = '';
        foreach ($this->renderResult[$groupID] as $result) {
            $HTML .= $result;
        }
        return $HTML;
    }

    /**
     * フォームの入力値をセットする
     * 
     * @param string $name
     * @param string $value 
     */
    public function setValues($name, $value) {
        $this->values[$name] = $value;
    }

    /**
     * フォームの入力値からhiddenタグを返す
     * 
     * @return string 
     */
    public function getValueForHiddenHTML() {
        $buff = '<div id="hiddens">' . "\n";
        foreach ($this->values as $key => $value) {
            if (is_array($value)) {
                $i = 0;
                foreach ($value as $item) {
                    $item = htmlentities($item, ENT_QUOTES, "UTF-8");
                    $buff .= '<input type="hidden" name="' . $key . '[' . $i . ']" id="' . $key . '[' . $i . ']" value="' . $item . '" />' . "\n";
                    $i++;
                }
            }
            else {
                $value = htmlentities($value, ENT_QUOTES, "UTF-8");
                $buff .= '<input type="hidden" name="' . $key . '" id="' . $key . '" value="' . $value . '" />' . "\n";
            }
        }
        $buff .= '<input type="hidden" name="ticket" value="' . $this->getTicket() . '" />' . "\n";
        $buff .= '</div>' . "\n";
        return $buff;
    }
    
    /**
     * CSRF防止のためのticketをセット
     * 
     * @param type $tiket 
     */
    public function setTicket($tiket) {
        $this->ticket = $tiket;
    }
    
    /**
     * CSRF防止のためのticketを返す
     * 
     * @return type 
     */
    public function getTicket() {
        return $this->ticket;
    }
    
    /**
     * 現在のページモードをセットする
     * 
     * @param string $pageMode 
     */
    public function setPageMode($pageMode) {
        $this->pageMode = $pageMode;
    }
    
    /**
     * 現在のページモードを返す
     * 
     * @return string 
     */
    public function getPageMode() {
        return $this->pageMode;
    }

}