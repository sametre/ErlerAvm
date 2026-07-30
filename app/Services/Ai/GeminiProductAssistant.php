<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProductAssistant
{
    /**
     * @param  array{name?: string|null, brand?: string|null, category?: string|null, sku?: string|null, notes?: string|null}  $product
     * @return array{name: string|null, short_description: string|null, description: string|null, seo_title: string|null, seo_description: string|null, seo_keywords: string|null, image_prompt: string|null}
     */
    public function generateProductCopy(array $product): array
    {
        $apiKey = (string) config('services.gemini.api_key');
        $model = (string) config('services.gemini.model', 'gemini-1.5-flash');
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        if ($apiKey === '') {
            throw new RuntimeException('Gemini API anahtarı tanımlı değil. GEMINI_API_KEY ortam değişkeni eklenmeden AI içerik üretimi çalışmaz.');
        }

        $prompt = $this->prompt($product);

        try {
            $response = Http::timeout(20)
                ->retry(2, 300)
                ->acceptJson()
                ->post("{$baseUrl}/models/{$model}:generateContent?key={$apiKey}", [
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'responseMimeType' => 'application/json',
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            report($exception);

            throw new RuntimeException('Gemini servisinden geçerli cevap alınamadı. API anahtarını, model adını ve ağ erişimini kontrol edin.');
        }

        $text = (string) data_get($response, 'candidates.0.content.parts.0.text', '');
        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini cevabı beklenen JSON formatında değil.');
        }

        return [
            'name' => $this->cleanNullable($decoded['name'] ?? null, 180),
            'short_description' => $this->cleanNullable($decoded['short_description'] ?? null, 500),
            'description' => $this->cleanNullable($decoded['description'] ?? null, 5000),
            'seo_title' => $this->cleanNullable($decoded['seo_title'] ?? null, 180),
            'seo_description' => $this->cleanNullable($decoded['seo_description'] ?? null, 320),
            'seo_keywords' => $this->cleanNullable($decoded['seo_keywords'] ?? null, 500),
            'image_prompt' => $this->cleanNullable($decoded['image_prompt'] ?? null, 1000),
        ];
    }

    /**
     * @param  array{name?: string|null, brand?: string|null, category?: string|null, sku?: string|null, notes?: string|null}  $product
     */
    private function prompt(array $product): string
    {
        return 'ErlerAVM e-ticaret admin paneli için gerçek ürün metni üret. '.
            'Çıktı sadece JSON olsun; markdown, açıklama ve kod bloğu kullanma. '.
            'Uydurma teknik özellik, garanti, stok, indirim, teslimat süresi veya kampanya yazma. '.
            'Bilinmeyen bilgileri genel ve dürüst bırak. Türkçe yaz. '.
            'Alanlar: name, short_description, description, seo_title, seo_description, seo_keywords, image_prompt. '.
            'Ürün verisi: '.json_encode([
                'name' => $product['name'] ?? null,
                'brand' => $product['brand'] ?? null,
                'category' => $product['category'] ?? null,
                'sku' => $product['sku'] ?? null,
                'notes' => $product['notes'] ?? null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function cleanNullable(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(strip_tags($value));

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $limit);
    }
}
