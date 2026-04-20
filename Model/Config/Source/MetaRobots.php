<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Provides meta robots directives for product, category, and CMS page
 * edit forms. Extends AbstractSource so it works as an EAV attribute source
 * (has setAttribute/getAttribute methods required by Magento EAV system).
 * An empty value means "inherit from template/rules".
 */
class MetaRobots extends AbstractSource
{
    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase|string}>
     */
    public function toOptionArray(): array
    {
        return $this->getAllOptions();
    }

    /**
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase|string}>
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '',                'label' => __('Use Default (from template/rules)')],
                ['value' => 'INDEX,FOLLOW',    'label' => __('INDEX,FOLLOW')],
                ['value' => 'NOINDEX,FOLLOW',  'label' => __('NOINDEX,FOLLOW')],
                ['value' => 'INDEX,NOFOLLOW',  'label' => __('INDEX,NOFOLLOW')],
                ['value' => 'NOINDEX,NOFOLLOW','label' => __('NOINDEX,NOFOLLOW')],
            ];
        }
        return $this->_options;
    }
}
