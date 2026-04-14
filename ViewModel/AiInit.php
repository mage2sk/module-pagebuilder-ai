<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\ViewModel;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for pagebuilder-ai-init.phtml.
 *
 * Panth_PageBuilderAi is a SOFT consumer of Panth_AdvancedSEO: the module
 * may be physically absent, present-but-disabled, or present-but-AI-disabled.
 * Therefore this ViewModel intentionally does NOT constructor-inject any
 * Panth\AdvancedSEO\* class — doing so would cause Magento DI to throw a
 * ReflectionException at page render when the class is not autoloadable.
 *
 * Instead it injects the framework ModuleManager (always present) and falls
 * back to a guarded ObjectManager::get() for the optional helper, behind a
 * class_exists() + isEnabled() gate. This is the idiomatic pattern for a
 * genuine soft optional dependency in Magento 2.
 */
class AiInit implements ArgumentInterface
{
    private const OPTIONAL_MODULE = 'Panth_AdvancedSEO';
    private const OPTIONAL_HELPER = \Panth\AdvancedSEO\Helper\Config::class;
    private const PROMPT_TABLE = 'panth_seo_ai_prompt';

    /**
     * @var bool|null
     */
    private ?bool $availableCache = null;

    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly UrlInterface $backendUrl,
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Returns true only when Panth_AdvancedSEO is installed, enabled, its
     * Helper class is autoloadable AND AI generation is toggled on in config.
     */
    public function isAvailable(): bool
    {
        if ($this->availableCache !== null) {
            return $this->availableCache;
        }

        if (!$this->moduleManager->isEnabled(self::OPTIONAL_MODULE)) {
            return $this->availableCache = false;
        }

        if (!class_exists(self::OPTIONAL_HELPER)) {
            return $this->availableCache = false;
        }

        try {
            // Guarded ObjectManager usage — justified because the target class
            // may not exist at DI compile time on installations without
            // Panth_AdvancedSEO. See class docblock.
            /** @var \Panth\AdvancedSEO\Helper\Config $config */
            $config = ObjectManager::getInstance()->get(self::OPTIONAL_HELPER);
            return $this->availableCache = (bool) $config->isAiEnabled();
        } catch (\Throwable $e) {
            return $this->availableCache = false;
        }
    }

    /**
     * Returns the admin URL for the AI generation endpoint.
     */
    public function getGenerateUrl(): string
    {
        return $this->backendUrl->getUrl('panth_seo/aigenerate/generate');
    }

    /**
     * Loads saved prompt templates for PageBuilder / cms_page usage.
     *
     * Returns an empty array on any failure — the prompts are a convenience
     * feature and must never break the admin page render.
     *
     * @return array<int, array{id:int, name:string, template:string}>
     */
    public function getSavedPrompts(): array
    {
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
