<?php
/**
 * GeminiVisionService.php
 * Service class for communicating with Google Gemini Vision API (Gemini 1.5 Flash)
 * Used for Visual Product Recognition in Dong Khoi Store
 */

class GeminiVisionService {
    private $apiKey;
    private $model;

    public function __construct($apiKey = null, $model = null) {
        $this->apiKey = $apiKey ?: ($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '');
        $this->model  = $model ?: ($_ENV['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?? 'gemini-1.5-flash');
    }

    /**
     * Check if Gemini API key is configured
     */
    public function isConfigured() {
        return !empty($this->apiKey) && trim($this->apiKey) !== 'your-gemini-api-key-here';
    }

    /**
     * Analyze image with Gemini Vision to extract product metadata
     * 
     * @param string $imagePath Local absolute file path or temporary upload path
     * @param string $mimeType Mime type (e.g. image/jpeg, image/png, image/webp)
     * @return array ['success' => bool, 'data' => array, 'raw' => string, 'error' => string]
     */
    public function identifyProductFromImage($imagePath, $mimeType = 'image/jpeg') {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'GEMINI_API_KEY_MISSING',
                'message' => 'GEMINI_API_KEY is not configured in .env file. Please add your API key from Google AI Studio.'
            ];
        }

        if (!file_exists($imagePath)) {
            return [
                'success' => false,
                'error' => 'FILE_NOT_FOUND',
                'message' => 'Uploaded image file not found.'
            ];
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            return [
                'success' => false,
                'error' => 'CANNOT_READ_FILE',
                'message' => 'Unable to read the image file content.'
            ];
        }

        // Detect mime type if not provided or generic
        if (empty($mimeType) || $mimeType === 'application/octet-stream') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $imagePath);
            finfo_close($finfo);
            if ($detectedMime) {
                $mimeType = $detectedMime;
            } else {
                $mimeType = 'image/jpeg';
            }
        }

        $base64Image = base64_encode($imageData);

        $prompt = "You are an AI product recognition assistant for Dong Khoi Store, a B2B Wholesale & Canteen Marketplace (food, beverages, instant noodles, canned goods, snacks, retail goods).

Your task is to identify the EXACT product shown in the image.

Rules:
1. Prioritize standard English or international brand and product naming (e.g., 'Hao Hao Hot & Sour Shrimp Noodles', 'Zero Degree Green Tea', 'Coca-Cola', 'Pepsi', 'Ha Long Canned Tuna', 'Duc Viet Sausages', 'Vfresh Fruit Juice', 'ST25 Rice', 'Red Bull Energy Drink').
2. Identify brand, category, and main searchable keywords.
3. If unsure of the exact variant, return the most prominent brand and item type.
4. Output MUST be valid JSON conforming to the schema below.

JSON Schema:
{
    \"primary_keyword\": \"Primary product search keyword in English\",
    \"product_name\": \"Full English product name\",
    \"brand\": \"Brand name (e.g. Acecook, Coca-Cola, Tan Hiep Phat, Vinamilk, Trung Nguyen, Vissan)\",
    \"category\": \"Category name (Beverages, Canned Foods, Dry Foods & Packaged Goods, Chilled & Fresh Packaged Foods, Other)\",
    \"confidence\": 0.95,
    \"features\": \"Brief note on packaging, volume or flavor if recognized\"
}";

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($this->model) . ":generateContent?key=" . urlencode($this->apiKey);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 512,
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local Laragon environments

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error' => 'CURL_ERROR',
                'message' => 'Connection error to Gemini API: ' . $curlError
            ];
        }

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $msg = $errData['error']['message'] ?? ('Gemini API Error (HTTP ' . $httpCode . ')');
            return [
                'success' => false,
                'error' => 'API_HTTP_ERROR',
                'http_code' => $httpCode,
                'message' => $msg,
                'raw' => $response
            ];
        }

        $resJson = json_decode($response, true);
        $rawText = $resJson['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($rawText)) {
            return [
                'success' => false,
                'error' => 'EMPTY_RESPONSE',
                'message' => 'The AI model returned an empty recognition response.'
            ];
        }

        // Clean any accidental markdown wrappers (```json ... ```)
        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

        $parsedData = json_decode($cleanJson, true);
        if (!$parsedData) {
            return [
                'success' => false,
                'error' => 'JSON_PARSE_ERROR',
                'message' => 'Unable to parse AI JSON response.',
                'raw_text' => $rawText
            ];
        }

        return [
            'success' => true,
            'data' => $parsedData,
            'raw_text' => $rawText
        ];
    }
}
