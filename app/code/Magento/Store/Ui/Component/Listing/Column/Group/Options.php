<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Ui\Component\Listing\Column\Group;

use Magento\Store\Model\Group;

class Options extends \Magento\Store\Ui\Component\Listing\Column\Store\Options
{
    /**
     * @inheritDoc
     */
    protected function generateCurrentOptions(): void
    {
        $groupCollection = $this->systemStore->getGroupCollection();
        /** @var Group $group */
        foreach ($groupCollection as $group) {
            $name = $this->sanitizeName($group->getName());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = $group->getId();
        }
    }
}
