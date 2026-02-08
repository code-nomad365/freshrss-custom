<?php

final class AIHelper
{
    private static string $default_model = "gpt-4o-mini";
    private static int $default_token_limit = 4096;

    private static function getCachePath(string $key): string {
        return "/tmp/freshrss_ai_" . md5($key) . ".json";
    }

    private static function getCachedResponse(string $key): ?string {
        $path = self::getCachePath($key);
        if (file_exists($path) && (time() - filemtime($path) < 86400)) {
            return file_get_contents($path);
        }
        return null;
    }

    private static function saveCachedResponse(string $key, string $json): void {
        file_put_contents(self::getCachePath($key), $json);
    }

    public static function generateArticleData(
        string $baseUrl,
        string $apiKey,
        string $model,
        float  $temperature,
        string $systemMessage,
        string $userPrompt,
        int    $maxTokenLimit
    ): array {
        $cacheKey = md5($systemMessage . $userPrompt);

        $cached = self::getCachedResponse($cacheKey);
        if ($cached !== null) {
            $decoded = json_decode($cached, true);
            return is_array($decoded) ? $decoded : [];
        }

        $combinedLen = mb_strlen($systemMessage) + mb_strlen($userPrompt);
        if ($combinedLen > $maxTokenLimit) {
            $overBy = $combinedLen - $maxTokenLimit;
            if (mb_strlen($userPrompt) >= $overBy) {
                $userPrompt = mb_substr($userPrompt, 0, mb_strlen($userPrompt) - $overBy);
            } else {
                $rem = $overBy - mb_strlen($userPrompt);
                $userPrompt = "";
                $systemMessage = mb_substr($systemMessage, 0, mb_strlen($systemMessage) - $rem);
            }
            $systemMessage .= "\n[NOTE: Input truncated due to token limit]";
        }

        $postData = [
            'model'       => $model ?: self::$default_model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user',   'content' => $userPrompt]
            ],
            'temperature' => $temperature,
            'max_tokens'  => 512
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($baseUrl, '/') . '/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            return ['title' => '[AI Error: No response]', 'summary' => '', 'tags' => ''];
        }

        $response = json_decode($result, true);
        if (!isset($response['choices'][0]['message']['content'])) {
            return ['title' => '[AI Error]', 'summary' => '', 'tags' => ''];
        }

        $aiText  = $response['choices'][0]['message']['content'];
        $decoded = json_decode($aiText, true);
        if (!is_array($decoded)) {
            // fallback if not valid JSON
            $decoded = [
                'title'   => '[AI Error: Not valid JSON]',
                'summary' => $aiText,
                'tags'    => ''
            ];
        }

        self::saveCachedResponse($cacheKey, json_encode($decoded));
        return $decoded;
    }
}
