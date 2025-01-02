<?php

namespace MageSuite\WidgetSalebar\Block\Widget;

class Salebar extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    public const TIMER_KEYWORD = '%TIMER%';
    public const TIMER_DIV_CLASS = '<span class="cs-salebar-widget__countdown"></span>';

    protected $_template = 'widget/salebar.phtml'; //phpcs:ignore

    protected \Magento\Framework\Stdlib\DateTime\DateTime $datetime;
    protected \Magento\Cms\Model\Template\Filter $filter;
    protected \Magento\Framework\Registry $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->filter = $filter;
        $this->datetime = $datetime;
        $this->registry = $registry;

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
        // TODO - implement this method, because it does not correctly compares dates (timezone maybe?)
        $finalTime = $this->getFinalTime();
        $currentTime = $this->datetime->gmtTimestamp();

        return $currentTime < $finalTime;
    }

    public function toHtml()
    {
        $output = parent::_toHtml();

        return $this->filter->filter($output);
    }
}
