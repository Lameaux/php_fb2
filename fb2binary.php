<?php

$fb2_file = 'book.fb2';
$id = $_GET['id'];

$handle = fopen($fb2_file, "r");
if ($handle) {

    $binaryFound = false;
    $binaryContent = '';
    while (($line = fgets($handle)) !== false) {
    
        if (!$binaryFound && strstr($line, "<binary") && strstr($line, $id)) {
            $binaryFound = true;
        }
        
        if ($binaryFound) {
            $binaryContent .= $line . "\r\n";
            if (strstr($line, "</binary>")) {
                break;
            }
        }
    }

    if ($binaryFound) { 
        if (preg_match('@<binary([^>]*)>([^<]*)</binary>@is', $binaryContent, $matches)) {
            
            $content_type = 'image/jpeg';
            if (preg_match('@content-type="([^"]+)"@i', $matches[1], $matches2)) {
                $content_type = $matches2[1];
            }
            
            $base64 = $matches[2];
            $decoded = base64_decode($base64);
            header('Content-Type: ');
            header('Content-Length: ' . strlen($decoded));
            echo $decoded;    
        }
    }

    fclose($handle);
} 

