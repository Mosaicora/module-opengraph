<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractAttributeSource extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var array<int, array{value: string, label: mixed}>|null
     */
    private ?array $options = null;

    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [['value' => '', 'label' => __('-- Auto --')]];
        foreach ($this->getAttributeCollection() as $attribute) {
            $code = (string)$attribute->getAttributeCode();
            if ($code === '' || !$this->isAllowedInput((string)$attribute->getFrontendInput())) {
                continue;
            }

            $label = (string)($attribute->getStoreLabel() ?: $attribute->getFrontendLabel() ?: $code);
            $options[] = [
                'value' => $code,
                'label' => sprintf('%s (%s)', $label, $code),
            ];
        }

        $this->options = $options;
        return $options;
    }

    public function getAllOptions(): array
    {
        return $this->toOptionArray();
    }

    abstract protected function getAttributeCollection(): iterable;

    abstract protected function isAllowedInput(string $frontendInput): bool;
}
