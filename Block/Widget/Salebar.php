<?php

namespace MageSuite\WidgetSalebar\Block\Widget;

class Salebar extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public const TIMER_KEYWORD = '%TIMER%';
    public const TIMER_DIV_CLASS = '<span class="cs-salebar-widget__countdown"></span>';

    protected $_template = 'widget/salebar.phtml'; //phpcs:ignore

    public function __construct( //phpcs:ignore
        \Magento\Framework\View\Element\Template\Context $context,
        protected \Magento\Cms\Model\Template\Filter $filter,
        protected \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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

        if ($this->isBase64Encoded($text)) {
           $text = base64_decode($text);
        }

        $text = str_replace(['<p>', '</p>'], ['',''], $text);
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
        $isTimerEnabled = $this->getData('use_timer');
        $isTimerProvided = $this->hasData('salebar_timer');
        $timerDate = $this->getFinalTime();

        if (!$isTimerEnabled || !$isTimerProvided || !$timerDate) {
            return true;
        }

        $finalTime = $this->getFinalTime();
        $currentTime = $this->getCurrentTime();

        return $currentTime < $finalTime;
    }

    public function toHtml(): string
    {
        $output = parent::_toHtml();

        return $this->filter->filter($output);
    }

    protected function getCurrentTime(): int
    {
        $currentTime = $this->datetime->gmtTimestamp();
        $timezone = $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_TIMEZONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $dateTimeZone = new \DateTimeZone($timezone);
        $dateTime = new \DateTime('@' . $currentTime);
        $dateTime->setTimezone($dateTimeZone);

        return strtotime($dateTime->format('d-m-Y H:i:s'));
    }

    protected function isBase64Encoded(string $string): bool
    {
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }

        $decoded = base64_decode($string, true);

        if ($decoded === false || base64_encode($decoded) !== str_replace(["\r", "\n"], '', $string)) {
            return false;
        }

        return true;
    }
}
