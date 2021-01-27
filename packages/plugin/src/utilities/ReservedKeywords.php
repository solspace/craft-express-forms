<?php

namespace Solspace\ExpressForms\utilities;

class ReservedKeywords
{
    /**
     * TODO: Implement this in form saving process
     * TODO: Add event firing, so that other processes can update this.
     *
     * @return array
     */
    public static function getReservedFieldHandles()
    {
        return ['formId'];
    }
}
