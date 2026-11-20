<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Model;

class StoreTime
{
    public function __construct(protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone)
    {
    }

    public function getCurrentTimestamp(): int
    {
        $storeDate = $this->timezone->date();

        return $storeDate->getTimestamp() + $storeDate->getOffset();
    }
}
