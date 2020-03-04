<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column\Website;

use Magento\Store\Model\Website;

class Options extends \Magento\Store\Ui\Component\Listing\Column\Store\Options
{
    /**
     * @inheritDoc
     */
    protected function generateCurrentOptions(): void
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        /** @var Website $website */
        foreach ($websiteCollection as $website) {
            $name = $this->sanitizeName($website->getName());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = $website->getId();
        }
    }
}
