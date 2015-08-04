<?php

mb_internal_encoding('UTF-8');

//$fb2_file = 'example.fb2';
$fb2_file = 'book.fb2';

$html = '';
$skip = 0;
$limit = 10000;

$handle = fopen($fb2_file, "r");
if ($handle) {

    $bodyFound = false;
    $lineNum=0;
    while (($line = fgets($handle)) !== false) {
    
        if (!$bodyFound && strstr($line, "<body")) {
            $bodyFound = true;
            continue;
        }
        
        if ($bodyFound) {
          $lineNum++;  
        
            if ($lineNum < $skip) {
                continue;
            }
          
            if ($lineNum > $skip + $limit) {
                break;
            }          
          
            if (strstr($line, "</body>")) {
                break;
            }
            
            $html .= transform($line) . "\r\n";
        
        }
         
        
    }

    fclose($handle);
} else {
    $html = 'Not found';
} 

function transform($line) {

    $line = preg_replace_callback('#<(/)?([a-z\-]+)(\s+.*|/)?>#', function($matches){
        
        $tag = strtolower($matches[2]);
        $closing = $matches[1];
        $attribute = isset($matches[3]) ? $matches[3] : '';
        
        if ($tag == 'empty-line') {
            return '<br>';
        }
        if ($tag == 'title') {
            return '<' . $closing . 'h3>';
        }

        if ($tag == 'subtitle') {
            return '<' . $closing . 'h4>';
        }

        if ($tag == 'a') {
            return '<' . $closing . 'a href="#">';
        }

        if ($tag == 'image') {
            return '<img alt="Image">';
        }

        // divs
        $divs = array('epigraph', 'annotation', 'cite', 'poem', 'history', 'section');         
        if (in_array($tag, $divs)) {
            if ($closing) {
                return '</div>';
            } else {
                return '<div class="' . $tag .'">';
            }
        }

        // paragraphs
        $paragraphs = array('text-author');
        if (in_array($tag, $paragraphs)) {
            if ($closing) {
                return '</p>';
            } else {
                return '<p class="' . $tag .'">';
            }
        }
        
        // spans
        $spans = array('emphasis');
        if (in_array($tag, $spans)) {
            if ($closing) {
                return '</span>';
            } else {
                return '<span class="' . $tag .'">';
            }
        }
        

                    
        return '<' . $closing . $tag . $attribute . '>';
    }, $line);

    return $line;
}


?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>BOOK</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php
    
//echo '<pre>' . htmlspecialchars($html) . '</pre>';
echo $html;    
    
?>
</pre>
</body>
</html>