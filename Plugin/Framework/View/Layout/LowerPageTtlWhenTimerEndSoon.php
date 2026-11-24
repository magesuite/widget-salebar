<?php

declare(strict_types=1);

namespace MageSuite\WidgetSalebar\Plugin\Framework\View\Layout;

class LowerPageTtlWhenTimerEndSoon
{
    public function __construct( // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
        protected \Magento\Framework\App\ResponseInterface $response,
        protected \Magento\Framework\Registry $registry,
        protected \Magento\PageCache\Model\Config $pageConfig,
        protected \MageSuite\WidgetSalebar\Model\StoreTime $storeTime
    ) {
    }

    public function afterGetOutput(\Magento\Framework\View\Layout $subject, $result)
    {
        $visibilityChangeTimestamp = $this->registry->registry(\MageSuite\WidgetSalebar\Block\Widget\Salebar::VISIBILITY_CHANGE_REGISTRY_KEY);
        $currentTtl = $this->getCurrentPublicTtl();

        if (empty($visibilityChangeTimestamp) || !$subject->isCacheable() || $currentTtl === null) {
            return $result;
        }

        $secondsToVisibilityChange = $visibilityChangeTimestamp - $this->storeTime->getCurrentTimestamp();

        if ($secondsToVisibilityChange >= $currentTtl) {
            return $result;
        }

        if ($secondsToVisibilityChange <= 0) {
            $this->response->setNoCacheHeaders();

            return $result;
        }

        $this->response->setPublicHeaders($secondsToVisibilityChange);

        return $result;
    }

    protected function getCurrentPublicTtl(): ?int
    {
        $cacheControlHeader = $this->response->getHeader('Cache-Control');
        $cacheControl = $cacheControlHeader ? $cacheControlHeader->getFieldValue() : '';

        if (!preg_match('/\bpublic\b/', $cacheControl) || !preg_match('/\bmax-age=(\d+)/', $cacheControl, $matches)) {
            return null;
        }

        return min((int)$this->pageConfig->getTtl(), (int)$matches[1]);
    }
}
