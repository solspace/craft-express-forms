<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Extras;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\Forms\Extras\HoneypotDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\RecaptchaDecorator;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\factories\IntegrationMappingFactory;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\models\Settings;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;
use Twig\Markup;

/**
 * @internal
 * @coversNothing
 */
class RecaptchaDecoratorTest extends TestCase
{
    /** @var HoneypotDecorator */
    private $honeypotDecorator;

    /** @var MockObject|RequestProviderInterface */
    private $requestMock;

    /** @var MockObject|SettingsProviderInterface */
    private $settings;

    /** @var MockObject|TranslatorInterface */
    private $translator;

    /** @var FormFactory */
    private $formFactory;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestProviderInterface::class);
        $this->settings = $this->createMock(SettingsProviderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->honeypotDecorator = new RecaptchaDecorator(
            $this->requestMock,
            $this->settings,
            $this->translator
        );

        $this->honeypotDecorator->initEventListeners();

        $this->formFactory = new FormFactory($this->createMock(IntegrationMappingFactory::class));
    }

    protected function tearDown(): void
    {
        $this->honeypotDecorator->destructEventListeners();
    }

    public function testAddRecaptchaScriptIfEnabled()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaLoadScript = true;
        $model->recaptchaSiteKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);
        $form->recaptcha->render();

        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringContainsString(
            '<script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            $result
        );
    }

    public function testDoesNotAddRecaptchaScriptIfDisabled()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaLoadScript = false;
        $model->recaptchaSiteKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);
        $form->recaptcha->render();

        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringNotContainsString(
            '<script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            $result
        );
    }

    public function testDoesNotAddRecaptchaScriptIfNotRendered()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaLoadScript = true;
        $model->recaptchaSiteKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);

        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringNotContainsString(
            '<script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            $result
        );
    }

    public function testDoesNotAddRecaptchaScriptWhenRecaptchaDisabled()
    {
        $model = new Settings();
        $model->recaptchaEnabled = false;
        $model->recaptchaLoadScript = true;
        $model->recaptchaSiteKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);
        $form->recaptcha->render();

        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringNotContainsString(
            '<script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            $result
        );
    }

    public function testRecaptchaAttachedToFormWhenEnabled()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaSiteKey = 'custom site key';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);

        self::assertInstanceOf(Markup::class, $form->recaptcha->render());
        self::assertSame(
            '<div class="g-recaptcha" data-sitekey="custom site key" data-theme="light"></div>',
            (string) $form->recaptcha
        );
    }

    public function testRecaptchaDoesNotRenderOnNoKey()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);

        self::assertSame('', (string) $form->recaptcha);
    }

    public function testRecaptchaNotAttachedWhenDisabled()
    {
        $model = new Settings();
        $model->recaptchaEnabled = false;
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);

        self::assertSame('', (string) $form->recaptcha);
    }

    public function testErrorsOnValidateRecaptchaIfNothingInPost()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaSiteKey = 'test';
        $model->recaptchaSecretKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $this->requestMock
            ->expects($this->once())
            ->method('post')
            ->with('g-recaptcha-response')
            ->willReturn(null)
        ;

        $this->translator
            ->expects($this->once())
            ->method('translate')
            ->with('express-forms', 'Please verify that you are not a robot.')
            ->willReturn('Please verify that you are not a robot.')
        ;

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);
        $form->recaptcha->render();

        $form->getCloseTag()->jsonSerialize();
        $form->submit([]);

        self::assertFalse($form->isValid());
        self::assertSame('Please verify that you are not a robot.', $form->getErrorsAsString());
        self::assertSame(['Please verify that you are not a robot.'], $form->recaptcha->getErrors());
    }

    public function testDoesNotValidateIfRecaptchaNotRendered()
    {
        $model = new Settings();
        $model->recaptchaEnabled = true;
        $model->recaptchaSiteKey = 'test';
        $model->recaptchaSecretKey = 'test';
        $this->settings->method('get')->willReturn($model);

        $form = new Form();
        $this->formFactory->populateFromArray($form, []);

        $form->getCloseTag()->jsonSerialize();
        $form->submit([]);

        self::assertTrue($form->isValid());
    }
}
