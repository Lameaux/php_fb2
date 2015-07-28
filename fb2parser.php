<?php

mb_internal_encoding('UTF-8');

$fb2_file = 'example.fb2';
//$fb2_file = 'book.fb2';

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

function getAuthorFullName($description) {
    return getAuthorFirstName($description) . ' ' . getAuthorLastName($description);    
}

function getAuthorFirstName($description) {
    if (isset($description['title-info']['author'])) {
        $author = $description['title-info']['author'];
        $result = '';
        $result .= isset($author['first-name']) ? $author['first-name'] . ' ' : '';
        $result .= isset($author['middle-name']) ? $author['middle-name'] . ' ' : '';
        return trim($result);
    }
    return '';        
}

function getAuthorLastName($description) {
    if (isset($description['title-info']['author'])) {
        $author = $description['title-info']['author'];
        return isset($author['last-name']) ? $author['last-name'] : '';
    }
    return '';        
}

function getBookGenres($description) {
    if (isset($description['title-info']['genre'])) {
        $genre = $description['title-info']['genre'];
        if (is_array($genre)) {
            return $genre;
        }
        return array( $genre );
    }
    return array();    
}

function getBookGenresNames($description) {
    $genres = getBookGenres($description);
    $names = array();
    foreach ($genres as $genre) {
        $names[] = mb_convert_case(str_replace('_', ' ', $genre), MB_CASE_TITLE);
    }
    return $names; 
}

function getAnnotations($description) {
    if (isset($description['title-info']['annotation']['p'])) {
        $annotations = $description['title-info']['annotation']['p'];
        if (is_array($annotations)) {
            return $annotations; 
        }
        return array($annotations); 
    }
    return array();
}

function getShortAnnotation($description, $limit=null) {
    $annotations = getAnnotations($description);
    if (count($annotations)) {
        $result = $annotations[0];
        $result = preg_replace('/\s+/iu', ' ', $result);
        $result = trim($result);
        if ($limit) {
            return mb_substr($result, 0, $limit);
        }
        return $result;
    }
    return '';
}

function getPublisher($description){
    if (isset($description['publish-info']['publisher'])) {
        return $description['publish-info']['publisher'];
    }
    return '';
}

function getPublishYear($description) {
    if (isset($description['publish-info']['year'])) {
        return $description['publish-info']['year'];
    }
    return '';
}

function getIsbn($description) {
    if (isset($description['publish-info']['isbn'])) {
        return $description['publish-info']['isbn'];
    }
    return '';
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= getBookTitle($description) . ' - ' . getAuthorFullName($description) ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="<?= implode(', ', getBookGenres($description)) ?>">
<meta name="description" content="<?= getShortAnnotation($description, 256) ?>">
</head>
<body>
<h1><?= getBookTitle($description) . ' - ' . getAuthorFullName($description) ?></h1>
<p><?= getAuthorLastName($description) ?>, <?= getAuthorFirstName($description) ?></p>
<p><?= implode(', ', getBookGenresNames($description)) ?></p>
<?php foreach (getAnnotations($description) as $annotation) { ?>
<p><?= preg_replace('/\s+/iu', ' ', $annotation) ?></p>
<?php } ?>
<p><?= getPublisher($description) ?> <?= getPublishYear($description) ?></p>
<p><?= getIsbn($description) ?></p>
<pre>
<?php
    print_r($description);
?>
</pre>
</body>
</html>