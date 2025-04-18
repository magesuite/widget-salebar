<?php

namespace MageSuite\WidgetSalebar\Block\Widget\Salebar;

class Text extends \Magento\Backend\Block\Widget\Form\Element
{
    protected \Magento\Cms\Model\Wysiwyg\Config $_wysiwygConfig; //phpcs:ignore
    protected \Magento\Framework\Data\Form\Element\Factory $_factoryElement; //phpcs:ignore

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        $data = []
    ) {
        $this->_factoryElement = $factoryElement;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $editor = $this->_factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setForm($element->getForm())
            ->setWysiwyg(true)
            ->setConfig($this->_wysiwygConfig->getConfig(['add_variables' => true, 'add_widgets' => true]));

        if ($this->isBase64Encoded($element->getValue())) {
            $editor->setValue(base64_decode($editor->getValue()));
        }

        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            $this->_getAfterElementHtml() . $editor->getElementHtml()
        );

        return $element;
    }

    //phpcs:disable
    protected function _getAfterElementHtml()
    {
    $html = <<<HTML
        <style>
            .admin__field-control.control .control-value {
                display: none !important;
            }
        </style>
HTML;

        return $html;
    }
    //phpcs:enable

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
