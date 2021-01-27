<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Extras;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\Forms\Extras\DynamicNotificationsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\DynamicRecipientsDecorator;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProviderInterface;
use yii\base\Event;

/**
 * @internal
 * @coversNothing
 */
class DynamicRecipientsDecoratorTest extends TestCase
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

        $this->decorator = new DynamicRecipientsDecorator(
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

    public function testDoesNotSendEmailsOnEmptyMap()
    {
        $form = new Form();
        $form->getOpenTag(['dynamicRecipients' => ['targetField' => ['template' => 'some.twig']]]);

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
        $field = new Text();
        $field->uid = 'test-uid';
        $field->handle = 'targetField';
        $field->setValue('testy');

        $form = new Form();
        $form->addField($field);

        $form->getOpenTag(['dynamicRecipients' => ['targetField' => ['map' => ['test' => 'email@example.com']]]]);

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
        $field = new Text();
        $field->uid = 'test-uid';
        $field->handle = 'targetField';
        $field->setValue('test');

        $form = new Form();
        $form
            ->addField($field)
            ->getOpenTag(
                [
                    'dynamicRecipients' => [
                        'targetField' => [
                            'template' => 'template.twig',
                            'map' => [
                                'test' => 'email@example.com',
                            ],
                        ],
                    ],
                ]
            )
        ;

        $notification = $this->createMock(EmailNotification::class);
        $submission = $this->createMock(Submission::class);

        $this->notificationsMock
            ->expects($this->once())
            ->method('getNotification')
            ->with('template.twig')
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

    public function testSendsEmailToMultipleRecipients()
    {
        $field = new Text();
        $field->uid = 'test-uid';
        $field->handle = 'targetField';
        $field->setValue(['one', 'three']);

        $form = new Form();
        $form
            ->addField($field)
            ->getOpenTag(
                [
                    'dynamicRecipients' => [
                        'targetField' => [
                            'template' => 'template.twig',
                            'map' => [
                                'one' => 'email@example.com, other@example.com',
                                'two' => 'not-chosen@example.com',
                                'three' => ['test2@test.com', 'test3@test.com'],
                            ],
                        ],
                    ],
                ]
            )
        ;

        $notification = $this->createMock(EmailNotification::class);
        $submission = $this->createMock(Submission::class);

        $this->notificationsMock
            ->expects($this->once())
            ->method('getNotification')
            ->with('template.twig')
            ->willReturn($notification)
        ;

        $this->notificationsMock
            ->expects($this->once())
            ->method('sendEmail')
            ->with(
                [
                    'email@example.com',
                    'other@example.com',
                    'test2@test.com',
                    'test3@test.com',
                ],
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

    public function testDoesNotSendToAnyoneIfValueNotInMap()
    {
        $field = new Text();
        $field->uid = 'test-uid';
        $field->handle = 'targetField';
        $field->setValue('non-existing');

        $form = new Form();
        $form
            ->addField($field)
            ->getOpenTag(
                [
                    'dynamicRecipients' => [
                        'targetField' => [
                            'template' => 'template.twig',
                            'map' => [
                                'one' => 'email@example.com, other@example.com',
                                'two' => 'not-chosen@example.com',
                                'three' => ['test2@test.com', 'test3@test.com'],
                            ],
                        ],
                    ],
                ]
            )
        ;

        $notification = $this->createMock(EmailNotification::class);
        $submission = $this->createMock(Submission::class);

        $this->notificationsMock
            ->expects($this->once())
            ->method('getNotification')
            ->with('template.twig')
            ->willReturn($notification)
        ;

        $this->notificationsMock
            ->expects($this->never())
            ->method('sendEmail')
        ;

        $event = new FormCompletedEvent($form, $submission, []);
        Event::trigger(
            SubmitController::class,
            SubmitController::EVENT_FORM_COMPLETED,
            $event
        );
    }
}
