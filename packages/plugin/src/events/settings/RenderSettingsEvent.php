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
     */
    public function __construct(Settings $settings, string $selectedItem)
    {
        $this->settings = $settings;
        $this->selectedItem = $selectedItem;

        parent::__construct();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getSelectedItem(): string
    {
        return $this->selectedItem;
    }

    public function getContent(): Markup
    {
        return new Markup(implode("\n<hr />\n", $this->contentChunks), 'utf-8');
    }

    /**
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

    public function setContentChunks(array $contentChunks): self
    {
        $this->contentChunks = $contentChunks;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getActionButton()
    {
        return $this->actionButton;
    }

    /**
     * @param string $actionButton
     */
    public function setActionButton(string $actionButton = null): self
    {
        $this->actionButton = $actionButton;

        return $this;
    }

    public function isAllowViewingWithoutAdminChanges(): bool
    {
        return $this->allowViewingWithoutAdminChanges;
    }

    /**
     * @return $this
     */
    public function setAllowViewingWithoutAdminChanges(bool $allowViewingWithoutAdminChanges): self
    {
        $this->allowViewingWithoutAdminChanges = $allowViewingWithoutAdminChanges;

        return $this;
    }
}
