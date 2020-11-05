<?php

namespace MageSuite\WidgetSalebar\Block\Widget\Salebar;

class Text extends \Magento\Backend\Block\Widget\Form\Element {

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var Factory
     */
    protected $_factoryElement;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, $data = []
    ) {
        $this->_factoryElement = $factoryElement;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $editor = $this->_factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setForm($element->getForm())
            ->setWysiwyg(true)
            ->setConfig($this->_wysiwygConfig->getConfig(['add_variables' => true, 'add_widgets' => true]));

        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        $element->setData(
            'after_element_html', $this->_getAfterElementHtml() . $editor->getElementHtml()
        );

        return $element;
    }

    protected function _getAfterElementHtml() {
    $html = <<<HTML
        <style>
            .admin__field-control.control .control-value {
                display: none !important;
            }
        </style>
HTML;

        return $html;
    }

}
