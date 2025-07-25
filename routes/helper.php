<?php

function addpadding($string, $blocksize = 32)
{
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= str_repeat(chr($pad), $pad);
    return $string;
}

function checkCode(array $response, string $merchantHashKey, string $merchantHashIV)
{
    if ($response['Status'] !== 'SUCCESS') {
        return;
    }

    $result = json_decode($response['Result'], true);

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
