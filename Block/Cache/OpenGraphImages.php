<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Block\Cache;

use Magento\Backend\Block\Template;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\FormKey;

class OpenGraphImages extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly AuthorizationInterface $authorization,
        private readonly FormKey $openGraphFormKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function hasAccessToFlushOpenGraphImages(): bool
    {
        return $this->authorization->isAllowed('Mosaicora_OpenGraph::flush_open_graph_images');
    }

    public function getCleanOpenGraphImagesUrl(): string
    {
        return $this->getUrl('mosaicora_opengraph/cache/cleanImages');
    }

    public function getFormKey(): string
    {
        return $this->openGraphFormKey->getFormKey();
    }
}
