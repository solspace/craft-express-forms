<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\BaseFunctionality;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\DefaultActionDecorator;
use Solspace\ExpressForms\models\Form;

/**
 * @internal
 *
 * @coversNothing
 */
class DefaultActionDecoratorTest extends TestCase
{
    /** @var DefaultActionDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->decorator = new DefaultActionDecorator();
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testAttachesDefaultActionIfNoActionSpecified()
    {
        $form = new Form();

        $result = $form->getOpenTag()->jsonSerialize();
        $expected = '<input type="hidden" name="action" value="express-forms/submit" />';

        self::assertStringContainsString($expected, $result);
    }

    public function testNoDefaultActionIfActionSpecifiedViaClassProperty()
    {
        $form = new Form();
        $form->getHtmlAttributes()->add('action', 'test-action');

        $result = $form->getOpenTag()->jsonSerialize();

        self::assertStringContainsString('<form method="post" action="test-action">', $result);
        self::assertStringNotContainsString('name="action"', $result);
    }

    public function testNoDefaultActionIfActionSpecifiedViaTagConfig()
    {
        $form = new Form();

        $result = $form->getOpenTag(['attributes' => ['action' => 'test-action']])->jsonSerialize();

        self::assertStringContainsString('<form method="post" action="test-action">', $result);
        self::assertStringNotContainsString('name="action"', $result);
    }
}
