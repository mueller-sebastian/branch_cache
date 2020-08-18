<?php
declare(strict_types=1);

namespace Cobweb\BranchCache\ContextMenu;

/*
 * This file is part of the Cobweb/BranchCache project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;

/**
 * Class ItemProvider
 */
class ItemProvider extends PageProvider
{
    /**
     * @var array
     */
    protected $itemsConfiguration = [
        'clearBranchCache' => [
            'type' => 'item',
            'label' => 'LLL:EXT:branch_cache/Resources/Private/Language/locallang.xlf:clear.branch.cache',
            'iconIdentifier' => 'actions-system-cache-clear-impact-medium',
            'callbackAction' => 'clearBranchCache'
        ],
    ];

    /**
     * @param string $itemName
     * @param string $type
     * @return bool
     */
    protected function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }
        return $this->canClearCache();
    }

    /**
     * This method adds custom item to list of items generated by item providers with higher priority value (PageProvider)
     * You could also modify existing items here.
     * The new item is added after the 'info' item.
     *
     * @param array $items
     * @return array
     */
    public function addItems(array $items): array
    {
        $this->initDisabledItems();

        // renders an item based on the configuration from $this->itemsConfiguration
        $localItems = $this->prepareItems($this->itemsConfiguration);

        if (!isset($items['clearCache'])) {
            $items = \array_merge($items, $localItems);
        } else {
            // add this entry after an existing clearCache entry
            $position = array_search('clearCache', array_keys($items), true);

            //slices array into two parts
            $beginning = array_slice($items, 0, $position + 1, true);
            $end = array_slice($items, $position, null, true);

            // adds custom item in the correct position
            $items = $beginning + $localItems + $end;
        }

        return $items;
    }

    /**
     * This priority should be lower than priority of the PageProvider, so it's evaluated after the PageProvider
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 60;
    }

    /**
     * @param string $itemName
     * @return array
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        return [
            'data-callback-module' => 'TYPO3/CMS/BranchCache/ContextMenuActions',
        ];
    }

    /**
     * Defaults are not good for multisite setups, allowing options.clearCache.pages would add a global
     * clear cache button for ALL pages, even if the user does not see them. Thus we omit this check and
     * allow clearing the branch cache, that's what this extension is supposed to do
     *
     * @return bool
     */
    protected function canClearCache(): bool
    {
        return true;
    }
}
