<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClaudeService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-sonnet-4-6';
    private const MAX_TOKENS = 2048;

    private const SYSTEM_PROMPT = 'You are a warm, perceptive companion helping someone understand themselves more deeply. You are not a therapist and you never present yourself as one. You speak like a very insightful, caring close friend. You always use the person\'s own words and specific details from what they\'ve shared. You never give generic affirmations or platitudes. You find the specific, real, continuous thread in what someone shares and reflect it back with clarity and warmth. Your entire purpose is to help people see that there is a consistent \'them\' underneath all the different versions they\'ve been. Be specific. Be real. Be warm.';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    /**
     * Non-streaming call — returns full text response
     */
    public function complete(string $userPrompt): string
    {
        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'system' => self::SYSTEM_PROMPT,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ],
        ]);

        $data = $response->toArray();
        return $data['content'][0]['text'] ?? '';
    }

    /**
     * Streaming call — yields text chunks as they arrive.
     * Returns a generator that yields string chunks.
     *
     * @return \Generator<string>
     */
    public function stream(string $userPrompt): \Generator
    {
        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'max_tokens' => self::MAX_TOKENS,
                'stream' => true,
                'system' => self::SYSTEM_PROMPT,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ],
            'buffer' => false,
        ]);

        $buffer = '';

        foreach ($this->httpClient->stream($response) as $chunk) {
            $buffer .= $chunk->getContent();

            // SSE events are separated by double newlines
            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $event = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                foreach (explode("\n", $event) as $line) {
                    if (!str_starts_with($line, 'data: ')) {
                        continue;
                    }
                    $data = substr($line, 6);
                    if ($data === '[DONE]') {
                        return;
                    }

                    $parsed = json_decode($data, true);
                    if (!$parsed) {
                        continue;
                    }

                    // content_block_delta events carry text
                    if (
                        isset($parsed['type']) &&
                        $parsed['type'] === 'content_block_delta' &&
                        isset($parsed['delta']['type']) &&
                        $parsed['delta']['type'] === 'text_delta'
                    ) {
                        yield $parsed['delta']['text'];
                    }

                    // Stop on message_stop
                    if (isset($parsed['type']) && $parsed['type'] === 'message_stop') {
                        return;
                    }
                }
            }
        }
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ];
    }
}
