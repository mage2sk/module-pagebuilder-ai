<?php
declare(strict_types=1);

namespace Panth\PageBuilderAi\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Panth\PageBuilderAi\Helper\Config;
use Panth\PageBuilderAi\Model\AiService;

class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public const ADMIN_RESOURCE = 'Panth_PageBuilderAi::generate';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly Config $config,
        private readonly AiService $aiService
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->config->isEnabled()) {
            return $result->setData(['success' => false, 'message' => 'PageBuilder AI is disabled.']);
        }

        if (!$this->config->hasOwnApiKey()) {
            return $result->setData([
                'success' => false,
                'message' => 'API key not configured. Go to Stores > Configuration > Panth Extensions > PageBuilder AI to set up your AI provider.',
            ]);
        }

        $request = $this->getRequest();

        // Support both form-encoded and JSON body
        $contentType = $request->getHeader('Content-Type');
        if ($contentType && stripos($contentType, 'application/json') !== false) {
            $body = json_decode($request->getContent(), true) ?: [];
        } else {
            $body = $request->getParams();
        }

        $prompt = (string) ($body['custom_prompt'] ?? '');
        $images = $body['images'] ?? [];

        if ($prompt === '') {
            return $result->setData(['success' => false, 'message' => 'Prompt is required.']);
        }

        $response = $this->aiService->generate($prompt, is_array($images) ? $images : []);

        if ($response['success']) {
            return $result->setData([
                'success' => true,
                'data' => [
                    'content' => $response['content'],
                    'description' => $response['content'],
                ],
            ]);
        }

        return $result->setData([
            'success' => false,
            'message' => $response['message'] ?? 'AI generation failed.',
        ]);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
