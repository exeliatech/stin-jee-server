Gentile <?=$info['first_name']?> <?=$info['last_name']?>,<br/>
<br/>
Grazie per aver acquistato un pacchetto di gettoni Stin Jee.<br/>
Il pacchetto e' allegato a questa email.<br/>
I dettagli della transazione sono:<br/>
<br/>
Dettagli del pagamento<br/>
<br/>
Stato: Success<br/>
Nome: <?=$info['first_name']?> <?=$info['last_name']?><br/>
Codice PayPal : <?=$info['PayerID']?> <br/>
Numero di gettoni: <?=$info['tokensNum']?><br/>
Codice del pacchetto gettoni: <?=$transaction->batch_id?><br/>
Prezzo (IVA inclusa): <?=$info['price']?> EUR<br/>
Data e ora: <?= date('r') ?><br/>
Codice transazione Stin Jee: <?=$transaction->object_id?><br/>
Conferma della transazione: <?=url('/')?>/#!/payment_success/<?=$transaction->batch_id?>/<?=$transaction->object_id?><br/>
<br/>
<br/>
Pagamento effettuato da:<br/>
<br/>
Email: <?=$info['email']?>  <?=($info['company'] ? "<br/>
Ragione sociale: {$info['company']}" : "") ?><br/>
Paese: <?=$info['country_code']?><br/>
<br/>
<br/>
Cordiali saluti<br/>
<br/>
Il team di Stin Jee Italia<br/>
<br/>
STIN JEE ITALIA<br/>
Largo Olgiata 15, isola 95C2<br/>
00123 Roma, Italy<br/>
Tel. +39  06 3088 9605<br/>
italia@stinjee.com