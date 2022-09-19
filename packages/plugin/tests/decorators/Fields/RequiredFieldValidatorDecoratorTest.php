<?php

namespace Solspace\Tests\ExpressForms\decorators\Fields;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Fields\RequiredFieldValidatorDecorator;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\models\Form;

/**
 * @internal
 *
 * @coversNothing
 */
class RequiredFieldValidatorDecoratorTest extends TestCase
{
    /** @var RequiredFieldValidatorDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->decorator = new RequiredFieldValidatorDecorator();
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testPassesWithStringValue()
    {
        $field = new Text();
        $field->handle = 'text';
        $field->uid = 'test-uid';
        $field->required = false;

        $form = new Form();
        $form->addField($field);

        $form->submit(['text' => 'test']);

        self::assertCount(0, $field->getErrors());
    }

    public function testPassesWithArrayValue()
    {
        $field = new Text();
        $field->handle = 'text';
        $field->uid = 'test-uid';
        $field->required = false;

        $form = new Form();
        $form->addField($field);

        $form->submit(['text' => ['test', 'test 2']]);

        self::assertCount(0, $field->getErrors());
    }

    public function testNoValueAddsError()
    {
        $field = new Text();
        $field->handle = 'text';
        $field->uid = 'test-uid';
        $field->required = true;

        $form = new Form();
        $form->addField($field);

        $form->submit([]);

        self::assertCount(1, $field->getErrors(), $field->getErrorsAsString());
        self::assertContains('This field is required', $field->getErrors(), $field->getErrorsAsString());
    }

    public function testEmptyValueAddsError()
    {
        $field = new Text();
        $field->handle = 'text';
        $field->uid = 'test-uid';
        $field->required = true;

        $form = new Form();
        $form->addField($field);

        $form->submit(['text' => '']);

        self::assertCount(1, $field->getErrors(), $field->getErrorsAsString());
        self::assertContains('This field is required', $field->getErrors(), $field->getErrorsAsString());
    }

    public function testEmptyArrayValueAddsError()
    {
        $field = new Text();
        $field->handle = 'text';
        $field->uid = 'test-uid';
        $field->required = true;

        $form = new Form();
        $form->addField($field);

        $form->submit(['text' => []]);

        self::assertCount(1, $field->getErrors(), $field->getErrorsAsString());
        self::assertContains('This field is required', $field->getErrors(), $field->getErrorsAsString());
    }
}
