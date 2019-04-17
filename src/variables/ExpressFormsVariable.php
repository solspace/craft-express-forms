<?php

namespace Solspace\ExpressForms\variables;

use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Form;

class ExpressFormsVariable
{
    /**
     * @param string|int $idOrHandle
     *
     * @return Form|null
     */
    public function form($idOrHandle)
    {
        return ExpressForms::getInstance()->forms->getFormByIdOrHandle($idOrHandle);
    }

    /**
     * @return string
     */
    public function name():string
    {
        return ExpressForms::getInstance()->name;
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return ExpressForms::getInstance()->isPro();
    }

    /**
     * @return bool
     */
    public function isLite(): bool
    {
        return ExpressForms::getInstance()->isLite();
    }
}
