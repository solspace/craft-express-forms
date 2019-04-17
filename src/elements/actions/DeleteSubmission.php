<?php

namespace Solspace\ExpressForms\elements\actions;

use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Solspace\ExpressForms\ExpressForms;

class DeleteSubmission extends ElementAction
{
    /** @var string */
    public $confirmationMessage;

    /** @var string */
    public $successMessage;

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return ExpressForms::t('Delete...');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }

    /**
     * Performs the action on any elements that match the given criteria.
     *
     * @param ElementQueryInterface $query
     *
     * @return bool
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        foreach ($query->all() as $element) {
            \Craft::$app->getElements()->deleteElement($element);
        }

        $this->setMessage($this->successMessage);

        return true;
    }
}
