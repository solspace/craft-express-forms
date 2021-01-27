<?php

namespace Solspace\ExpressForms\variables;

use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Form;

class ExpressFormsVariable
{
    /**
     * @param int|string $idOrHandle
     *
     * @return null|Form
     */
    public function form($idOrHandle)
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
