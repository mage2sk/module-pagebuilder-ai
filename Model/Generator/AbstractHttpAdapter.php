<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Model\RequestLogger;
use Psr\Log\LoggerInterface;

abstract class AbstractHttpAdapter
{
    protected const MAX_RETRIES = 3;
    protected const BACKOFF_BASE_MS = 500;
    protected const CONNECT_TIMEOUT_SECONDS = 10;
    protected const REQUEST_TIMEOUT_SECONDS = 60;
    protected const TOTAL_TIMEOUT_SECONDS = 120;

    protected int $lastUsageTokens = 0;

    public function getLastUsageTokens(): int
    {
        return $this->lastUsageTokens;
    }

    abstract public function getProvider(): string;

    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly EncryptorInterface $encryptor,
        protected readonly ResourceConnection $resource,
        protected readonly DateTime $dateTime,
        protected readonly LoggerInterface $logger
    ) {
    }

    protected function getApiKey(string $path): string
    {
        $raw = (string)$this->scopeConfig->getValue($path);
        if ($raw === '') {
            return '';
        }
        try {
            $decrypted = $this->encryptor->decrypt($raw);
        } catch (\Throwable $e) {
            $decrypted = '';
        }
        return $decrypted !== '' ? $decrypted : $raw;
    }

    protected function getMonthlyBudget(): int
    {
        return (int)$this->scopeConfig->getValue('panth_pagebuilderai/ai/monthly_budget');
    }

    protected function getTemperature(): float
    {
        $raw = $this->scopeConfig->getValue('panth_pagebuilderai/ai/temperature');
        if ($raw === null || $raw === '') {
            return 0.4;
        }
        $value = (float)$raw;
        if ($value < 0.0 || $value > 2.0) {
            return 0.4;
        }
        return $value;
    }

    protected function getMaxTokens(int $default = 600): int
    {
        $raw = (int)$this->scopeConfig->getValue('panth_pagebuilderai/ai/max_tokens');
        return $raw > 0 ? $raw : $default;
    }

    protected function getCacheTtl(): int
    {
        $raw = $this->scopeConfig->getValue('panth_pagebuilderai/ai/cache_ttl');
        if ($raw === null || $raw === '') {
            return 0;
        }
        return max(0, (int)$raw);
    }

    protected function getMonthlyUsage(string $provider): int
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_usage');
        if (!$connection->isTableExists($table)) {
            return 0;
        }
        $select = $connection->select()
            ->from($table, ['used' => 'SUM(total_tokens)'])
            ->where('provider = ?', $provider)
            ->where('period = ?', date('Y-m'));
        $row = $connection->fetchRow($select);
        return (int)($row['used'] ?? 0);
    }

    protected function reserveBudget(string $provider, int $estimate, int $budget): bool
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_usage');
        if (!$connection->isTableExists($table)) {
            return true;
        }

        $period = date('Y-m');
        $now = $this->dateTime->gmtDate();

        try {
            $connection->insertOnDuplicate(
                $table,
                ['provider' => $provider, 'period' => $period, 'total_tokens' => 0, 'created_at' => $now],
                ['created_at']
            );

            $affected = $connection->update(
                $table,
                [
                    'total_tokens' => new \Zend_Db_Expr('total_tokens + ' . (int)$estimate),
                    'created_at' => $now,
                ],
                [
                    'provider = ?' => $provider,
                    'period = ?' => $period,
                    new \Zend_Db_Expr('total_tokens + ' . (int)$estimate . ' <= ' . (int)$budget),
                ]
            );

            return $affected > 0;
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi budget reservation failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function adjustUsage(string $provider, int $delta): void
    {
        if ($delta === 0) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_usage');
        if (!$connection->isTableExists($table)) {
            return;
        }
        try {
            $expr = $delta > 0
                ? 'total_tokens + ' . $delta
                : 'GREATEST(0, total_tokens - ' . abs($delta) . ')';
            $connection->update(
                $table,
                [
                    'total_tokens' => new \Zend_Db_Expr($expr),
                    'created_at' => $this->dateTime->gmtDate(),
                ],
                [
                    'provider = ?' => $provider,
                    'period = ?' => date('Y-m'),
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi usage adjustment failed: ' . $e->getMessage());
        }
    }

    protected function releaseBudget(string $provider, int $estimate): void
    {
        $this->adjustUsage($provider, -$estimate);
    }

    protected function recordUsage(string $provider, int $tokens): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_usage');
        if (!$connection->isTableExists($table)) {
            return;
        }
        try {
            $connection->insertOnDuplicate(
                $table,
                [
                    'provider' => $provider,
                    'period' => date('Y-m'),
                    'total_tokens' => $tokens,
                    'created_at' => $this->dateTime->gmtDate(),
                ],
                ['total_tokens' => new \Zend_Db_Expr('total_tokens + VALUES(total_tokens)'), 'created_at']
            );
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi usage record failed: ' . $e->getMessage());
        }
    }

    protected function loadCached(string $promptHash): ?array
    {
        $ttl = $this->getCacheTtl();
        if ($ttl <= 0) {
            return null;
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_cache');
        if (!$connection->isTableExists($table)) {
            return null;
        }
        $select = $connection->select()
            ->from($table, ['response', 'expires_at'])
            ->where('cache_key = ?', $promptHash)
            ->where('expires_at > ?', time())
            ->limit(1);
        $row = $connection->fetchRow($select);
        if (!$row) {
            return null;
        }
        $decoded = json_decode((string)$row['response'], true);
        return is_array($decoded) ? $decoded : null;
    }

    protected function saveCached(string $promptHash, array $response, string $provider = ''): void
    {
        $ttl = $this->getCacheTtl();
        if ($ttl <= 0) {
            return;
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_cache');
        if (!$connection->isTableExists($table)) {
            return;
        }
        try {
            $connection->insertOnDuplicate(
                $table,
                [
                    'cache_key' => $promptHash,
                    'provider' => $provider,
                    'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
                    'expires_at' => time() + $ttl,
                    'created_at' => $this->dateTime->gmtDate(),
                ],
                ['response', 'expires_at', 'created_at']
            );
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi cache save failed: ' . $e->getMessage());
        }
    }

    private const ALLOWED_API_HOSTS = [
        'api.openai.com',
        'api.anthropic.com',
    ];

    private const MAX_RESPONSE_BYTES = 2 * 1024 * 1024;

    protected function curlPost(string $url, array $headers, array $payload): array
    {
        $parsedUrl = parse_url($url);
        $host = strtolower($parsedUrl['host'] ?? '');
        $scheme = strtolower($parsedUrl['scheme'] ?? '');

        if ($scheme !== 'https' || !in_array($host, self::ALLOWED_API_HOSTS, true)) {
            $this->logger->warning('Panth PageBuilderAi: blocked request to disallowed host: ' . $host);
            return ['status' => 0, 'body' => '{"error":"Request blocked: disallowed API host"}'];
        }

        $startedAt = microtime(true);
        $attempt = 0;
        $lastStatus = 0;
        $lastBody = '';
        $hdrs = [];
        foreach ($headers as $k => $v) {
            $hdrs[] = $k . ': ' . $v;
        }
        $postBody = (string)json_encode($payload, JSON_UNESCAPED_UNICODE);
        while ($attempt < static::MAX_RETRIES) {
            $remaining = static::TOTAL_TIMEOUT_SECONDS - (microtime(true) - $startedAt);
            if ($remaining < 1) {
                break;
            }
            $attempt++;
            $timeout = (int)max(1, min(static::REQUEST_TIMEOUT_SECONDS, floor($remaining)));
            $result = $this->executeRequest($url, $hdrs, $postBody, $timeout);
            $body = $result['body'];
            $status = $result['status'];
            $contentType = $result['content_type'];

            if (!is_string($body)) {
                $lastBody = $result['error'] ?: 'curl failed';
                $lastStatus = 0;
            } else {
                $bodyStr = $body;

                if (strlen($bodyStr) > self::MAX_RESPONSE_BYTES) {
                    $this->logger->warning('Panth PageBuilderAi: response exceeded max size and was truncated');
                    $bodyStr = substr($bodyStr, 0, self::MAX_RESPONSE_BYTES);
                }

                if ($contentType !== '' && stripos($contentType, 'application/json') === false) {
                    $this->logger->warning('Panth PageBuilderAi: unexpected response content-type: ' . $contentType);
                }
                $lastBody = $bodyStr;
                $lastStatus = $status;
            }

            if ($lastStatus >= 200 && $lastStatus < 300) {
                $this->auditLog(
                    payload: $payload,
                    status: $lastStatus,
                    body: $lastBody,
                    latencyMs: (int) round((microtime(true) - $startedAt) * 1000)
                );
                return ['status' => $lastStatus, 'body' => $lastBody];
            }
            if ($lastStatus === 429 || $lastStatus >= 500 || $lastStatus === 0) {
                $backoffMs = (int)(static::BACKOFF_BASE_MS * (2 ** ($attempt - 1)));
                $left = static::TOTAL_TIMEOUT_SECONDS - (microtime(true) - $startedAt);
                if ($attempt >= static::MAX_RETRIES || $left - ($backoffMs / 1000) < 1) {
                    break;
                }
                usleep($backoffMs * 1000);
                continue;
            }
            break;
        }
        $this->auditLog(
            payload: $payload,
            status: $lastStatus,
            body: $lastBody,
            latencyMs: (int) round((microtime(true) - $startedAt) * 1000)
        );
        return ['status' => $lastStatus, 'body' => $lastBody];
    }

    protected function executeRequest(string $url, array $headers, string $body, int $timeout): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('curl_init failed');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min($timeout, static::CONNECT_TIMEOUT_SECONDS),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        $response = curl_exec($ch);
        $result = [
            'body' => is_string($response) ? $response : null,
            'status' => (int)curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'content_type' => (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            'error' => curl_error($ch),
        ];
        curl_close($ch);
        return $result;
    }

    private function auditLog(array $payload, int $status, string $body, int $latencyMs): void
    {
        try {
            $logger = ObjectManager::getInstance()->get(RequestLogger::class);
        } catch (\Throwable) {
            return;
        }

        $prompt = '';
        $imageCount = 0;
        foreach (($payload['messages'] ?? []) as $msg) {
            $content = $msg['content'] ?? '';
            if (is_string($content)) {
                $prompt .= $content . "\n";
            } elseif (is_array($content)) {
                foreach ($content as $part) {
                    if (is_array($part)) {
                        if (($part['type'] ?? '') === 'text') {
                            $prompt .= (string) ($part['text'] ?? '') . "\n";
                        } elseif (($part['type'] ?? '') === 'image' || ($part['type'] ?? '') === 'image_url') {
                            $imageCount++;
                        }
                    }
                }
            }
        }
        if (!empty($payload['system']) && is_string($payload['system'])) {
            $prompt = $payload['system'] . "\n\n" . $prompt;
        }

        $response = $body;
        $tokens = null;
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            if (isset($decoded['choices'][0]['message']['content'])) {
                $response = (string) $decoded['choices'][0]['message']['content'];
                $tokens = isset($decoded['usage']['total_tokens']) ? (int) $decoded['usage']['total_tokens'] : null;
            } elseif (isset($decoded['content']) && is_array($decoded['content'])) {
                $response = '';
                foreach ($decoded['content'] as $block) {
                    if (is_array($block) && ($block['type'] ?? '') === 'text') {
                        $response .= (string) ($block['text'] ?? '');
                    }
                }
                if (isset($decoded['usage']['input_tokens']) || isset($decoded['usage']['output_tokens'])) {
                    $tokens = (int) ($decoded['usage']['input_tokens'] ?? 0) + (int) ($decoded['usage']['output_tokens'] ?? 0);
                }
            }
        }

        $success = $status >= 200 && $status < 300;

        $logger->record([
            'prompt'        => trim($prompt) !== '' ? trim($prompt) : json_encode($payload, JSON_UNESCAPED_UNICODE),
            'response'      => $response !== '' ? $response : $body,
            'image_count'   => $imageCount,
            'success'       => $success,
            'http_status'   => (string) $status,
            'error_message' => $success ? null : substr($body, 0, 500),
            'tokens_used'   => $tokens,
            'latency_ms'    => $latencyMs,
            'output_format' => 'json',
        ]);
    }

    protected function heuristicConfidence(string $title, string $description): float
    {
        $tLen = mb_strlen($title);
        $dLen = mb_strlen($description);
        $tPart = ($tLen >= 30 && $tLen <= 60) ? 1.0 : max(0.0, 1.0 - abs(45 - $tLen) / 45.0);
        $dPart = ($dLen >= 120 && $dLen <= 160) ? 1.0 : max(0.0, 1.0 - abs(140 - $dLen) / 140.0);
        return round(($tPart * 0.4) + ($dPart * 0.6), 3);
    }

    protected function promptHash(string $provider, string $model, string $prompt): string
    {
        return hash('sha256', $provider . '|' . $model . '|' . $prompt);
    }

    protected function buildPrompt(array $context): string
    {
        $entityType = (string)($context['entity_type'] ?? 'product');
        $attrs = (array)($context['attributes'] ?? []);
        $content = trim(strip_tags((string)($context['content'] ?? '')));
        if (mb_strlen($content) > 1200) {
            $content = mb_substr($content, 0, 1200);
        }

        $requestedFields = (array)($context['fields'] ?? []);

        $customPrompt = (string)($context['custom_prompt'] ?? '');
        if ($customPrompt !== '') {
            $prompt = $this->renderPromptTemplate($customPrompt, $attrs, $content, $entityType);
            return $this->appendKnowledgeGuidelines($prompt, $entityType, $requestedFields);
        }

        $promptTemplate = $this->loadPromptTemplate($entityType, $context);
        if ($promptTemplate !== null) {
            $prompt = $this->renderPromptTemplate($promptTemplate, $attrs, $content, $entityType);
            return $this->appendKnowledgeGuidelines($prompt, $entityType, $requestedFields);
        }

        if (!empty($requestedFields)) {
            $prompt = $this->buildMultiFieldPrompt($entityType, $attrs, $content, $requestedFields);
            return $this->appendKnowledgeGuidelines($prompt, $entityType, $requestedFields);
        }

        $lines = [];
        $lines[] = 'You are an SEO expert. Generate a meta title and meta description for the following ' . $entityType . '.';
        $lines[] = 'Title must be 50-60 characters, description 140-156 characters.';
        $lines[] = 'Return strict JSON: {"title":"...","description":"..."}';
        $lines[] = '';
        foreach ($attrs as $k => $v) {
            if (is_scalar($v) && $v !== '') {
                $lines[] = ucfirst((string)$k) . ': ' . (string)$v;
            }
        }
        if ($content !== '') {
            $lines[] = '';
            $lines[] = 'Content:';
            $lines[] = $content;
        }
        $prompt = implode("\n", $lines);
        return $this->appendKnowledgeGuidelines($prompt, $entityType, $requestedFields);
    }

    private function buildMultiFieldPrompt(
        string $entityType,
        array $attrs,
        string $content,
        array $requestedFields
    ): string {
        $fieldSpecs = [
            'meta_title'        => '"meta_title": "50-60 characters, optimized for search engines"',
            'meta_description'  => '"meta_description": "140-156 characters, compelling with a call to action"',
            'meta_keywords'     => '"meta_keywords": "5-10 comma-separated relevant keywords"',
            'og_title'          => '"og_title": "60-90 characters, engaging for social media sharing"',
            'og_description'    => '"og_description": "100-200 characters, social media friendly summary"',
            'short_description' => '"short_description": "1-2 sentences summarizing the ' . $entityType . '"',
        ];

        $jsonFields = [];
        foreach ($requestedFields as $field) {
            if (isset($fieldSpecs[$field])) {
                $jsonFields[] = '  ' . $fieldSpecs[$field];
            }
        }

        if (empty($jsonFields)) {
            $jsonFields[] = '  ' . $fieldSpecs['meta_title'];
            $jsonFields[] = '  ' . $fieldSpecs['meta_description'];
        }

        $lines = [];
        $lines[] = 'You are an SEO expert. Generate optimized SEO content for the following ' . $entityType . '.';
        $lines[] = '';
        $lines[] = 'Return strict JSON with ALL of these fields:';
        $lines[] = '{';
        $lines[] = implode(",\n", $jsonFields);
        $lines[] = '}';
        $lines[] = '';
        $lines[] = 'Important rules:';
        $lines[] = '- meta_title: Must be unique, include primary keyword, and stay within character limit';
        $lines[] = '- meta_description: Must be persuasive, include a call to action, and stay within character limit';
        $lines[] = '- meta_keywords: Use relevant search terms separated by commas';
        $lines[] = '- og_title: Slightly more engaging than meta_title for social sharing';
        $lines[] = '- og_description: Social-friendly version of description';
        $lines[] = '- short_description: Concise marketing-oriented summary';
        $lines[] = '- Do NOT use generic filler text. Base all content on the entity data provided.';
        $lines[] = '- Return ONLY the JSON object, no other text.';
        $lines[] = '';
        $lines[] = '--- Entity Data ---';
        foreach ($attrs as $k => $v) {
            if (is_scalar($v) && $v !== '') {
                $lines[] = ucfirst((string)$k) . ': ' . (string)$v;
            }
        }
        if ($content !== '') {
            $lines[] = '';
            $lines[] = 'Content:';
            $lines[] = $content;
        }
        return implode("\n", $lines);
    }

    private function loadPromptTemplate(string $entityType, array $context): ?string
    {
        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('panth_seo_ai_prompt');
            if (!$connection->isTableExists($table)) {
                return null;
            }

            $promptId = (int)($context['prompt_id'] ?? $context['options']['prompt_id'] ?? 0);
            if ($promptId > 0) {
                $select = $connection->select()
                    ->from($table, ['prompt_template'])
                    ->where('prompt_id = ?', $promptId)
                    ->where('is_active = ?', 1)
                    ->limit(1);
                $template = $connection->fetchOne($select);
                if ($template) {
                    return (string)$template;
                }
            }

            $select = $connection->select()
                ->from($table, ['prompt_template'])
                ->where('is_active = ?', 1)
                ->where('is_default = ?', 1)
                ->where('entity_type IN (?)', [$entityType, 'all'])
                ->order(new \Zend_Db_Expr("FIELD(entity_type, " . $connection->quote($entityType) . ", 'all')"))
                ->limit(1);
            $template = $connection->fetchOne($select);
            if ($template) {
                return (string)$template;
            }

            $select = $connection->select()
                ->from($table, ['prompt_template'])
                ->where('is_active = ?', 1)
                ->where('entity_type IN (?)', [$entityType, 'all'])
                ->order('sort_order ASC')
                ->limit(1);
            $template = $connection->fetchOne($select);
            return $template ? (string)$template : null;
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi prompt load failed: ' . $e->getMessage());
            return null;
        }
    }

    private function renderPromptTemplate(
        string $template,
        array $attrs,
        string $content,
        string $entityType
    ): string {
        $placeholders = [
            '{{name}}'              => (string)($attrs['name'] ?? ''),
            '{{sku}}'               => (string)($attrs['sku'] ?? ''),
            '{{price}}'             => (string)($attrs['price'] ?? ''),
            '{{brand}}'             => (string)($attrs['brand'] ?? $attrs['manufacturer'] ?? ''),
            '{{category}}'          => (string)($attrs['category'] ?? $attrs['category_name'] ?? ''),
            '{{short_description}}' => trim(strip_tags((string)($attrs['short_description'] ?? ''))),
            '{{description}}'       => $content,
            '{{store_name}}'        => (string)($attrs['store_name'] ?? $this->scopeConfig->getValue('general/store_information/name') ?? ''),
            '{{url}}'               => (string)($attrs['url'] ?? $attrs['url_key'] ?? ''),
            '{{entity_type}}'       => $entityType,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    private function appendKnowledgeGuidelines(string $prompt, string $entityType, array $requestedFields = []): string
    {
        try {
            $lines = [];

            $storeContext = $this->getStoreContext();
            if ($storeContext !== '') {
                $lines[] = '';
                $lines[] = '--- STORE CONTEXT (use this information) ---';
                $lines[] = $storeContext;
                $lines[] = '--- END STORE CONTEXT ---';
            }

            $lines[] = '';
            $lines[] = '--- RULES (Google 2026 SEO) ---';
            $lines[] = 'Meta title: 50-60 chars, primary keyword first, brand last. Meta description: 140-156 chars, include CTA. Use E-E-A-T. Mobile-first. Core Web Vitals (LCP<2.5s, CLS<0.1). No keyword stuffing. Natural language. Unique per page.';
            $lines[] = 'IMPORTANT: Never use emojis in any output. No emoji characters anywhere in titles, descriptions, keywords, or content. Use professional language only.';
            $lines[] = '--- END RULES ---';

            $entries = $this->loadKnowledgeEntries($entityType, $requestedFields);
            if (!empty($entries)) {
                $lines[] = '';
                $lines[] = '--- GUIDELINES ---';
                foreach ($entries as $i => $entry) {
                    $content = (string) ($entry['content'] ?? '');
                    if (mb_strlen($content) > 200) {
                        $content = mb_substr($content, 0, 200) . '...';
                    }
                    $lines[] = ($i + 1) . '. ' . ($entry['title'] ?? '') . ': ' . $content;
                }
                $lines[] = '---';
            }

            return empty($lines) ? $prompt : $prompt . "\n" . implode("\n", $lines);
        } catch (\Throwable $e) {
            $this->logger->warning('Panth PageBuilderAi knowledge load failed: ' . $e->getMessage());
            return $prompt;
        }
    }

    private function getStoreContext(): string
    {
        try {
            $lines = [];
            $lines[] = 'Store Name: ' . ($this->scopeConfig->getValue('general/store_information/name') ?: 'N/A');
            $lines[] = 'Store Phone: ' . ($this->scopeConfig->getValue('general/store_information/phone') ?: 'N/A');
            $lines[] = 'Country: ' . ($this->scopeConfig->getValue('general/store_information/country_id') ?: 'N/A');
            $lines[] = 'Currency: ' . ($this->scopeConfig->getValue('currency/options/base') ?: 'USD');
            $lines[] = 'Locale: ' . ($this->scopeConfig->getValue('general/locale/code') ?: 'en_US');

            $freeShippingEnabled = $this->scopeConfig->isSetFlag('carriers/freeshipping/active');
            if ($freeShippingEnabled) {
                $freeShippingThreshold = $this->scopeConfig->getValue('carriers/freeshipping/free_shipping_subtotal');
                $lines[] = 'Free Shipping: Yes (over ' . ($freeShippingThreshold ?: '0') . ')';
            }

            $lines[] = 'Default Meta Title Suffix: ' . ($this->scopeConfig->getValue('design/head/default_title') ?: 'N/A');
            $lines[] = 'Title Separator: ' . ($this->scopeConfig->getValue('catalog/seo/title_separator') ?: '-');

            return implode("\n", $lines);
        } catch (\Throwable) {
            return '';
        }
    }

    private function loadKnowledgeEntries(string $entityType, array $requestedFields = []): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('panth_seo_ai_knowledge');

        if (!$connection->isTableExists($table)) {
            return [];
        }

        $categories = ['seo'];

        if (in_array($entityType, ['product', 'category'], true)) {
            $categories[] = 'ecommerce';
        }

        $contentFields = ['short_description', 'og_description', 'meta_description'];
        $isContentGeneration = !empty($requestedFields)
            && !empty(array_intersect($requestedFields, $contentFields));

        if ($isContentGeneration || empty($requestedFields)) {
            $categories[] = 'accessibility';
            $categories[] = 'response_format';
        }

        if ($entityType === 'cms_page' || empty($requestedFields)) {
            $categories[] = 'pagebuilder';
            $categories[] = 'html_patterns';
        }

        $categories = array_unique($categories);

        $tagTerms = [$entityType];
        foreach ($requestedFields as $field) {
            $tagTerms[] = str_replace('_', '-', $field);
            $tagTerms[] = str_replace('_', ' ', $field);
        }

        $entityTagMap = [
            'product'   => ['product', 'description', 'features', 'ecommerce'],
            'category'  => ['category', 'collection', 'navigation', 'ecommerce'],
            'cms_page'  => ['cms', 'page', 'content', 'layout', 'pagebuilder'],
        ];
        if (isset($entityTagMap[$entityType])) {
            $tagTerms = array_merge($tagTerms, $entityTagMap[$entityType]);
        }

        $select = $connection->select()
            ->from($table, ['category', 'title', 'content', 'tags'])
            ->where('is_active = ?', 1)
            ->where('category IN (?)', $categories)
            ->order('sort_order ASC')
            ->limit(30);

        $rows = $connection->fetchAll($select);

        if (empty($rows)) {
            return [];
        }

        $scored = [];
        foreach ($rows as $row) {
            $tags = strtolower((string)($row['tags'] ?? ''));
            $score = 0;
            foreach ($tagTerms as $term) {
                if (stripos($tags, $term) !== false) {
                    $score += 2;
                }
            }
            if (($row['category'] ?? '') === 'seo') {
                $score += 1;
            }
            $scored[] = ['row' => $row, 'score' => $score];
        }

        usort($scored, static function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $result = [];
        $limit = 5;
        foreach ($scored as $item) {
            if (count($result) >= $limit) {
                break;
            }
            $result[] = $item['row'];
        }

        return $result;
    }

    protected function parseJsonReply(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
        $text = preg_replace('/```\s*$/', '', $text) ?? $text;
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $text = $m[0];
        }
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            return ['title' => '', 'description' => ''];
        }

        if (isset($decoded['meta_title']) || isset($decoded['meta_description'])) {
            $result = [];
            $allowedFields = [
                'meta_title', 'meta_description', 'meta_keywords',
                'og_title', 'og_description', 'short_description',
            ];
            foreach ($allowedFields as $field) {
                if (isset($decoded[$field]) && $decoded[$field] !== '') {
                    $result[$field] = (string)$decoded[$field];
                }
            }
            if (!isset($result['title']) && isset($result['meta_title'])) {
                $result['title'] = $result['meta_title'];
            }
            if (!isset($result['description']) && isset($result['meta_description'])) {
                $result['description'] = $result['meta_description'];
            }
            return $result;
        }

        return [
            'title' => (string)($decoded['title'] ?? ''),
            'description' => (string)($decoded['description'] ?? ''),
        ];
    }
}
