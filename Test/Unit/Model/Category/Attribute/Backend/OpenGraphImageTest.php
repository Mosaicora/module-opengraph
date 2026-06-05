<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\ImageUploader;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mosaicora\OpenGraph\Model\Category\Attribute\Backend\OpenGraphImage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OpenGraphImageTest extends TestCase
{
    public function testBeforeSavePromotesStandardTemporaryUpload(): void
    {
        $attribute = $this->createStub(AbstractAttribute::class);
        $attribute->method('getName')->willReturn('og_image_custom');

        $logger = $this->createStub(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createMock(Store::class);
        $imageUploader = $this->createMock(ImageUploader::class);

        $filesystem->method('getUri')
            ->with(DirectoryList::MEDIA)
            ->willReturn('pub/media');
        $imageUploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with('fall.jpg', true)
            ->willReturn('mosaicora/opengraph/fall.jpg');

        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $store->expects($this->once())
            ->method('getBaseMediaDir')
            ->willReturn('pub/media');

        $model = new OpenGraphImage(
            $logger,
            $filesystem,
            $this->createStub(\Magento\MediaStorage\Model\File\UploaderFactory::class),
            $storeManager,
            $imageUploader
        );
        $model->setAttribute($attribute);

        $object = new DataObject([
            'og_image_custom' => [[
                'name' => 'fall.jpg',
                'url' => 'https://example.test/media/mosaicora/opengraph/tmp/fall.jpg',
                'tmp_name' => '/tmp/fall.jpg',
            ]],
        ]);

        $model->beforeSave($object);

        self::assertSame('/pub/media/mosaicora/opengraph/fall.jpg', $object->getData('og_image_custom'));
        self::assertSame(
            [[
                'name' => '/pub/media/mosaicora/opengraph/fall.jpg',
                'url' => '/pub/media/mosaicora/opengraph/fall.jpg',
                'tmp_name' => '/tmp/fall.jpg',
            ]],
            $object->getData('_additional_data_og_image_custom')
        );
    }

    public function testBeforeSaveDoesNotPromoteAlreadySavedImage(): void
    {
        $attribute = $this->createStub(AbstractAttribute::class);
        $attribute->method('getName')->willReturn('og_image_custom');

        $logger = $this->createStub(LoggerInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $imageUploader = $this->createMock(ImageUploader::class);

        $filesystem->method('getUri')
            ->with(DirectoryList::MEDIA)
            ->willReturn('pub/media');
        $filesystem->expects($this->never())->method('getDirectoryWrite');
        $imageUploader->expects($this->never())->method('getBasePath');

        $model = new OpenGraphImage(
            $logger,
            $filesystem,
            $this->createStub(\Magento\MediaStorage\Model\File\UploaderFactory::class),
            $storeManager,
            $imageUploader
        );
        $model->setAttribute($attribute);

        $object = new DataObject([
            'og_image_custom' => [[
                'name' => 'fall.jpg',
                'url' => '/pub/media/mosaicora/opengraph/fall.jpg',
            ]],
        ]);

        $imageUploader->expects($this->never())->method('moveFileFromTmp');

        $model->beforeSave($object);

        self::assertSame('/pub/media/mosaicora/opengraph/fall.jpg', $object->getData('og_image_custom'));
    }
}
