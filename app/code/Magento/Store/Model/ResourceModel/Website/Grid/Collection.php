<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\ResourceModel\Website\Grid;

class Collection extends \Magento\Store\Model\ResourceModel\Website\Collection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_map['fields']['store_id'] = 'store_table.store_id';
        $this->_map['fields']['store_title'] = 'store_table.name';
        $this->_map['fields']['group_id'] = 'group_table.group_id';
        $this->_map['fields']['group_title'] = 'group_table.name';
        $this->_map['fields']['name'] = 'main_table.name';
    }

    /**
     * @inheritDoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinGroupAndStore();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        if (!array_key_exists('main_table.name', $this->_orders)) {
            $this->unshiftOrder('main_table.name', self::SORT_ORDER_ASC);
        }

        return $this->loadWithFilter($printQuery, $logQuery);
    }

    /**
     * @inheritdoc
     */
    public function joinGroupAndStore()
    {
        if (!$this->getFlag('groups_and_stores_joined')) {
            $this->_idFieldName = 'website_group_store';
            $this->getSelect()->joinLeft(
                ['group_table' => $this->getTable('store_group')],
                'main_table.website_id = group_table.website_id',
                [
                    'group_id',
                    'group_title' => 'name',
                    'group_code' => 'code',
                    'default_store_id',
                    'root_category_id'
                ]
            )->joinLeft(
                ['store_table' => $this->getTable('store')],
                'group_table.group_id = store_table.group_id',
                [
                    'store_id',
                    'store_title' => 'name',
                    'store_code' => 'code',
                    'store_active' => 'is_active'
                ]
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        // Columns in grid are rendered and sorted by store, group and website names, but should be filtered by ids
        if ($field === 'store_title') {
            $field = 'store_id';
        } elseif ($field === 'group_title') {
            $field = 'group_id';
        } elseif ($field === 'name') {
            $field = 'website_id';
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
