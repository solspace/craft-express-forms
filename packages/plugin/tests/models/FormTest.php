<?php

namespace Solspace\Tests\ExpressForms\models;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\events\fields\FieldValidateEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormValidateEvent;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\models\Form;
use yii\base\Event;

/**
 * @internal
 *
 * @coversNothing
 */
class FormTest extends TestCase
{
    public function testGetOpenTag()
    {
        $form = new Form();
        $result = $form->getOpenTag()->jsonSerialize();

        self::assertSame('<form method="post">', $result);
    }

    public function testGetOpenTagCallsEvent()
    {
        $form = new Form();

        Event::on(
            Form::class,
            Form::EVENT_RENDER_OPENING_TAG,
            function (FormRenderTagEvent $event) {
                $event->appendToOutput('custom content appended SALTY');
            }
        );

        $result = $form->getOpenTag()->jsonSerialize();

        Event::off(Form::class, Form::EVENT_RENDER_OPENING_TAG);

        self::assertStringContainsString('custom content appended SALTY', $result);
    }

    public function testGetCloseTag()
    {
        $form = new Form();
        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringContainsString('</form>', $result);
    }

    public function testGetCloseTagCallsEvent()
    {
        $form = new Form();

        Event::on(
            Form::class,
            Form::EVENT_RENDER_CLOSING_TAG,
            function (FormRenderTagEvent $event) {
                $event->prependToOutput('custom CLOSING TAG content');
                $event->appendToOutput('!!end salt!!');
            }
        );

        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringStartsWith('custom CLOSING TAG content', $result);
        self::assertStringEndsWith('!!end salt!!', $result);
    }

    public function testSubmit()
    {
        $form = new Form();

        self::assertFalse($form->isSubmitted());
        $form->submit([]);
        self::assertTrue($form->isSubmitted());
    }

    public function testSubmitTwiceThrows()
    {
        $this->expectException(\Solspace\ExpressForms\exceptions\Form\FormAlreadySubmittedException::class);
        $this->expectExceptionMessage('Form has already been submitted');

        $form = new Form();
        $form->submit([]);
        $form->submit([]);
    }

    public function testSubmitCallsFormValidateEvent()
    {
        $form = new Form();
        $form->setHandle('test');

        Event::on(
            Form::class,
            Form::EVENT_VALIDATE_FORM,
            function (FormValidateEvent $event) {
                $event->getForm()->setHandle('change of handle');
            }
        );

        self::assertSame('test', $form->getHandle());
        $form->submit(['test' => null]);
        self::assertSame('change of handle', $form->getHandle());
    }

    public function testSubmitCallsFieldValidateEvent()
    {
        $field = new Text();
        $field->uid = 'test';
        $field->required = true;
        $field->handle = 'test';

        $form = new Form();
        $form->addField($field);

        Event::on(
            Form::class,
            Form::EVENT_VALIDATE_FIELD,
            function (FieldValidateEvent $event) {
                $event->getField()->handle = 'change of handle';
            }
        );

        self::assertSame('test', $field->getHandle());
        $form->submit(['test' => null]);
        self::assertSame('change of handle', $field->getHandle());
    }

    public function testSubmitWithErrorsMakesFormInvalid()
    {
        $form = new Form();

        self::assertTrue($form->isValid());
        $form->addError('test error');
        $form->submit([]);
        self::assertFalse($form->isValid());
    }

    public function testMarkingFormAsInvalidKeepsItInvalid()
    {
        $form = new Form();

        self::assertTrue($form->isValid());
        $form->setValid(false);
        $form->submit([]);
        self::assertFalse($form->isValid());
    }

    public function testSubmitInsertsValuesInFields()
    {
        $field = new Text();
        $field->uid = 'test';
        $field->handle = 'test';

        $form = new Form();
        $form->addField($field);

        self::assertNull($field->getValue());
        $form->submit(['test' => 'new Value']);
        self::assertSame('new Value', $field->getValue());
    }

    public function testOpenTagParsesHtmlAttributes()
    {
        $form = new Form();
        $result = $form->getOpenTag(['attributes' => ['one' => 'testing', 'two' => 'testing again']])->jsonSerialize();

        $expected = '<form method="post" one="testing" two="testing again">';

        self::assertSame($expected, $result);
        self::assertCount(3, $form->getHtmlAttributes());
        self::assertSame('testing', $form->getHtmlAttributes()->get('one'));
        self::assertSame('testing again', $form->getHtmlAttributes()->get('two'));
    }

    public function testOpenTagParsesParameterBag()
    {
        $form = new Form();
        $form->getOpenTag(['return' => true, 'other' => 'four', 'yet' => 'another']);

        self::assertCount(3, $form->getParameters());
        self::assertTrue($form->getParameters()->get('return'));
        self::assertSame('four', $form->getParameters()->get('other'));
        self::assertSame('another', $form->getParameters()->get('yet'));
    }

    public function testExtraParamsAccessibleViaMagicGet()
    {
        $form = new Form();

        self::assertNull($form->magic);
        $form->getExtraParameters()->add('test', 'value');
        self::assertSame('value', $form->test);
    }
}
