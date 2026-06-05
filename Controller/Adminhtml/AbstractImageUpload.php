<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

abstract class AbstractImageUpload extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly ImageUploader $imageUploader
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $imageId = $this->getRequest()->getParam('param_name', 'og_image_custom');

        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
            if (empty($result['tmp_name'])) {
                throw new LocalizedException(__('The uploaded image has no temporary file reference.'));
            }
        } catch (\Exception $exception) {
            $result = [
                'error' => $exception->getMessage(),
                'errorcode' => $exception->getCode(),
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
