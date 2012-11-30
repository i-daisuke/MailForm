<?php

require_once('MailFormError.php');

/**
 * メールフォームの各要素を扱うクラス
 * 
 * @author Daisuke.Itani
 * @version 1.0
 * @package com.irohacreative.form.mailForm
 */
class MailFormItem {

    //type:input inputs tel post textarea select radio checkbox todofuken
    //validate:required mail exactMatch tel post items todofuken strlenMax.20 strlenMin.20
    //common
    public $key;
    public $value;
    public $label;
    public $labelAddtionalTxt;
    public $trAttribute;
    public $thAttribute;
    public $tdAttribute;
    public $type;
    public $validate = array();
    public $validateFnc;
    public $size;
    public $maxlength;
    public $attribute;
    public $afterTxt;
    public $firstTxt;
    public $group;
    //other type callback
    public $renderEditFnc;
    public $ValueToStringFnc;
    //display
    public $noDisplayMail;
    public $noDisplayConfirm;
    public $noDisplayEdit;
    //type 'radio','select','checkbox'
    public $items;
    public $default;
    //type 'select' , 'todofuken'
    public $optionFirst;
    //textarea
    public $cols;
    public $rows;
    //inputs
    public $numberOfInput;
    public $inputsTexts;
    public $inputsGlue;
    //error code
    const ERROR_REQUIRED = "required";
    const ERROR_REQUIRED_SELECT = "requiredSelect";
    const ERROR_INVALID_TYPE = "invalidType";
    const ERROR_ILLEGAL_CHAR = 'IllegalChar';
    const ERROR_TOO_LONG = "tooLong";
    const ERROR_TOO_SHORT = "tooShort";
    const ERROR_LENGTH = "length";

    /**
     *
     * @var MailFormSetting 
     */
    protected $mailFormSetting;

    /**
     *
     * @param string $key
     * @param array $mailFormScheme
     * @param value $value
     * @param MailFormSetting $mailFormSetting 
     */
    public function setDataWithArray($mailFormScheme, $key, $value, $mailFormSetting) {
        $this->key = $key;
        $this->value = $value;
        $this->mailFormSetting = $mailFormSetting;
        //バインド
        foreach ($mailFormScheme as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $mailFormScheme[$key];
            }
        }
    }

    public function getEditHTML() {
        if ($this->noDisplayEdit === TRUE) {
            return '';
        }
        $buff = '';
        $buff .= '<tr ' . $this->trAttribute . '>' . "\n";
        $requiredHTML = '';
        foreach ($this->validate as $valid) {
            if (strpos($valid, 'required') !== FALSE) {
                $requiredHTML = $this->mailFormSetting->getRequiredString();
            }
        }
        $buff .= '<th ' . $this->thAttribute . '>' . $this->label . $this->labelAddtionalTxt . $requiredHTML . '</th>' . "\n";
        $buff .= '<td ' . $this->tdAttribute . '>' . "\n";
        $renderConvertMethodName = 'getEditHTMLTypeOf_' . $this->type;
        if (isset($this->renderEditFnc) && is_callable($this->renderEditFnc)) {
            $buff .= call_user_func($this->renderEditFnc, $this);
        }
        elseif (method_exists($this, $renderConvertMethodName)) {
            $buff .= $this->$renderConvertMethodName();
        }
        else {
            die('MailFormItem:実装されていないtypeが指定されています');
        }

        $buff .= '</td>' . "\n";
        $buff .= '</tr>' . "\n";
        return $buff;
    }

    public function getConfirmHTML() {
        if ($this->noDisplayConfirm === TRUE) {
            return '';
        }
        $buff = '';
        $buff .= '<tr ' . $this->trAttribute . '>' . "\n";
        $requiredHTML = '';
        foreach ($this->validate as $valid) {
            if (strpos($valid, 'required') !== FALSE) {
                $requiredHTML = $this->mailFormSetting->getRequiredString();
            }
        }
        $buff .= '<th ' . $this->thAttribute . '>' . $this->label . $this->labelAddtionalTxt . $requiredHTML . '</th>' . "\n";
        $buff .= '<td ' . $this->tdAttribute . '>' . "\n";
        $renderConvertMethodName = 'getValueToStringTypeOf_' . $this->type;
        if (isset($this->ValueToStringFnc) && is_callable($this->ValueToStringFnc)) {
            $buff .= call_user_func($this->ValueToStringFnc, $this);
        }
        elseif (method_exists($this, $renderConvertMethodName)) {
            $buff .= $this->$renderConvertMethodName();
        }
        else {
            $buff .= $this->getValueToStringTypeOf_defult();
        }

        $buff .= '</td>' . "\n";
        $buff .= '</tr>' . "\n";
        return $buff;
    }

    public function getStringForMail($noTitle = false) {
        if ($this->noDisplayMail === TRUE) {
            return '';
        }
        $renderConvertMethodName = 'getValueToStringTypeOf_' . $this->type;
        $value = '';
        if (isset($this->ValueToStringFnc) && is_callable($this->ValueToStringFnc)) {
            $value .= call_user_func($this->ValueToStringFnc, $this);
        }
        elseif (method_exists($this, $renderConvertMethodName)) {
            $value .= $this->$renderConvertMethodName();
        }
        else {
            $value .= $this->getValueToStringTypeOf_defult();
        }
        if($noTitle){
            $returnValue = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "", $value);
        }   
        else{
            $returnValue = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "", $this->getFormatedMailString($value));
        }
            
        
        return $returnValue;
    }

    /**
     * メール本文の部分用文字列を整形して返す
     * 
     * @param string $value
     * @return string 
     */
    protected function getFormatedMailString($value) {
        return '■' . $this->label . '：' . $value . "\n";
    }

    public function hasError() {
        $error = array();
        //機種依存文字
        $value = $this->value;
        if (is_array($value)) {
            $validCharsValue = implode(',', $value);
        }
        else {
            $validCharsValue = $value;
        }
        $validChars = TRUE;
        $sjisStr = mb_convert_encoding($validCharsValue, 'SJIS-win', 'UTF-8');
        for ($i = 0; $i < mb_strlen($sjisStr, 'SJIS-win'); $i++) {
            $ch = mb_substr($sjisStr, $i, 1, 'SJIS-win');
            $hex = intval(bin2hex($ch), 16);
            if ($hex > 0x8540 && $hex < 0x889E) {
                $validChars = FALSE;
            }
        }
        if (strstr($validCharsValue, '\\') !== FALSE) {
            $validChars = FALSE;
        }
        if ($validChars === FALSE) {
            $error[] = MailFormError::initError(MailFormItem::ERROR_ILLEGAL_CHAR, $this->label);
        }

        if (!isset($this->validate)) {
            return FALSE;
        }
        foreach ($this->validate as $validate) {
            //必須
            if ($validate == 'required') {
                if($this->type == 'inputs' && is_array($value)){
                    $inputedValueCount = 0;
                    foreach($value as $item){
                        if($item != '' && !preg_match('/^[　| |\t|\n|\r|\0|\v]*$/u', $item)){
                            $inputedValueCount++;
                        }
                    }
                    if($this->numberOfInput != $inputedValueCount){
                        $error[] = MailFormError::initError(MailFormItem::ERROR_REQUIRED, $this->label);
                    }
                }
                if($this->type == 'tel' && is_array($value)){
                    $inputedValueCount = 0;
                    foreach($value as $item){
                        if($item != '' && !preg_match('/^[　| |\t|\n|\r|\0|\v]*$/u', $item)){
                            $inputedValueCount++;
                        }
                    }
                    if($inputedValueCount != 3){
                        $error[] = MailFormError::initError(MailFormItem::ERROR_REQUIRED, $this->label);
                    }
                }
                if($this->type == 'post' && is_array($value)){
                    $inputedValueCount = 0;
                    foreach($value as $item){
                        if($item != '' && !preg_match('/^[　| |\t|\n|\r|\0|\v]*$/u', $item)){
                            $inputedValueCount++;
                        }
                    }
                    if($inputedValueCount != 2){
                        $error[] = MailFormError::initError(MailFormItem::ERROR_REQUIRED, $this->label);
                    }
                }
                if ($value == '' || (!is_array($value) && preg_match('/^[　| |\t|\n|\r|\0|\v]*$/u', $value)) ) {
                    if (isset($this->items) || $this->type == 'todofuken') {
                        $error[] = MailFormError::initError(MailFormItem::ERROR_REQUIRED_SELECT, $this->label);
                    }
                    else {
                        $error[] = MailFormError::initError(MailFormItem::ERROR_REQUIRED, $this->label);
                    }
                }
            }
            //正規表現
            if (strpos($validate, 'regexCheck') !== FALSE) {
                $validateArray = explode('_', $validate);
                $regexType = $validateArray[1];
                $regex = $this->getRegexVar($regexType);
                mb_regex_encoding("UTF-8");
                if(is_array($value)){
                    $checkValue = implode('', $value);
                }
                else{
                    $checkValue = $value;
                }
                
                if ($checkValue != '' && mb_ereg($regex, $checkValue) === false) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
            }
            //マッチ
            //if (strpos($validate, 'exactMatch') !== FALSE) {
            //    $validateArray = explode('_', $validate);
            //    $targetName = $validateArray[1];
            //    if ($value != '' && $value != $this->getPost($targetName, FALSE)) {
            //        $formScheme = $this->mailFormSetting->getFormScheme();
            //        $error[] = array(MailFormItem::ERROR_NOT_EXACTMATCH,$this->label,);
            //    }
            //}
            //選択項目
            if ($validate == 'items') {
                if ($this->type == 'checkbox') {
                    $itemNoExists = FALSE;
                    if ($value != '') {
                        foreach ($value as $itenOne) {
                            if (!array_key_exists($itenOne, $this->items)) {
                                $itemNoExists = TRUE;
                            }
                        }
                        if ($itemNoExists === TRUE) {
                            $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                        }
                    }
                }
                else {
                    if ($value != '' && !array_key_exists($value, $this->items)) {
                        $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                    }
                }
            }
            //都道府県
            if ($validate == 'todofuken') {
                if ($value != '' && !array_key_exists($value, $this->getTodofukenArray())) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
            }
            //電話番号
            if ($validate == 'tel' && $value !== false) {
                if(!is_array($value) || count($value) != 3){
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
                $checkValue = implode('-', $value);
                if ($checkValue !='--' && mb_ereg($this->getRegexVar('telWithHyphen'), $checkValue) === false) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
            }
            //郵便番号
            if ($validate == 'post' && $value !== false) {
                if(!is_array($value) || count($value) != 2){
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
                $checkValue = implode('-', $value);
                if ($checkValue !='-' && mb_ereg($this->getRegexVar('zipcodeWithHyphen'), $checkValue) === false) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_INVALID_TYPE, $this->label);
                }
            }

            //最大文字数
            if (strpos($validate, 'strlenMax') !== FALSE) {
                $validateArray = explode('_', $validate);
                $num = $validateArray[1];
                if(is_array($value)){
                    $checkValue = implode('', $value);
                }
                else{
                    $checkValue = $value;
                }
                if (mb_strlen($checkValue) > $num) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_TOO_LONG, array($this->label, $num));
                }
            }

            //最小文字数
            if (strpos($validate, 'strlenMin') !== FALSE) {
                $validateArray = explode('_', $validate);
                $num = $validateArray[1];
                if(is_array($value)){
                    $checkValue = implode('', $value);
                }
                else{
                    $checkValue = $value;
                }
                if (mb_strlen($checkValue) < $num) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_TOO_SHORT, array($this->label, $num));
                }
            }

            //指定文字数
            if (strpos($validate, 'length') !== FALSE) {
                $validateArray = explode('_', $validate);
                $num = $validateArray[1];
                if(is_array($value)){
                    $checkValue = implode('', $value);
                }
                else{
                    $checkValue = $value;
                }
                if (mb_strlen($checkValue) != $num) {
                    $error[] = MailFormError::initError(MailFormItem::ERROR_LENGTH, array($this->label, $num));
                }
            }
        }

        if (isset($this->validateFnc) && is_callable($this->validateFnc)) {
            $res = call_user_func($this->validateFnc, $this);
            if ($res !== TRUE) {
                $error[] = $res;
            }
        }

        if (count($error) == 0) {
            return FALSE;
        }
        else {
            return $error;
        }
    }

    protected function getEditHTMLTypeOf_input() {
        $size = ($this->size != '') ? ' size="' . $this->size . '" ' : '';
        $maxlength = ($this->maxlength != '') ? ' maxlength="' . $this->maxlength . '" ' : '';
        return $this->firstTxt . '<input type="text" name="' . $this->key . '"  value="' . $this->value . '"' . $size . $maxlength . $this->attribute . ' />' . $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_radio() {
        $i = 0;
        $buff = $this->firstTxt;
        foreach ($this->items as $value => $item) {
            $checked = '';
            $radioPost = $this->value;
            if ($radioPost !== FALSE && $radioPost == $value) {
                $checked = 'checked = "checked"';
            }
            elseif ($radioPost === FALSE && isset($this->default) && $this->default == $value) {
                $checked = 'checked = "checked"';
            }
            $buff .= '<input type="radio" name="' . $this->key . '" id="form_' . $this->key . $i . '" value="' . $value . '" ' . $checked . ' ' . $this->attribute . ' /> <label for="form_' . $this->key . $i . '" >' . $item . '</label> ' . "\n";
            $i++;
        }
        return $buff .= $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_select() {
        $buff = $this->firstTxt . '<select name="' . $this->key . '" size="1" ' . $this->attribute . ' >';
        if (isset($this->optionFirst)) {
            $buff .= '<option value="">' . $this->optionFirst . '</option>';
        }
        $i = 0;
        foreach ($this->items as $value => $item) {
            $selected = '';
            $selectPost = $this->value;
            if ($selectPost !== FALSE && $selectPost == $value) {
                $selected = 'selected = "selected"';
            }
            elseif ($selectPost === FALSE && isset($this->default) && $this->default == $value) {
                $selected = 'selected = "selected"';
            }
            $buff .= '<option value="' . $value . '" ' . $selected . ' >' . $item . '</option>';
            $buff .= '' . "\n";
            $i++;
        }
        $buff .= '</select>' . $this->afterTxt;
        return $buff;
    }

    protected function getEditHTMLTypeOf_checkbox() {
        $i = 0;
        $buff = $this->firstTxt;
        foreach ($this->items as $value => $item) {
            $checked = '';
            $checkBoxPostArray = $this->value;
            if ($checkBoxPostArray !== FALSE && in_array($value, $checkBoxPostArray)) {
                $checked = 'checked = "checked"';
            }
            $buff .= '<input type="checkbox" id="form_' . $this->key . $i . '" name="' . $this->key . '[' . $i . ']" value="' . $value . '" ' . $checked . ' ' . $this->attribute . ' /><label for="form_' . $this->key . $i . '" >' . $item . '</label>　' . "\n";
            $i++;
        }
        return $buff .= $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_textarea() {
        $rows = ($this->rows != '') ? ' rows="' . $this->rows . '"' : '';
        $cols = ($this->cols != '') ? ' cols="' . $this->cols . '"' : '';
        return $this->firstTxt . '<textarea name="' . $this->key . '"' . $rows . $cols . $this->attribute . ' >' . $this->value . '</textarea>' . $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_todofuken() {
        $buff = $this->firstTxt . '<select name="' . $this->key . '" size="1" >';
        if (isset($this->optionFirst)) {
            $buff .= '<option value="">' . $this->optionFirst . '</option>';
        }
        $i = 0;
        $todofukenArray = $this->getTodofukenArray();
        foreach ($todofukenArray as $value => $item) {
            $selected = '';
            $selectPost = $this->value;
            if ($selectPost !== FALSE && $selectPost == $value) {
                $selected = 'selected = "selected"';
            }
            $buff .= '<option value="' . $value . '" ' . $selected . ' >' . $item . '</option>';
            $buff .= '' . "\n";
            $i++;
        }
        $buff .= '</select>' . $this->afterTxt;
        return $buff;
    }

    protected function getEditHTMLTypeOf_inputs() {
        if (!isset($this->numberOfInput)) {
            die('formSchemeにnumberOfInputが指定されていません');
        }
        if (!is_array($this->size) || !is_array($this->maxlength)) {
            die('typeがinputsの場合formSchemeのsize、maxlengthは配列である必要があります');
        }
        $inputs = array();
        for ($i = 0; $i < $this->numberOfInput; $i++) {
            $size = (isset($this->size[$i])) ? ' size="' . $this->size[$i] . '" ' : '';
            $maxlength = (isset($this->maxlength[$i])) ? ' maxlength="' . $this->maxlength[$i] . '" ' : '';
            $inputText = (isset($this->inputsTexts[$i])) ? $this->inputsTexts[$i] : '';
            $inputs[] = $inputText . '<input type="text" name="' . $this->key . '[' . $i . ']' . '"  value="' . $this->value[$i] . '"' . $size . $maxlength . $this->attribute . ' />';
        }
        $glue = (isset($this->inputsGlue)) ? $this->inputsGlue : '';
        return $this->firstTxt . implode($glue, $inputs) . $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_tel() {
        $buff = $this->firstTxt;
        $buff .= '<input type="text" name="' . $this->key . '[0]" id="form_' . $this->key . '_1" value="' . $this->value[0] . '" size="10" maxlength="5" /> - <input type="text" name="' . $this->key . '[1]" name="form_' . $this->key . '_2" value="' . $this->value[1] . '" size="7" maxlength="4" /> - <input type="text" name="' . $this->key . '[2]" name="form_' . $this->key . '_3" value="' . $this->value[2] . '" size="7" maxlength="4" />';
        return $buff .= $this->afterTxt;
    }

    protected function getEditHTMLTypeOf_post() {
        $buff = $this->firstTxt;
        $buff .= '<input type="text" name="' . $this->key . '[0]" id="form_' . $this->key . '_1" value="' . $this->value[0] . '" size="5" maxlength="3" /> - <input type="text" name="' . $this->key . '[1]" name="form_' . $this->key . '_2" value="' . $this->value[1] . '" size="7" maxlength="4" />';
        return $buff .= $this->afterTxt;
    }

    protected function getValueToStringTypeOf_defult() {
        return $this->value;
    }

    protected function getValueToStringTypeOf_tel() {
        if ($this->value == '' || count($this->value) != 3 || ($this->value[0] == '' || $this->value[1] == '' || $this->value[2] == '')) {
            return '';
        }
        return $this->value[0] . '-' . $this->value[1] . '-' . $this->value[2];
    }

    protected function getValueToStringTypeOf_post() {
        if ($this->value == '' || count($this->value) != 2 || ($this->value[0] == '' || $this->value[1] == '')) {
            return '';
        }
        return $this->value[0] . '-' . $this->value[1];
    }

    protected function getValueToStringTypeOf_textarea() {
        return nl2br($this->value);
    }

    protected function getValueToStringTypeOf_select() {
        return $this->getValueToStringWithArray($this->items);
    }

    protected function getValueToStringTypeOf_checkbox() {
        if ($this->value == '') {
            return '';
        }
        $buff = '';
        foreach ($this->value as $value) {
            $buff .= $value . ' ';
        }
        return $buff;
    }

    protected function getValueToStringTypeOf_inputs() {
        if ($this->value == '' || count($this->value) != $this->numberOfInput) {
            return '';
        }
        foreach ($this->value as $value) {
            if ($value == '') {
                return '';
            }
        }
        $glue = (isset($this->inputsGlue)) ? $this->inputsGlue : '';
        return implode($glue, $this->value);
    }

    protected function getValueToStringTypeOf_todofuken() {
        return $this->getValueToStringWithArray($this->getTodofukenArray());
    }

    protected function getValueToStringWithArray($array) {
        if ($this->value == '') {
            return '';
        }
        if (isset($array[$this->value])) {
            return $array[$this->value];
        }
        else {
            die('MailFormItem:不正な選択肢　が通過しています');
        }
    }

    protected function getTodofukenArray() {
        return array(
            '北海道' => '北海道',
            '青森県' => '青森県',
            '岩手県' => '岩手県',
            '宮城県' => '宮城県',
            '秋田県' => '秋田県',
            '山形県' => '山形県',
            '福島県' => '福島県',
            '茨城県' => '茨城県',
            '栃木県' => '栃木県',
            '群馬県' => '群馬県',
            '埼玉県' => '埼玉県',
            '千葉県' => '千葉県',
            '東京都' => '東京都',
            '神奈川県' => '神奈川県',
            '富山県' => '富山県',
            '石川県' => '石川県',
            '福井県' => '福井県',
            '新潟県' => '新潟県',
            '山梨県' => '山梨県',
            '長野県' => '長野県',
            '岐阜県' => '岐阜県',
            '静岡県' => '静岡県',
            '愛知県' => '愛知県',
            '三重県' => '三重県',
            '滋賀県' => '滋賀県',
            '京都府' => '京都府',
            '大阪府' => '大阪府',
            '兵庫県' => '兵庫県',
            '奈良県' => '奈良県',
            '和歌山県' => '和歌山県',
            '鳥取県' => '鳥取県',
            '島根県' => '島根県',
            '岡山県' => '岡山県',
            '広島県' => '広島県',
            '山口県' => '山口県',
            '徳島県' => '徳島県',
            '香川県' => '香川県',
            '愛媛県' => '愛媛県',
            '高知県' => '高知県',
            '福岡県' => '福岡県',
            '佐賀県' => '佐賀県',
            '長崎県' => '長崎県',
            '熊本県' => '熊本県',
            '大分県' => '大分県',
            '宮崎県' => '宮崎県',
            '鹿児島県' => '鹿児島県',
            '沖縄県' => '沖縄県'
        );
    }

    protected function getRegexVar($name) {
        static $regexVars = null;
        if (is_null($regexVars)) {
            $regexVars = array(
                'email' => '^[A-Za-z0-9_-]+[A-Za-z0-9_.-]*@[A-Za-z0-9_-]+[A-Za-z0-9_.-]*\.[A-Za-z]{2,5}$',
                'urlPath' => '^[0-9/a-zA-Z\-._?%:;=\&~]*$',
                'urlPart' => '^[0-9/a-zA-Z\-._?%;=\&~#!]*$',
                'tel' => '^0([0-9]{1,2}-?[0-9]{4,4}|[0-9]{2}-?[0-9]{3,3}|[0-9]{3,3}-?[0-9]{2,2})-?[0-9]{4,4}$',
                'telWithHyphen' => '^0([0-9]{2}-[0-9]{4,4}|[0-9]{1}-[0-9]{4,4}|[0-9]{2}-[0-9]{3,3}|[0-9]{3,3}-[0-9]{2,2})-[0-9]{4,4}$',
                'fax' => '^0([0-9]{1}-?[0-9]{4,4}|[0-9]{2}-?[0-9]{3,3}|[0-9]{3,3}-?[0-9]{2,2})-?[0-9]{4,4}$',
                'faxWithHyphen' => '^0([0-9]{2}-[0-9]{4,4}|[0-9]{1}-[0-9]{4,4}|[0-9]{2}-[0-9]{3,3}|[0-9]{3,3}-[0-9]{2,2})-[0-9]{4,4}$',
                'zipcode' => '^[0-9]{3,3}(-?[0-9]{4,4})?$',
                'zipcodeWithHyphen' => '^[0-9]{3,3}(-[0-9]{4,4})?$',
                'hiragana' => '^[あ-んー０１２３４５６７８９]+$',
                'katakana' => '^[ア-ンー０１２３４５６７８９]+$',
                'number' => '^[0-9]+$',
                'integer' => '^[0-9]+$',
                'numberNumeric' => '^[0-9]+(.[0-9]+)?$',
                'float' => '^[0-9]+(.[0-9]+)?$',
                'date' => '^[0-9]{4,4}\/[0-9]{1,2}\/[0-9]{1,2}$',
                'unquotedString' => '^[^"]+$',
                'boolean' => '^([tf]|[01])$',
                'alphabeticalId' => '^[0-9a-zA-Z]+$',
                'password' => '^[0-9a-zA-Z!+\-*#_]+$',
                'user_id' => '^[0-9a-zA-Z\-_]+$',
            );
        }

        if (isset($regexVars[$name])) {
            return $regexVars[$name];
        }
        else {
            return false;
        }
    }

}