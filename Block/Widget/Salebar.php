<?php

namespace MageSuite\WidgetSalebar\Block\Widget;

class Salebar extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public const TIMER_KEYWORD = '%TIMER%';
    public const TIMER_DIV_CLASS = '<span class="cs-salebar-widget__countdown"></span>';

    protected $_template = 'widget/salebar.phtml'; //phpcs:ignore

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        protected \Magento\Cms\Model\Template\Filter $filter,
        protected \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getText()
    {
        $text = str_replace(['<p>', '</p>'], ['',''], $this->getData('salebar_text') ?? '');
        $text = str_replace(self::TIMER_KEYWORD, self::TIMER_DIV_CLASS, $text);

        return $this->filter->filter($text);
    }

    public function getFinalTime()
    {
        $finalDate = $this->getData('salebar_timer');

        $timestamp = $this->datetime->gmtTimestamp($finalDate);
        $salebarVisibilityTimestamp = $this->registry->registry('salebar_timestamp');

        if ($salebarVisibilityTimestamp === null || $timestamp < $salebarVisibilityTimestamp) {
            $this->registry->register('salebar_timestamp', $timestamp);
        }

        return $timestamp;
    }

    public function isSalebarActive(): bool
    {
        $finalTime = $this->getFinalTime();
        $currentTime = $this->getCurrentTime();

        return $currentTime < $finalTime;
    }

    public function toHtml()
    {
        $output = parent::_toHtml();

        return $this->filter->filter($output);
    }

    protected function getCurrentTime(): \DateTime
    {
        $currentTime = $this->datetime->gmtTimestamp();
        $timezone = $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_TIMEZONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $dateTimeZone = new \DateTimeZone($timezone);
        $dateTime = new \DateTime('@' . $currentTime);
        $dateTime->setTimezone($dateTimeZone);

        return $dateTime->getTimestamp();
    }
}
