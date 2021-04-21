<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Extras;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\Extras\HoneypotDecorator;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\models\Settings;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;

/**
 * @internal
 * @coversNothing
 */
class HoneypotDecoratorTest extends TestCase
{
    /** @var HoneypotDecorator */
    private $honeypotDecorator;

    /** @var MockObject|RequestProviderInterface */
    private $requestMock;

    /** @var MockObject|SettingsProviderInterface */
    private $settings;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestProviderInterface::class);
        $this->settings = $this->createMock(SettingsProviderInterface::class);

        $this->honeypotDecorator = new HoneypotDecorator(
            $this->requestMock,
            $this->settings
        );
        $this->honeypotDecorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->honeypotDecorator->destructEventListeners();
    }

    public function testAttachingHoneypotToFormIfEnabled()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $result = $form->getOpenTag()->jsonSerialize();

        self::assertStringContainsString('<input type="text" name="form_handler" autocomplete="express-form form_handler" value="" id="', $result);
    }

    public function testAttachingHoneypotWithCustomName()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;
        $model->honeypotInputName = 'test_honeypot_input_name';

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $result = $form->getOpenTag()->jsonSerialize();

        self::assertStringContainsString('<input type="text" name="test_honeypot_input_name" autocomplete="express-form test_honeypot_input_name" value="" id="', $result);
    }

    public function testHoneypotValidOnEmptyValue()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $this->requestMock
            ->expects($this->once())
            ->method('post')
            ->with('form_handler')
            ->willReturn(null)
        ;

        $form->submit([]);

        self::assertFalse($form->isMarkedAsSpam());
        self::assertTrue($form->isValid());
    }

    public function testHoneypotTriggersMarkAsSpamOnNonEmptyValue()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $this->requestMock
            ->expects($this->once())
            ->method('post')
            ->with('form_handler')
            ->willReturn('abc')
        ;

        $form->submit([]);

        self::assertTrue($form->isMarkedAsSpam());
    }

    public function testHoneypotSimulateSuccess()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $this->requestMock
            ->expects($this->once())
            ->method('post')
            ->with('form_handler')
            ->willReturn('abc')
        ;

        $form->submit([]);

        self::assertTrue($form->isMarkedAsSpam());
        self::assertTrue($form->isValid());
        self::assertEmpty($form->getErrors());
    }

    public function testHoneypotDisplayErrors()
    {
        $form = new Form();

        $model = new Settings();
        $model->honeypotEnabled = true;
        $model->honeypotBehaviour = 'display_errors';

        $this->settings
            ->expects($this->once())
            ->method('get')
            ->willReturn($model)
        ;

        $this->requestMock
            ->expects($this->once())
            ->method('post')
            ->with('form_handler')
            ->willReturn('abc')
        ;

        $form->submit([]);

        self::assertTrue($form->isMarkedAsSpam());
        self::assertFalse($form->isValid());
        self::assertSame(['Form has triggered spam control'], $form->getErrors());
    }
}
