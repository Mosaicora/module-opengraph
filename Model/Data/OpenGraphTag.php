<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Data;

use Magento\Framework\DataObject;
use Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface;
use Mosaicora\OpenGraph\Model\Resolver\TextSanitizer;

class OpenGraphTag extends DataObject implements OpenGraphTagInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly TextSanitizer $textSanitizer,
        array $data = []
    ) {
        parent::__construct($data);
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $name): OpenGraphTagInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getContent(): string
    {
        return (string)$this->getData(self::CONTENT);
    }

    public function setContent(string $content): OpenGraphTagInterface
    {
        return $this->setData(self::CONTENT, $this->textSanitizer->clean($content));
    }
}
