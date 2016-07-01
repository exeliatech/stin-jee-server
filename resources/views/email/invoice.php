Invoice details<br>

Contact email: <?=$transaction->email?><br>
Company name: <?=$transaction->company?><br>
Store name: <?=$transaction->store?><br>
Address: <?=$transaction->address?><br>
City: <?=$transaction->city?><br>
Province: <?=$transaction->province?><br>
Postal ID: <?=$transaction->postal_id?><br>
Fiscal ID/VAT: <?=$transaction->fiscal_id?><br>
<br>
<br>

Tokens: <?=$transaction->tokens?><br>
Commercial or complementary: <?=($transaction->promotion? "complementary" : "commercial")?><br>
<br>
<br>
Date: <?=date('r')?><br>
Batch ID: <?=$transaction->batch_id?><br>
StinJee TransactionID: <?=$transaction->object_id?><br>
Download tokens: <?=url('/')?>/#!/payment_success/<?=$transaction->batch_id?>/<?=$transaction->object_id?>