<?php

namespace Solspace\ExpressForms\variables;

use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Form;

class ExpressFormsVariable
{
    public function form(int|string $idOrHandle): ?Form
    {
        return ExpressForms::getInstance()->forms->getFormByIdOrHandle($idOrHandle);
    }

    public function name(): string
    {
        return ExpressForms::getInstance()->name;
    }

    public function isPro(): bool
    {
        return ExpressForms::getInstance()->isPro();
    }

    public function isLite(): bool
    {
        return ExpressForms::getInstance()->isLite();
    }
}
