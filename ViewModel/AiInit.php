<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Panth\PageBuilderAi\Helper\Config;

/**
 * ViewModel for pagebuilder-ai-init.phtml.
 *
 * Supports two modes:
 *   1. Standalone — uses PageBuilderAi's own AI config and endpoint.
 *   2. With AdvancedSEO — uses AdvancedSEO's AI backend and prompt templates.
 *
 * Priority: if PageBuilderAi has its own API key configured, it uses its own
 * endpoint. Otherwise, falls back to AdvancedSEO if available. This ensures
 * the module works independently AND alongside AdvancedSEO without conflicts.
 */
class AiInit implements ArgumentInterface
{
    private const OPTIONAL_MODULE = 'Panth_AdvancedSEO';
    private const OPTIONAL_HELPER = 'Panth\AdvancedSEO\Helper\Config';
    private const PROMPT_TABLE = 'panth_seo_ai_prompt';

    public function __construct(
        private readonly Config $config,
        private readonly ModuleManager $moduleManager,
        private readonly UrlInterface $backendUrl,
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Returns true when the module is enabled.
     * API key validation happens at generation time, not at render time.
     */
    public function isAvailable(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Returns the AI generation endpoint URL based on which backend is active.
     * Falls back to own endpoint when AdvancedSEO is not available — the
     * controller will return a clear error if the API key is not yet configured.
     */
    public function getGenerateUrl(): string
    {
        if ($this->config->isAdvancedSeoAvailable() && !$this->config->hasOwnApiKey()) {
            return $this->backendUrl->getUrl('panth_seo/aigenerate/generate');
        }

        return $this->backendUrl->getUrl('panth_pagebuilderai/generate/index');
    }

    /**
     * Returns which backend is active: 'own', 'advancedseo', or 'none'.
     */
    public function getActiveBackend(): string
    {
        return $this->config->getActiveBackend();
    }

    /**
     * Loads saved prompt templates from AdvancedSEO (if available).
     * Returns empty array when running standalone.
     *
     * @return array<int, array{id:int, name:string, template:string}>
     */
    public function getSavedPrompts(): array
    {
        // Saved prompts only exist in AdvancedSEO's database table
        if (!$this->moduleManager->isEnabled(self::OPTIONAL_MODULE)) {
            return [];
        }

        try {
            $conn = $this->resource->getConnection();
            $table = $this->resource->getTableName(self::PROMPT_TABLE);
            if (!$conn->isTableExists($table)) {
                return [];
            }

            $rows = $conn->fetchAll(
                $conn->select()
                    ->from($table, ['prompt_id', 'name', 'prompt_template', 'is_default'])
                    ->where('is_active = 1')
                    ->where('entity_type IN (?)', ['cms_page', 'all', 'pagebuilder'])
                    ->order('is_default DESC')
                    ->order('sort_order ASC')
            );

            $prompts = [];
            foreach ($rows as $row) {
                $prompts[] = [
                    'id' => (int) $row['prompt_id'],
                    'name' => (string) $row['name'],
                    'template' => (string) $row['prompt_template'],
                ];
            }
            return $prompts;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
