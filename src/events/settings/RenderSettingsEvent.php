<?php

namespace Solspace\ExpressForms\events\settings;

use Solspace\ExpressForms\models\Settings;
use Twig\Markup;
use yii\base\Event;

class RenderSettingsEvent extends Event
{
    /** @var Settings */
    private $settings;

    /** @var array */
    private $contentChunks = [];

    /** @var string */
    private $title = 'Settings';

    /** @var string */
    private $actionButton;

    /** @var string */
    private $selectedItem;

    /** @var bool */
    private $allowViewingWithoutAdminChanges = false;

    /**
     * RenderSettingsEvent constructor.
     *
     * @param Settings $settings
     * @param string   $selectedItem
     */
    public function __construct(Settings $settings, string $selectedItem)
    {
        $this->settings     = $settings;
        $this->selectedItem = $selectedItem;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return RenderSettingsEvent
     */
    public function setTitle(string $title): RenderSettingsEvent
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getSelectedItem(): string
    {
        return $this->selectedItem;
    }

    /**
     * @return Markup
     */
    public function getContent(): Markup
    {
        return new Markup(implode("\n<hr />\n", $this->contentChunks), 'utf-8');
    }

    /**
     * @param string   $content
     * @param int|null $atIndex
     *
     * @return $this
     */
    public function addContent(string $content, int $atIndex = null): self
    {
        if (null !== $atIndex) {
            array_splice($this->contentChunks, $atIndex, 0, $content);
        } else {
            $this->contentChunks[] = $content;
        }

        return $this;
    }

    /**
     * @param array $contentChunks
     *
     * @return RenderSettingsEvent
     */
    public function setContentChunks(array $contentChunks): RenderSettingsEvent
    {
        $this->contentChunks = $contentChunks;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionButton()
    {
        return $this->actionButton;
    }

    /**
     * @param string $actionButton
     *
     * @return RenderSettingsEvent
     */
    public function setActionButton(string $actionButton = null): RenderSettingsEvent
    {
        $this->actionButton = $actionButton;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowViewingWithoutAdminChanges(): bool
    {
        return $this->allowViewingWithoutAdminChanges;
    }

    /**
     * @param bool $allowViewingWithoutAdminChanges
     *
     * @return $this
     */
    public function setAllowViewingWithoutAdminChanges(bool $allowViewingWithoutAdminChanges): self
    {
        $this->allowViewingWithoutAdminChanges = $allowViewingWithoutAdminChanges;

        return $this;
    }
}
