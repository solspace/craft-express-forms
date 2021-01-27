<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Extras;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\Forms\Extras\DynamicNotificationsDecorator;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProviderInterface;
use yii\base\Event;

/**
 * @internal
 * @coversNothing
 */
class DynamicNotificationsDecoratorTest extends TestCase
{
    /** @var DynamicNotificationsDecorator */
    private $decorator;

    /** @var LoggerProviderInterface|MockObject */
    private $loggerMock;

    /** @var EmailNotificationsProviderInterface|MockObject */
    private $notificationsMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerProviderInterface::class);
        $this->notificationsMock = $this->createMock(EmailNotificationsProviderInterface::class);

        $this->decorator = new DynamicNotificationsDecorator(
            $this->loggerMock,
            $this->notificationsMock
        );
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testDoesNotSendEmailsOnNoConfig()
    {
        $form = new Form();
        $form->getOpenTag();

        $this->notificationsMock
            ->expects($this->never())
            ->method('sendEmail')
        ;
    }

    public function testDoesNotSendEmailsOnEmptyTo()
    {
        $form = new Form();
        $form->getOpenTag(['dynamicNotifications' => ['template' => 'some.twig']]);

        $this->notificationsMock
            ->expects($this->never())
            ->method('sendEmail')
        ;

        $submission = $this->createMock(Submission::class);

        $event = new FormCompletedEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_FORM_COMPLETED,
            $event
        );
    }

    public function testDoesNotSendEmailsOnEmptyTemplate()
    {
        $form = new Form();
        $form->getOpenTag(['dynamicNotifications' => ['to' => ['email@example.com']]]);

        $this->notificationsMock
            ->expects($this->never())
            ->method('sendEmail')
        ;

        $submission = $this->createMock(Submission::class);

        $event = new FormCompletedEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_FORM_COMPLETED,
            $event
        );
    }

    public function testSendsEmail()
    {
        $form = new Form();
        $form->getOpenTag(['dynamicNotifications' => ['to' => ['email@example.com'], 'template' => 'twig.test']]);

        $notification = $this->createMock(EmailNotification::class);
        $submission = $this->createMock(Submission::class);

        $this->notificationsMock
            ->expects($this->once())
            ->method('getNotification')
            ->with('twig.test')
            ->willReturn($notification)
        ;

        $this->notificationsMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                ['email@example.com'],
                $notification,
                $form,
                $submission,
                []
            )
        ;

        $event = new FormCompletedEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_FORM_COMPLETED,
            $event
        );
    }

    public function testConvertsToFromStringToArray()
    {
        $form = new Form();
        $form->getOpenTag(['dynamicNotifications' => ['to' => 'email@example.com,other@email.com', 'template' => 'twig.test']]);

        $notification = $this->createMock(EmailNotification::class);
        $submission = $this->createMock(Submission::class);

        $this->notificationsMock
            ->expects($this->once())
            ->method('getNotification')
            ->with('twig.test')
            ->willReturn($notification)
        ;

        $this->notificationsMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                ['email@example.com', 'other@email.com'],
                $notification,
                $form,
                $submission,
                []
            )
        ;

        $event = new FormCompletedEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_FORM_COMPLETED,
            $event
        );
    }
}
