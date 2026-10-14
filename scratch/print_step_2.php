<?php
$transcript = 'C:/Users/Lam Loan/.gemini/antigravity-ide/brain/ccf5706a-1b8b-4bee-a683-0f470034a2f4/.system_generated/logs/transcript.jsonl';
$f = fopen($transcript, 'r');

while (($line = fgets($f)) !== false) {
    $data = json_decode($line, true);
    if (isset($data['step_index']) && $data['step_index'] == 159) {
        $c = $data['content'];
        $pos2 = strpos($c, '## 📌 BƯỚC 2:');
        $pos3 = strpos($c, '## 📌 BƯỚC 3:');
        if ($pos2 !== false && $pos3 !== false) {
            echo substr($c, $pos2, $pos3 - $pos2);
        } else {
            echo substr($c, $pos2, 2000);
        }
        break;
    }
}
fclose($f);
