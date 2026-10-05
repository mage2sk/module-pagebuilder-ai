<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Panth\PageBuilderAi\Helper\Config as AiConfig;
use Psr\Log\LoggerInterface;

class RequestLogger
{
    private const TABLE = 'panth_pagebuilderai_request_log';
    public const IMAGE_SUBPATH = 'panth_pagebuilderai/request-log';
    public const ALLOWED_IMAGE_TYPES = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    private const MAX_IMAGE_BASE64_LENGTH = 4000000;

    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime,
        private readonly AdminSession $adminSession,
        private readonly AiConfig $config,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger
    ) {
    }

    public function record(array $data): void
    {
        try {
            $connection = $this->resource->getConnection();
            $table      = $this->resource->getTableName(self::TABLE);

            if (!$connection->isTableExists($table)) {
                return;
            }

            $prompt   = (string) ($data['prompt'] ?? '');
            $response = (string) ($data['response'] ?? '');

            $promptStored   = mb_substr($prompt, 0, 1_000_000);
            $responseStored = mb_substr($response, 0, 1_000_000);

            $imagePaths = $this->persistImages((array) ($data['images'] ?? []));
            $imagesJson = !empty($imagePaths) ? json_encode($imagePaths) : null;

            $adminUser = '';
            try {
                $user = $this->adminSession->getUser();
                if ($user) {
                    $adminUser = (string) ($user->getUsername() ?: $user->getData('username') ?: '');
                }
            } catch (\Throwable) {
            }

            $provider = $this->config->getProvider();
            $model = match ($provider) {
                'openai' => $this->config->getOpenAiModel(),
                'claude' => $this->config->getClaudeModel(),
                default  => null,
            };

            $connection->insert($table, [
                'admin_user'      => $adminUser !== '' ? $adminUser : null,
                'entity_type'     => $this->nullIfEmpty($data['entity_type'] ?? null),
                'entity_id'       => isset($data['entity_id']) && (int) $data['entity_id'] > 0 ? (int) $data['entity_id'] : null,
                'store_id'        => isset($data['store_id']) ? (int) $data['store_id'] : null,
                'target_field'    => $this->nullIfEmpty($data['target_field'] ?? null),
                'output_format'   => $this->nullIfEmpty($data['output_format'] ?? null),
                'provider'        => $provider,
                'model'           => $model ?: null,
                'prompt_length'   => strlen($prompt),
                'response_length' => strlen($response),
                'image_count'     => (int) ($data['image_count'] ?? 0),
                'tokens_used'     => isset($data['tokens_used']) ? (int) $data['tokens_used'] : null,
                'latency_ms'      => isset($data['latency_ms']) ? (int) $data['latency_ms'] : null,
                'success'         => !empty($data['success']) ? 1 : 0,
                'http_status'     => $this->nullIfEmpty($data['http_status'] ?? null),
                'error_message'   => $this->nullIfEmpty($data['error_message'] ?? null),
                'prompt'          => $promptStored,
                'response'        => $responseStored,
                'images_json'     => $imagesJson,
                'created_at'      => $this->dateTime->gmtDate(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->warning('[Panth PageBuilderAi] request log insert failed: ' . $e->getMessage());
        }
    }

    private function persistImages(array $images): array
    {
        $images = array_slice($images, 0, 5);
        if (empty($images)) {
            return [];
        }

        try {
            $writer = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } catch (\Throwable $e) {
            $this->logger->warning('[Panth PageBuilderAi] var dir unavailable: ' . $e->getMessage());
            return [];
        }

        $bucket = date('Ymd_His') . '_' . substr(str_replace('.', '', (string) microtime(true)), -6)
            . '_' . bin2hex(random_bytes(3));
        $relDir = self::IMAGE_SUBPATH . '/' . $bucket;

        $stored = [];
        $index  = 0;
        foreach ($images as $img) {
            if (!is_string($img) || !preg_match('#^data:image/[a-z]+;base64,([A-Za-z0-9+/=]+)$#i', $img, $m)) {
                continue;
            }
            if (strlen($m[1]) > self::MAX_IMAGE_BASE64_LENGTH) {
                continue;
            }
            $binary = base64_decode($m[1], true);
            if ($binary === false || $binary === '') {
                continue;
            }
            try {
                $info = getimagesizefromstring($binary);
            } catch (\Throwable) {
                $info = false;
            }
            $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
            if (!isset(self::ALLOWED_IMAGE_TYPES[$mime])) {
                continue;
            }

            $relPath = $relDir . '/' . $index . '.' . self::ALLOWED_IMAGE_TYPES[$mime];
            try {
                $writer->writeFile($relPath, $binary);
                $stored[] = $relPath;
            } catch (\Throwable $e) {
                $this->logger->warning('[Panth PageBuilderAi] image write failed: ' . $e->getMessage());
            }
            $index++;
        }

        return $stored;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = (string) $value;
        return $str === '' ? null : $str;
    }
}
