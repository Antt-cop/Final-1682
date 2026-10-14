<?php
$transcript = 'C:/Users/Lam Loan/.gemini/antigravity-ide/brain/ccf5706a-1b8b-4bee-a683-0f470034a2f4/.system_generated/logs/transcript.jsonl';
$f = fopen($transcript, 'r');

while (($line = fgets($f)) !== false) {
    $data = json_decode($line, true);
    if (isset($data['step_index']) && $data['step_index'] >= 156 && $data['step_index'] <= 165) {
        echo "=== STEP " . $data['step_index'] . " (" . $data['type'] . ") ===\n";
        if (isset($data['content'])) {
            echo mb_substr($data['content'], 0, 1500) . "\n\n";
        }
    }
}
fclose($f);
