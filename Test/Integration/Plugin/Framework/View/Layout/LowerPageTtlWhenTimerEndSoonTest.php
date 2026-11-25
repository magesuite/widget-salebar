<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Test\Integration\Plugin\Framework\View\Layout;

class LowerPageTtlWhenTimerEndSoonTest extends \PHPUnit\Framework\TestCase
{
    protected const CURRENT_TIMESTAMP = 1893499200;

    protected ?\Magento\Framework\ObjectManagerInterface $objectManager = null;
    protected ?\Magento\Framework\Registry $registry = null;
    protected ?\Magento\Framework\App\Response\Http $response = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->create(\Magento\Framework\Registry::class);
        $this->response = $this->objectManager->create(\Magento\Framework\App\Response\Http::class);
    }

    public function testItLowersTtlOfCacheablePageToTimeLeftUntilVisibilityChange(): void
    {
        $this->response->setPublicHeaders(86400);

        $this->runPlugin(self::CURRENT_TIMESTAMP + 600, true);

        $this->assertStringContainsString('max-age=600', $this->response->getHeader('Cache-Control')->getFieldValue());
    }

    public function testItDisablesCachingWhenVisibilityChangeHasAlreadyPassed(): void
    {
        $this->response->setPublicHeaders(86400);

        $this->runPlugin(self::CURRENT_TIMESTAMP - 1, true);

        $this->assertStringNotContainsString('public', $this->response->getHeader('Cache-Control')->getFieldValue());
    }

    public function testItDoesNotMakeNonCacheablePagePublic(): void
    {
        $this->response->setNoCacheHeaders();

        $this->runPlugin(self::CURRENT_TIMESTAMP + 600, false);

        $this->assertStringNotContainsString('public', $this->response->getHeader('Cache-Control')->getFieldValue());
    }

    protected function runPlugin(int $visibilityChangeTimestamp, bool $isCacheable): void
    {
        $this->registry->register(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY, $visibilityChangeTimestamp);

        $storeTime = $this->createMock(\MageSuite\WidgetSalebar\Model\StoreTime::class);
        $storeTime->method('getCurrentTimestamp')->willReturn(self::CURRENT_TIMESTAMP);

        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->method('isCacheable')->willReturn($isCacheable);

        $plugin = $this->objectManager->create(\MageSuite\WidgetSalebar\Plugin\Framework\View\Layout\LowerPageTtlWhenTimerEndSoon::class, [
            'response' => $this->response,
            'registry' => $this->registry,
            'storeTime' => $storeTime,
        ]);

        $plugin->afterGetOutput($layout, '');
    }
}
