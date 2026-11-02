<?php
$lines = file('C:/Users/Lenovo/.gemini/antigravity/brain/bba33407-378b-43b1-a09e-6bea6dd0a85e/.system_generated/logs/transcript.jsonl');
for ($i = max(0, count($lines) - 80); $i < count($lines); $i++) {
    $j = json_decode($lines[$i], true);
    if (isset($j['type']) && strpos($j['type'], 'USER') !== false) {
        echo "Step: " . $j['step_index'] . " | Content: " . ($j['content'] ?? '') . "\n";
        if (isset($j['media'])) echo "Media: " . json_encode($j['media']) . "\n";
    }
}
