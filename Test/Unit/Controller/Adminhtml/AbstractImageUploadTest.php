<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Controller\Adminhtml;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mosaicora\OpenGraph\Controller\Adminhtml\Category\Image\Upload as CategoryUpload;
use Mosaicora\OpenGraph\Controller\Adminhtml\Cms\Image\Upload as CmsUpload;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AbstractImageUploadTest extends TestCase
{
    public function testUploadControllerNamespacesMatchAdminRoutes(): void
    {
        self::assertSame(
            'Mosaicora\OpenGraph\Controller\Adminhtml\Category\Image\Upload',
            CategoryUpload::class
        );
        self::assertSame(
            'Mosaicora\OpenGraph\Controller\Adminhtml\Cms\Image\Upload',
            CmsUpload::class
        );
    }

    public function testCategoryUploadUsesCategoryAcl(): void
    {
        self::assertSame('Magento_Catalog::categories', CategoryUpload::ADMIN_RESOURCE);
    }

    public function testCmsUploadUsesCmsSaveAcl(): void
    {
        self::assertSame('Magento_Cms::save', CmsUpload::ADMIN_RESOURCE);
    }

    public function testExecuteDelegatesToImageUploaderWithStandardResponse(): void
    {
        $objectManager = new ObjectManager($this);
        $request = $objectManager->getObject(Http::class);
        $request->setParam('param_name', 'og_image_custom');

        $uploader = $this->createPartialMock(ImageUploader::class, ['saveFileToTmpDir']);
        $uploader->expects($this->once())
            ->method('saveFileToTmpDir')
            ->with('og_image_custom')
            ->willReturn(['name' => 'image.jpg', 'tmp_name' => '/tmp/image.jpg']);

        $result = $this->createMock(Json::class);
        $result->expects($this->once())
            ->method('setData')
            ->with(['name' => 'image.jpg', 'tmp_name' => '/tmp/image.jpg'])
            ->willReturnSelf();

        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultFactory->expects($this->once())->method('create')->willReturn($result);

        $controller = $objectManager->getObject(
            CategoryUpload::class,
            [
                'request' => $request,
                'resultFactory' => $resultFactory,
                'imageUploader' => $uploader,
            ]
        );

        $controller->execute();
    }

    public function testExecuteRejectsResponseWithoutTemporaryFileReference(): void
    {
        $objectManager = new ObjectManager($this);
        $uploader = $this->createPartialMock(ImageUploader::class, ['saveFileToTmpDir']);
        $uploader->method('saveFileToTmpDir')->willReturn(['name' => 'image.jpg']);

        $result = $this->createMock(Json::class);
        $result->expects($this->once())
            ->method('setData')
            ->with($this->callback(
                static fn (array $data): bool => isset($data['error'], $data['errorcode'])
                    && str_contains($data['error'], 'temporary file reference')
            ))
            ->willReturnSelf();

        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultFactory->method('create')->willReturn($result);

        $controller = $objectManager->getObject(
            CmsUpload::class,
            [
                'resultFactory' => $resultFactory,
                'imageUploader' => $uploader,
            ]
        );

        $controller->execute();
    }
}
