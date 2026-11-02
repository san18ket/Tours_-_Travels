<?php
$lines = file('C:/Users/Lenovo/.gemini/antigravity/brain/bba33407-378b-43b1-a09e-6bea6dd0a85e/.system_generated/logs/transcript.jsonl');
foreach (array_slice($lines, -10) as $l) {
    $j = json_decode($l, true);
    echo "Step: " . ($j['step_index'] ?? '?') . " | Source: " . ($j['source'] ?? '?') . " | Type: " . ($j['type'] ?? '?') . "\n";
    if (!empty($j['content'])) {
        echo "  Content: " . substr(trim($j['content']), 0, 150) . "\n";
    }
    if (!empty($j['media'])) {
        echo "  Media: " . json_encode($j['media']) . "\n";
    }
}
