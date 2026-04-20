<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Block\Adminhtml;

use Magento\Backend\Block\Template;

class AiSettings extends Template
{
    protected $_template = 'Panth_PageBuilderAi::ai_settings.phtml';

    public function getConfigUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit', ['section' => 'panth_pagebuilderai']);
    }

    public function getJobsUrl(): string
    {
        return $this->getUrl('panth_pagebuilderai/aisettings/jobs');
    }
}
