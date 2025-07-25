

DB 規劃：
```
create_ezpay_invoices
create_ezpay_invoice_allowances
create_ezpay_invoice_schedules
```


```php
<?php

// 回傳
EzpayInvoice::invoice()->submit(); // array
EzpayInvoice::invoice()->generateForm($autoSubmit = true); // string
EzpayInvoice::invoice()->toFormData(); // array
```

```php
<?php

// 即時開立發票
EzpayInvoice::invoice()
    ->merchantOrderNo('Order'.time())
    ->category('B2C')
    ->buyer([
        'name' => 'John Doe',
        'email' => 'test@email.com',
    ])
    ->tax([
        'amount' => 0,
        'rate' => 0,
    ])
    ->items([
        [
            'name' => 'Product 1',
            'quantity' => 1,
            'price' => 1000,
        ],
    ])
    ->submit(); // array

// 等待觸發開立發票
EzpayInvoice::invoice()
    ->pending()
    // ...
    ->submit(); // array

// 預約自動開立發票
EzpayInvoice::invoice()
    ->pending()
    // ...
    ->submit(); // array

// 觸發開立發票
EzpayInvoice::invoice()
    ->trigger()
    ->invoiceTransNo('1234567890')
    ->merchantOrderNo('Order'.time())
    ->totalAmt(3000)
    ->submit(); // array

// 開立折讓
EzpayInvoice::allowance()
    ->invoiceNo('1234567890')
    ->merchantOrderNo('Order'.time())
    ->items([
        [
            'name' => 'Product 1',
            'quantity' => 1,
            'price' => 1000,
        ],
    ])
    ->totalAmt(3000)
    ->submit(); // array


```


