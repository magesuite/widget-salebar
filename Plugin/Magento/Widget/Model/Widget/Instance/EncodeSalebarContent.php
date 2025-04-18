<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Plugin\Magento\Widget\Model\Widget\Instance;

class EncodeSalebarContent
{
    public function beforeSave(\Magento\Widget\Model\Widget\Instance $subject): void
    {
        $params = $subject->getWidgetParameters();

        if (isset($params['salebar_text'])) {
            $params['salebar_text'] = base64_encode($params['salebar_text']);
            $subject->setWidgetParameters($params);
        }
    }
}
