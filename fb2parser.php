<?php

$fb2_file = 'book.fb2';

$xml = new XMLReader();
$xml->open($fb2_file);

function readDescription($xml) {
    while($xml->read()) {
       switch($xml->nodeType) {
           case (XMLREADER::ELEMENT):
                  if ($xml->name == 'description') {
                    return $xml->readOuterXML();
                  }
           case (XMLREADER::END_ELEMENT):
                break;
       }
    }    
    return '';
}

$description_raw = readDescription($xml);

$description_xml = simplexml_load_string($description_raw);
$description_json = json_encode($description_xml);
$description = json_decode($description_json,TRUE);

function getBookTitle($description) {
    if (isset($description['title-info']['book-title'])) {
        return $description['title-info']['book-title'];
    }
    return '';
}

function getAuthorName($description) {
    $result = '';
    if (isset($description['title-info']['author'])) {
        $author = $description['title-info']['author'];
        $result .= isset($author['first-name']) ? $author['first-name'] . ' ' : '';
        $result .= isset($author['middle-name']) ? $author['middle-name'] . ' ' : '';
        $result .= isset($author['last-name']) ? $author['last-name'] . ' ' : '';
    }
    return trim($result);    
}


?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= getBookTitle($description) . ' - ' . getAuthorName($description) ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Pracujete se zpraseným kódem a už nemáte nervy? Počkejte s mazáním a refaktoringem! Nahrajte ho nejdřív sem, tím si zvednete náladu a pobavíte ostatní!">
</head>
<body>
<pre>
<?php
    print_r($description);
?>
</pre>
</body>
</html>