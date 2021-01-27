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
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        parent::__construct();
    }

    public function getSidebarItems(): array
    {
        return $this->sidebarItems;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function addItem(string $name, string $handle = null, string $beforeItem = null): self
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
