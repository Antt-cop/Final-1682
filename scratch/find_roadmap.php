<?php
$transcript = 'C:/Users/Lam Loan/.gemini/antigravity-ide/brain/ccf5706a-1b8b-4bee-a683-0f470034a2f4/.system_generated/logs/transcript.jsonl';
$f = fopen($transcript, 'r');
$plan_found = '';

while (($line = fgets($f)) !== false) {
    if (strpos($line, 'USER_INPUT') !== false && (strpos($line, 'sắp xếp') !== false || strpos($line, 'hướng đi') !== false)) {
        echo "Found prompt: " . mb_substr($line, 0, 200) . "\n";
    }
    if (strpos($line, 'BƯỚC 2') !== false || strpos($line, 'Bước 2:') !== false || strpos($line, 'Bước 2 -') !== false) {
        // Find model response
        $data = json_decode($line, true);
        if (isset($data['content'])) {
            echo "MATCH: " . mb_substr($data['content'], 0, 500) . "\n---\n";
        }
    }
}
fclose($f);
