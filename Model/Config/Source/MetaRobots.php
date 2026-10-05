<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class MetaRobots extends AbstractSource
{
    public function toOptionArray(): array
    {
        return $this->getAllOptions();
    }

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
