<?php

namespace Solspace\ExpressForms\elements\actions;

use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Solspace\ExpressForms\ExpressForms;

class DeleteSubmission extends ElementAction
{
    public string $confirmationMessage = '';
    public string $successMessage = '';

    public function getTriggerLabel(): string
    {
        return ExpressForms::t('Delete...');
    }

    public static function isDestructive(): bool
    {
        return true;
    }

    public function getConfirmationMessage(): ?string
    {
        return $this->confirmationMessage;
    }

    public function performAction(ElementQueryInterface $query): bool
    {
        foreach ($query->all() as $element) {
            \Craft::$app->getElements()->deleteElement($element);
        }

        $this->setMessage($this->successMessage);

        return true;
    }
}
