<?php

function addpadding($string, $blocksize = 32)
{
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= str_repeat(chr($pad), $pad);
    return $string;
}

function removepadding($string)
{
    $pad = ord(substr($string, -1));
    if ($pad < 1 || $pad > 32) {
        return $string; // No padding
    }
    return substr($string, 0, -$pad);
}

function checkCode(array $response, string $merchantHashKey, string $merchantHashIV)
{
    if ($response['Status'] !== 'SUCCESS') {
        return;
    }

    $result = is_array($response['Result'])
        ? $response['Result']
        : json_decode($response['Result'], true);

    if (!isset($result['MerchantID'], $result['MerchantOrderNo'], $result['InvoiceTransNo'], $result['TotalAmt'], $result['RandomNum'], $result['CheckCode'])) {
        return;
    }

    $checkCodeArr = [
        'MerchantID' => $result['MerchantID'],
        'MerchantOrderNo' => $result['MerchantOrderNo'],
        'InvoiceTransNo' => $result['InvoiceTransNo'],
        'TotalAmt' => $result['TotalAmt'],
        'RandomNum' => $result['RandomNum'],
    ];
    ksort($checkCodeArr);
    $checkStr = http_build_query($checkCodeArr);
    $checkCode = strtoupper(hash('sha256', 'HashIV='.$merchantHashIV.'&'.$checkStr.'&HashKey='.$merchantHashKey));

    logger()->debug('['.$response['Message'].'] checkCode: '.($checkCode === $result['CheckCode'] ? 'OK' : 'NG'));
}

function checkAlphanumericCode(array $response, string $merchantHashKey, string $merchantHashIV)
{
    if ($response['Status'] !== 'SUCCESS') {
        return;
    }

    $result = is_array($response['Result']) ? $response['Result'] : json_decode($response['Result'], true);

    if (!isset($result['AphabeticLetter'], $result['CompanyId'], $result['EndNumber'], $result['ManagementNo'], $result['StartNumber'], $result['CheckCode'])) {
        return;
    }

    $checkCodeArr = [
        'AphabeticLetter' => $result['AphabeticLetter'],
        'CompanyId' => $result['CompanyId'],
        'EndNumber' => $result['EndNumber'],
        'ManagementNo' => $result['ManagementNo'],
        'StartNumber' => $result['StartNumber'],
    ];
    ksort($checkCodeArr);
    $checkStr = http_build_query($checkCodeArr);
    $checkCode = strtoupper(hash('sha256', 'HashIV='.$merchantHashIV.'&'.$checkStr.'&HashKey='.$merchantHashKey));

    logger()->debug('['.$response['Message'].'] checkCode: '.($checkCode === $result['CheckCode'] ? 'OK' : 'NG'));
}

/**
 * 產⽣ check value
 *
 * @param  string  $string 原始字串
 * @param  string  $key 串接⾦鑰 Hash Key 值
 * @param  string  $iv 串接⾦鑰 Hash IV 值
 * @return string
 */
function checkValue($string = '', $key = '', $iv = '')
{
    return strtoupper(hash('sha256', "HashKey={$key}&{$string}&HashIV={$iv}"));
}
