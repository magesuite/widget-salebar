<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Test\Integration\Block\Widget;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
class SalebarTest extends \PHPUnit\Framework\TestCase
{
    protected const TIMER_DATE = '2030-01-01 12:00:00';

    protected ?\Magento\Framework\ObjectManagerInterface $objectManager = null;
    protected ?\Magento\Framework\Registry $registry = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
    }

    protected function tearDown(): void
    {
        $this->registry->unregister(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY);
    }

    public function testItRendersTextAfterCountdownWithoutTimerWhenTimerExpired(): void
    {
        $html = $this->createSalebar(strtotime(self::TIMER_DATE) + 1, 'Scheduled text')->toHtml();

        $this->assertStringContainsString('Scheduled text', $html);
        $this->assertStringNotContainsString('Countdown text', $html);
        $this->assertStringNotContainsString('cs-salebar-widget__countdown', $html);
        $this->assertNull($this->registry->registry(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY));
    }

    public function testItRendersNothingWhenTimerExpiredWithoutTextAfterCountdown(): void
    {
        $this->assertSame('', $this->createSalebar(strtotime(self::TIMER_DATE) + 1, null)->toHtml());
    }

    public function testItRendersCountdownAndRegistersVisibilityChangeWhileTimerRuns(): void
    {
        $html = $this->createSalebar(strtotime(self::TIMER_DATE) - 1, 'Scheduled text')->toHtml();

        $this->assertStringContainsString('Countdown text', $html);
        $this->assertStringContainsString('cs-salebar-widget__countdown-active', $html);
        $this->assertSame(strtotime(self::TIMER_DATE), $this->registry->registry(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY));
    }

    protected function createSalebar(int $currentTimestamp, ?string $textAfterCountdown): \MageSuite\WidgetSalebar\Block\Widget\Salebar
    {
        $storeTime = $this->createMock(\MageSuite\WidgetSalebar\Model\StoreTime::class);
        $storeTime->method('getCurrentTimestamp')->willReturn($currentTimestamp);

        return $this->objectManager->create(\MageSuite\WidgetSalebar\Block\Widget\Salebar::class, [
            'storeTime' => $storeTime,
            'data' => [
                'salebar_text' => 'Countdown text %TIMER%',
                'use_timer' => '1',
                'salebar_timer' => self::TIMER_DATE,
                'salebar_timer_text_after_countdown' => $textAfterCountdown,
            ],
        ]);
    }
}
