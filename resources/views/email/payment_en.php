Payment details<br/>
<br/>
Status: Success<br/>
Name: <?=$info['first_name']?> <?=$info['last_name']?><br/>
PayPal Payer ID: <?=$info['PayerID']?> <br/>
Tokens bought: <?=$info['tokensNum']?><br/>
BatchID: <?=$transaction->batch_id?><br/>
Price: <?=$info['price']?> EUR<br/>
Date: <?= date('r') ?><br/>
StinJee TransactionID: <?=$transaction->object_id?><br/>
Information: <?=url('/')?>/#!/payment_success/<?=$transaction->batch_id?>/<?=$transaction->object_id?><br/>
<br/>
<br/>
Payer<br/>
<br/>
Email: <?=$info['email']?> <br/>

<?=($info['company'] ? "<br/>
Company: {$info['company']}" : "") ?><br/>
Country: <?=$info['country_code']?><br/>