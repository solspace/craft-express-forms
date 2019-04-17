<?php

namespace Solspace\ExpressForms\events\settings;

use craft\helpers\StringHelper;
use Solspace\ExpressForms\models\Settings;
use yii\base\Event;

class RegisterSettingSidebarItemsEvent extends Event
{
    /** @var array */
    private $sidebarItems;

    /** @var Settings */
    private $settings;

    /**
     * RegisterSettingsEvent constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getSidebarItems(): array
    {
        return $this->sidebarItems;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @param string      $name
     * @param string|null $handle
     * @param string|null $beforeItem
     *
     * @return RegisterSettingSidebarItemsEvent
     */
    public function addItem(string $name, string $handle = null, string $beforeItem = null): RegisterSettingSidebarItemsEvent
    {
        if (!$handle) {
            $handle = StringHelper::toKebabCase($name);
        }

        if (!isset($this->sidebarItems[$handle])) {
            if ($beforeItem) {
                $beforeItemHandle = StringHelper::toKebabCase($beforeItem);

                $sidebarItems = [];
                foreach ($this->sidebarItems as $itemHandle => $itemName) {
                    if ($itemHandle === $beforeItemHandle) {
                        $sidebarItems[$handle] = $name;
                    }

                    $sidebarItems[$itemHandle] = $itemName;
                }

                $this->sidebarItems = $sidebarItems;
            } else {
                $this->sidebarItems[$handle] = $name;
            }
        }

        return $this;
    }
}
