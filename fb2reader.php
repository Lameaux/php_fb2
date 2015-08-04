<?php

$fb2_file = 'book.fb2';

$html = '';
$skip = 0;
$limit = 1000;

$handle = fopen($fb2_file, "r");
if ($handle) {

    $transformer = new Transformer();

    $bodyFound = false;
    $lineNum=0;
    while (($line = fgets($handle)) !== false) {
    
        if (!$bodyFound && strstr($line, "<body")) {
            $bodyFound = true;
            continue;
        }
        
        if ($bodyFound) {
          $lineNum++;  

            if (strstr($line, "</body>")) {
                break;
            }
        
            if ($lineNum < $skip) {
                continue;
            }
          
            if ($lineNum > $skip + $limit) {
                break;
            }          
          
            
            $html .= $transformer->transform($line) . "\r\n";

        }
        
    }

            // locate last </p>

    $firstP = strpos($html, '<p');            
    $lastP = strrpos($html, '</p>');
    if ($firstP !== FALSE && $lastP !== FALSE) {
        $html = substr($html, $firstP, $lastP + strlen('</p>'));    
    }

    fclose($handle);
} else {
    $html = 'Not found';
} 

class Transformer {

    private $stack = array();

private function replace($matches) {
        
        $tag = strtolower($matches[2]);
        $closing = $matches[1];
        $attribute = isset($matches[3]) ? $matches[3] : '';
        
        if ($tag == 'empty-line') {
            return '<br>';
        }

        if ($tag == 'image') {
            if (preg_match('|href="#([^"]+)"|', $attribute, $matches)) {
                return '<img alt="Image" src="/fb2binary.php?id=' . urlencode($matches[1]) . '" />';    
            }
        }

        // skips
        $skips = array('section', 'a');         
        if (in_array($tag, $skips)) {
            return '';
        }

        // parts
        $parts = array('epigraph', 'annotation', 'cite', 'poem', 'history', 'title', 'subtitle', 'stanza', 'poem');         
        if (in_array($tag, $parts)) {
            if ($closing) {
                $key = array_search($tag, $this->stack);
                if ($key !== FALSE) {
                    unset($this->stack[$key]);
                }
            } else {
                array_unshift($this->stack, $tag);
            }
            return '';
        }

        // paragraphs
        $paragraphs = array('text-author', 'p');
        if (in_array($tag, $paragraphs)) {
            if ($closing) {
                return '</p>';
            } else {
                $class = trim(implode('-', $this->stack) . ($tag != 'p' ? ' ' . $tag : '')) ;
                if ($class) {
                    return '<p class="' . trim($class) .'">';
                } else {
                    return '<p>';
                }
            }
        }
        
        // spans
        $spans = array('emphasis', 'strikethrough' , 'sub', 'sup', 'code', 'v');
        if (in_array($tag, $spans)) {
            if ($closing) {
                return '</span>';
            } else {
                $class = trim(implode('-', $this->stack) . ' ' . $tag);
                if ($class) {
                    return '<span class="' . trim($class) . '">';
                } else {
                }   return '</span>';
            }
        }
        

                    
        return '';
    }

public function transform($line) {

    $line = preg_replace_callback('@<(/)?([a-z\-]+)(\s+.*)?/?>@', array($this, 'replace') , $line);

    return $line;
}
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>BOOK</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    p.title {
        font-size:30px;
    }
    p.epigraph {
        font-style: italic;
    }
</style>
</head>
<body>
<?php
    
//echo '<pre>' . htmlspecialchars($html) . '</pre>';
echo $html;    
    
?>
</pre>
</body>
</html>