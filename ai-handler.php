<?php
// search-engine.php

// Enable CORS so your GitHub Pages frontend can access this backend securely
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header('Content-Type: application/json');

// Handle preflight browser security checks gracefully
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$userMessage = $_POST['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(["reply" => "Empty message array packet."]);
    exit;
}

// Fetch the Gemini API Key safely stored in the environment variables configuration panel
$apiKey = getenv('GEMINI_API_KEY'); 
$modelName = "gemini-2.5-flash";

if (!$apiKey) {
    echo json_encode(["reply" => "Error: Engine key environment context variable missing."]);
    exit;
}

if (strpos($userMessage, 'Generate a Top 10 webpage response array for:') !== false) {
    $topic = str_replace('Generate a Top 10 webpage response array for:', '', $userMessage);
    
    $systemPrompt = "You are a professional frontend web generator for the platform WEBREX. 
    The user searched for the topic: '$topic'. Generate a beautiful 'Top 10' layout page in raw HTML/CSS.
    Use an animated linear gradient background layout, modern flex/grid item cards, and smooth hover translations.
    Output ONLY functional raw code starting with <!DOCTYPE html>. No markdown ticks.";

    $postData = [
        "contents" => [["parts" => [["text" => $systemPrompt . "\n\nBuild layout now."]]]]
    ];

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . $modelName . ":generateContent?key=" . $apiKey;

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $generatedHTML = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $generatedHTML = preg_replace('/^```html\s*|```\s*$/i', '', trim($generatedHTML));

    echo json_encode(["html_payload" => $generatedHTML]);
    exit;
}

// General chatbot conversational fallback route
echo json_encode(["reply" => "Portal input processed successfully standard communication channels open."]);