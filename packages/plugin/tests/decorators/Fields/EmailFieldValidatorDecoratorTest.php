<?php

namespace Solspace\Tests\ExpressForms\decorators\Fields;

use PHPUnit\Framework\TestCase;
use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\Fields\EmailFieldValidatorDecorator;
use Solspace\ExpressForms\fields\Email;
use Solspace\ExpressForms\models\Form;

/**
 * @internal
 * @coversNothing
 */
class EmailFieldValidatorDecoratorTest extends TestCase
{
    /** @var EmailFieldValidatorDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock
            ->method('translate')
            ->willReturnArgument(1)
        ;

        $this->decorator = new EmailFieldValidatorDecorator($translatorMock);
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testEmptyValueDoesNotIniValidation()
    {
        $field = new Email();
        $field->handle = 'email';
        $field->uid = 'test-uid';

        $form = new Form();
        $form->addField($field);

        $form->submit(['email' => '']);

        self::assertCount(0, $field->getErrors());
    }

    public function invalidEmailProvider(): array
    {
        return [
            ['plainaddress'],
            [']#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            [']    email.example.com'],
            ['email@example@example.com'],
            [']    .email@example.com'],
            ['email.@example.com'],
            ['email..email@example.com'],
            ['あいうえお@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example'],
            ['email@-example.com'],
            ['email@111.222.333.44444'],
            ['email@example..com'],
            ['Abc..123@example.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testInvalidEmailAddsValidationError(string $email)
    {
        $field = new Email();
        $field->handle = 'email';
        $field->uid = 'test-uid';

        $form = new Form();
        $form->addField($field);

        $form->submit(['email' => $email]);

        self::assertCount(1, $field->getErrors(), $email);
        self::assertContains('Email address is not valid', $field->getErrors(), $field->getErrorsAsString());
    }

    public function validEmailProvider(): array
    {
        return [
            ['email@example.com'],
            ['firstname.lastname@example.com'],
            ['email@subdomain.example.com'],
            ['firstname+lastname@example.com'],
            ['email@[123.123.123.123]'],
            ['1234567890@example.com'],
            ['email@example-one.com'],
            ['_______@example.com'],
            ['email@example.name'],
            ['email@example.museum'],
            ['email@example.co.jp'],
            ['firstname-lastname@example.com'],
        ];
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidEmailPassesValidation(string $email)
    {
        $field = new Email();
        $field->handle = 'email';
        $field->uid = 'test-uid';

        $form = new Form();
        $form->addField($field);

        $form->submit(['email' => $email]);

        self::assertCount(0, $field->getErrors(), $email);
    }
}
