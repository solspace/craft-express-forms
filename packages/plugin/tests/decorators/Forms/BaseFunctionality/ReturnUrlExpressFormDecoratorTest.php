<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\BaseFunctionality;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\ReturnUrlExpressFormDecorator;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\View\RenderProviderInterface;
use yii\base\Event;

/**
 * @internal
 * @coversNothing
 */
class ReturnUrlExpressFormDecoratorTest extends TestCase
{
    /** @var ReturnUrlExpressFormDecorator */
    private $returnUrlDecorator;

    /** @var MockObject|RenderProviderInterface */
    private $rendererMock;

    protected function setUp(): void
    {
        $this->rendererMock = $this->createMock(RenderProviderInterface::class);

        $this->returnUrlDecorator = new ReturnUrlExpressFormDecorator(
            $this->rendererMock,
            $this->createMock(LoggerProviderInterface::class)
        );
        $this->returnUrlDecorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->returnUrlDecorator->destructEventListeners();
    }

    public function testCallsRedirectOnSubmit()
    {
        $form = new Form();
        $form->getOpenTag(['return' => '/some/url']);

        $submission = $this->createMock(Submission::class);

        $this->rendererMock
            ->expects($this->once())
            ->method('renderObjectTemplate')
            ->with('/some/url')
            ->willReturn('compiled/return-url')
        ;

        $event = new FormRedirectEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_REDIRECT,
            $event
        );

        self::assertSame('compiled/return-url', $event->getRedirectUrl());
    }
}
