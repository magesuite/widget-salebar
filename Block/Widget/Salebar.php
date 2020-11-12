<?php

namespace MageSuite\WidgetSalebar\Block\Widget;

class Salebar extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    const TIMER_KEYWORD = '%TIMER%';

    const TIMER_DIV_CLASS = '<span class="cs-salebar-widget__countdown"></span>';

    protected $_template = 'widget/salebar.phtml';

    /**
     * @var \Magento\Cms\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        array $data = []
    )
    {
        $this->filter = $filter;
        $this->datetime = $datetime;

        parent::__construct($context, $data);
    }

    public function getText(){
        $text = str_replace(['<p>','</p>'],['',''], $this->getData('salebar_text'));
        $text = str_replace(self::TIMER_KEYWORD, self::TIMER_DIV_CLASS, $text);
        return $this->filter->filter($text);
    }

    public function getFinalTime(){
        $finalDate = $this->getData('salebar_timer');
        return ($this->datetime->gmtTimestamp($finalDate));
    }

    public function toHtml(){
        $output = parent::_toHtml();
        return $this->filter->filter($output);;
    }
}
