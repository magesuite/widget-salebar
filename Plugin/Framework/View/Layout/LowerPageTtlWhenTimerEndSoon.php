<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Plugin\Framework\View\Layout;

class LowerPageTtlWhenTimerEndSoon
{
    protected \Magento\Framework\App\ResponseInterface $response;
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Framework\View\DesignInterface $design;
    protected \Magento\PageCache\Model\Config $pageConfig;

    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Registry $registry,
        \Magento\PageCache\Model\Config $pageConfig
    ) {
        $this->pageConfig = $pageConfig;
        $this->registry = $registry;
        $this->response = $response;
    }

    public function afterGetOutput(\Magento\Framework\View\Layout $subject, $result)
    {
        $ttlFromConfig = $this->pageConfig->getTtl();

        $salebarVisibilityTimestamp = $this->registry->registry('salebar_timestamp');

        if (empty($salebarVisibilityTimestamp)) {
            return $result;
        }

        $secondsToNearestVisibilityChange = $salebarVisibilityTimestamp - time();
        $existingTtl = $this->response->getHeader('Cache-Control');

        if ($existingTtl && preg_match('/max-age=(\d+)/', $existingTtl->getFieldValue(), $matches)) {
            $existingTtlValue = (int)$matches[1];
            if ($existingTtlValue < $ttlFromConfig) {
                $ttlFromConfig = $existingTtlValue;
            }
        }

        if ($ttlFromConfig <= $secondsToNearestVisibilityChange) {
            return $result;
        }

        if ($secondsToNearestVisibilityChange > time() && $secondsToNearestVisibilityChange < $ttlFromConfig) {
            $this->response->setPublicHeaders($secondsToNearestVisibilityChange);
        }

        return $result;
    }
}
