<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mosaicora\OpenGraph\Model\Cache\OpenGraphImages;

class CleanImages extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Mosaicora_OpenGraph::flush_open_graph_images';

    public function __construct(
        Context $context,
        private readonly OpenGraphImages $openGraphImagesCache
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        try {
            $this->openGraphImagesCache->clean();
            $this->_eventManager->dispatch('mosaicora_opengraph_clean_images_cache_after');
            $this->messageManager->addSuccessMessage(__('The Open Graph images cache was cleaned.'));
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('An error occurred while clearing the Open Graph images cache.')
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('adminhtml/*');
    }
}
