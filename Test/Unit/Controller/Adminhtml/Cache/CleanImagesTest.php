<?php
/**
 * Copyright © 2026 Mosaicora.io. All rights reserved.
 */

declare(strict_types=1);

namespace Mosaicora\OpenGraph\Test\Unit\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mosaicora\OpenGraph\Controller\Adminhtml\Cache\CleanImages;
use Mosaicora\OpenGraph\Model\Cache\OpenGraphImages;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CleanImagesTest extends TestCase
{
    public function testExecuteCleansCacheDispatchesEventAndRedirects(): void
    {
        $openGraphImagesCache = $this->createMock(OpenGraphImages::class);
        $openGraphImagesCache->expects($this->once())->method('clean');

        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with('mosaicora_opengraph_clean_images_cache_after');

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with($this->callback($this->phraseEquals('The Open Graph images cache was cleaned.')));

        $redirect = $this->createRedirect();
        $controller = $this->createController($openGraphImagesCache, $eventManager, $messageManager, $redirect);

        self::assertInstanceOf(HttpPostActionInterface::class, $controller);
        self::assertSame($redirect, $controller->execute());
    }

    public function testExecuteReportsFailureAndRedirects(): void
    {
        $exception = new \RuntimeException('Unable to delete cache.');
        $openGraphImagesCache = $this->createMock(OpenGraphImages::class);
        $openGraphImagesCache->expects($this->once())
            ->method('clean')
            ->willThrowException($exception);

        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManager->expects($this->never())->method('dispatch');

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with(
                $exception,
                $this->callback($this->phraseEquals('An error occurred while clearing the Open Graph images cache.'))
            );

        $redirect = $this->createRedirect();
        $controller = $this->createController($openGraphImagesCache, $eventManager, $messageManager, $redirect);

        self::assertSame($redirect, $controller->execute());
    }

    private function createController(
        OpenGraphImages $openGraphImagesCache,
        EventManagerInterface $eventManager,
        MessageManagerInterface $messageManager,
        Redirect $redirect
    ): CleanImages {
        $objectManager = new ObjectManager($this);
        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirect);

        $context = $objectManager->getObject(
            Context::class, [
            'eventManager' => $eventManager,
            'messageManager' => $messageManager,
            'resultFactory' => $resultFactory,
            ]
        );

        return $objectManager->getObject(
            CleanImages::class, [
            'context' => $context,
            'openGraphImagesCache' => $openGraphImagesCache,
            ]
        );
    }

    private function createRedirect(): Redirect
    {
        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*')
            ->willReturnSelf();

        return $redirect;
    }

    private function phraseEquals(string $expected): callable
    {
        return static fn (Phrase $phrase): bool => (string)$phrase === $expected;
    }
}
