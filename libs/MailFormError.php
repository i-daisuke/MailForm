<?php

/**
 * フォーム内容のエラー要素
 * 
 * @author Daisuke.Itani
 * @version 1.0
 * @package com.irohacreative.form.mailForm
 */
class MailFormError {
    
    /**
     *
     * @var string
     */
    public $errorCode;
    
    /**
     *
     * @var array
     */
    public $argument = array();
    
    /**
     *
     * @var string
     */
    public $originalErrorMessage;
    
    /**
     * フォームエラーオブジェクトを生成する
     * 
     * errorCodeはMailFormItemの定数もしくはオリジナルのものを指定。
     * argumentは配列を渡す(1つの場合は文字列で渡すことも可能)。argumentの値とエラーメッセージは数字(#1#)で連動しており置換される。
     * 独自のメッセージを指定したい場合はoriginalErrorMessageとして渡す。
     * 
     * 
     * @param string $errorCode
     * @param array $argument
     * @param string $originalErrorMessage
     * @return MailFormError 
     */
    static public function initError($errorCode, $argument, $originalErrorMessage = FALSE) {
        $mailFormError = new MailFormError;
        $mailFormError->errorCode = $errorCode;
        if (is_array($argument)) {
            $mailFormError->argument = $argument;
        }
        else {
            $mailFormError->argument = array($argument);
        }
        $mailFormError->originalErrorMessage = $originalErrorMessage;
        return $mailFormError;
    }

}