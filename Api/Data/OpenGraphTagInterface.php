<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Api\Data;

interface OpenGraphTagInterface
{
    public const NAME = 'name';
    public const CONTENT = 'content';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return \Mosaicora\OpenGraph\Api\Data\OpenGraphTagInterface
     */
    public function setContent(string $content): self;
}
