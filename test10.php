<?php

$parameter = array(	
    "vendor_Key"		=> "Rj5ZmcNY5KaG1q",
    "vendor_IV"         => "AKiiPeN8NwWgch0KXT2t",
    "PRODUCT_NAME"      => "商家商品 I",
    "game_id"           => 24,
    "language"          => "CNY"
);

$xml = new SimpleXMLElement('<TRANS/>');
//array_walk_recursive($parameter, array ($xml, 'addChild'));
array_walk_recursive($parameter, function($value, $key)use($xml){
    $xml->addChild($key, $value);
});
print $xml->asXML();