<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column\Store\Options;

use Magento\Store\Model\Store;
use Magento\Store\Ui\Component\Listing\Column\Store\Options;

class Flat extends Options
{
    /**
     * @inheritDoc
     */
    protected function generateCurrentOptions(): void
    {
        $storeCollection = $this->systemStore->getStoreCollection();
        /** @var Store $store */
        foreach ($storeCollection as $store) {
            $name = $this->sanitizeName($store->getName());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = $store->getId();
        }
    }
}
