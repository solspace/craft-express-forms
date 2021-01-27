<?php

namespace Solspace\ExpressForms\twig\filters;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ClassFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('class', function ($input) {
                return \get_class($input);
            }),
        ];
    }
}
