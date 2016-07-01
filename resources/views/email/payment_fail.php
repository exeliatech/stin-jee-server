<?=$error?>
<br>
<br>

Payment details<br/>
<br/>
Status: <?=$status?><br/>
Name: <?=(isset($info['first_name']) ? $info['first_name'] : "") ?> <?=(isset($info['last_name']) ? $info['last_name'] : "")?><br/>
PayPal Payer ID: <?=(isset($info['PayerID']) ? $info['PayerID'] : "")?> <br/>
Tokens bought: <?=(isset($info['tokensNum']) ? $info['tokensNum'] : "")?><br/>
Price: <?=(isset($info['price']) ? $info['price'] : "0")?> EUR<br/>
Date: <?= date('r') ?><br/>
<br/>
<br/>
Payer<br/>
<br/>
Email:<?=(isset($info['email']) ? $info['email'] : "")?> <br/>

<?=(isset($info['company']) ? "<br/>
Company: {$info['company']}" : "") ?><br/>
Country: <?=(isset($info['country_code']) ? $info['country_code'] : "")?><br/>