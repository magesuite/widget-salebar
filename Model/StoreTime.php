<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Model;

class StoreTime
{
    public function __construct(
        protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        protected \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
    }

    public function getCurrentTimestamp(): int
    {
        return (int)$this->dateTime->gmtTimestamp();
    }

    public function getTimestamp(string $storeDateTime): ?int
    {
        try {
            $date = new \DateTime($storeDateTime, new \DateTimeZone($this->timezone->getConfigTimezone()));
        } catch (\Exception $e) {
            return null;
        }

        return $date->getTimestamp();
    }
}
