<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Test\Integration\Block\Widget;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
class SalebarTest extends \PHPUnit\Framework\TestCase
{
    protected const TIMER_DATE = '2030-03-31 03:30:00';
    protected const TIMER_TIMESTAMP_IN_BERLIN = 1901151000;

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

    #[\Magento\TestFramework\Fixture\Config('general/locale/timezone', 'Europe/Berlin', 'store', 'default')]
    public function testItRendersTextAfterCountdownWithoutTimerWhenTimerExpired(): void
    {
        $html = $this->createSalebar(self::TIMER_TIMESTAMP_IN_BERLIN + 1, 'Scheduled text')->toHtml();

        $this->assertStringContainsString('Scheduled text', $html);
        $this->assertStringNotContainsString('Countdown text', $html);
        $this->assertStringNotContainsString('cs-salebar-widget__countdown', $html);
        $this->assertNull($this->registry->registry(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY));
    }

    #[\Magento\TestFramework\Fixture\Config('general/locale/timezone', 'Europe/Berlin', 'store', 'default')]
    public function testItRendersNothingWhenTimerExpiredWithoutTextAfterCountdown(): void
    {
        $this->assertSame('', $this->createSalebar(self::TIMER_TIMESTAMP_IN_BERLIN + 1, null)->toHtml());
    }

    #[\Magento\TestFramework\Fixture\Config('general/locale/timezone', 'Europe/Berlin', 'store', 'default')]
    public function testItRendersCountdownAndRegistersVisibilityChangeWhileTimerRuns(): void
    {
        $html = $this->createSalebar(self::TIMER_TIMESTAMP_IN_BERLIN - 1, 'Scheduled text')->toHtml();

        $this->assertStringContainsString('Countdown text', $html);
        $this->assertStringContainsString('cs-salebar-widget__countdown-active', $html);
        $this->assertSame(self::TIMER_TIMESTAMP_IN_BERLIN, $this->registry->registry(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY));
    }

    #[\Magento\TestFramework\Fixture\Config('general/locale/timezone', 'Europe/Berlin', 'store', 'default')]
    public function testItKeepsQuotesFromSettingsInsideHtmlAttributes(): void
    {
        $salebar = $this->createSalebar(self::TIMER_TIMESTAMP_IN_BERLIN - 1, null);
        $salebar->setData('css_modifier', 'promo" data-injected="1');
        $salebar->setData('background_color', '#fff" data-injected="2');

        $html = $salebar->toHtml();

        $this->assertStringNotContainsString('data-injected="1"', $html);
        $this->assertStringNotContainsString('data-injected="2"', $html);
    }

    protected function createSalebar(int $currentTimestamp, ?string $textAfterCountdown): \MageSuite\WidgetSalebar\Block\Widget\Salebar
    {
        $storeTime = $this->getMockBuilder(\MageSuite\WidgetSalebar\Model\StoreTime::class)
            ->setConstructorArgs([
                $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class),
                $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class),
            ])
            ->onlyMethods(['getCurrentTimestamp'])
            ->getMock();
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
