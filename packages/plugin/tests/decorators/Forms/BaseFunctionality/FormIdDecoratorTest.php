<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\BaseFunctionality;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\FormIdDecorator;
use Solspace\ExpressForms\models\Form;

/**
 * @internal
 * @coversNothing
 */
class FormIdDecoratorTest extends TestCase
{
    /** @var FormIdDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->decorator = new FormIdDecorator();
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testAttachFormIdInput()
    {
        $form = new Form();
        $form->setUuid('test uuid for form');

        $result = $form->getOpenTag()->jsonSerialize();
        $expected = '<input type="hidden" name="formId" value="test uuid for form" />';

        self::assertStringContainsString($expected, $result);
    }
}
