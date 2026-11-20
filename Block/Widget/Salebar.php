<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Block\Widget;

class Salebar extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public const TIMER_KEYWORD = '%TIMER%';
    public const TIMER_DIV_CLASS = '<span class="cs-salebar-widget__countdown"></span>';
    public const VISIBILITY_CHANGE_REGISTRY_KEY = 'salebar_timestamp';

    protected $_template = 'widget/salebar.phtml'; //phpcs:ignore

    public function __construct( //phpcs:ignore
        \Magento\Framework\View\Element\Template\Context $context,
        protected \Magento\Cms\Model\Template\Filter $filter,
        protected \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        protected \Magento\Framework\Registry $registry,
        protected \MageSuite\WidgetSalebar\Model\StoreTime $storeTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getText(): string
    {
        $text = $this->getData('salebar_text');

        if (!$text) {
            return '';
        }

        $text = str_replace(['<p>', '</p>'], ['', ''], $text);
        $text = str_replace(self::TIMER_KEYWORD, self::TIMER_DIV_CLASS, $text);

        return $this->filter->filter($text);
    }

    public function getTextAfterCountdown(): string
    {
        return (string)$this->getData('salebar_timer_text_after_countdown');
    }

    public function getFinalTime(): int
    {
        return (int)$this->datetime->gmtTimestamp($this->getData('salebar_timer'));
    }

    public function isSalebarActive(): bool
    {
        if (!$this->isTimerExpired()) {
            return true;
        }

        return $this->getTextAfterCountdown() !== '';
    }

    public function isTimerRunning(): bool
    {
        if (!$this->isTimerConfigured() || $this->isTimerExpired()) {
            return false;
        }

        $this->registerVisibilityChange($this->getFinalTime());

        return true;
    }

    public function isTimerExpired(): bool
    {
        if (!$this->isTimerConfigured()) {
            return false;
        }

        return $this->storeTime->getCurrentTimestamp() >= $this->getFinalTime();
    }

    public function toHtml(): string
    {
        $output = parent::_toHtml();

        return $this->filter->filter($output);
    }

    protected function isTimerConfigured(): bool
    {
        return $this->getData('use_timer') && $this->getData('salebar_timer') && $this->getFinalTime();
    }

    protected function registerVisibilityChange(int $timestamp): void
    {
        $registeredTimestamp = $this->registry->registry(self::VISIBILITY_CHANGE_REGISTRY_KEY);

        if ($registeredTimestamp !== null && $registeredTimestamp <= $timestamp) {
            return;
        }

        $this->registry->unregister(self::VISIBILITY_CHANGE_REGISTRY_KEY);
        $this->registry->register(self::VISIBILITY_CHANGE_REGISTRY_KEY, $timestamp);
    }
}
