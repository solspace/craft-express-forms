<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\BaseFunctionality;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\FormPayloadDecorator;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Security\HashingInterface;
use yii\base\Event;

/**
 * @internal
 * @coversNothing
 */
class FormPayloadDecoratorTest extends TestCase
{
    /** @var FormPayloadDecorator */
    private $decorator;

    /** @var HashingInterface|MockObject */
    private $hashMock;

    protected function setUp(): void
    {
        $this->hashMock = $this->createMock(HashingInterface::class);

        $this->decorator = new FormPayloadDecorator(
            $this->hashMock,
            $this->createMock(LoggerProviderInterface::class)
        );
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testAttachPayloadToFormIfEmptyData()
    {
        $this->hashMock
            ->expects($this->once())
            ->method('encrypt')
            ->with('{"attributes":{"method":"post"},"parameters":[]}', 'test-uuid')
            ->willReturn('encrypted empty data')
        ;

        $form = new Form();
        $form->setUuid('test-uuid');
        $result = $form->getCloseTag()->jsonSerialize();

        self::assertStringContainsStringIgnoringCase('<input type="hidden" name="formPayload" value="encrypted empty data" />', $result);
    }

    public function testAttachPayloadToFormWithCorrectData()
    {
        $this->hashMock
            ->expects($this->once())
            ->method('encrypt')
            ->with(
                '{"attributes":{"method":"post"},"parameters":{"some":"data","other":123,"nested":{"0":"array","1":"of","data":"items"}}}',
                'test-uuid'
            )
            ->willReturn('encrypted data')
        ;

        $form = new Form();
        $form->setUuid('test-uuid');

        $result = $form->getCloseTag(
            ['some' => 'data', 'other' => 123, 'nested' => ['array', 'of', 'data' => 'items']]
        )->jsonSerialize();

        self::assertStringContainsString('<input type="hidden" name="formPayload" value="encrypted data" />', $result);
    }

    public function testThrowsExceptionOnEmptyPayload()
    {
        $this->expectException(\Solspace\ExpressForms\exceptions\Form\InsufficientFormDataPostedException::class);
        $this->expectExceptionMessage('Insufficient form data posted');

        $form = new Form();

        $event = new FormSubmitEvent($form, []);
        Event::trigger(SubmitController::class, SubmitController::EVENT_BEFORE_FORM_SUBMIT, $event);
    }

    public function testDecryptsAndSetsEmptyPayload()
    {
        $this->hashMock
            ->expects($this->once())
            ->method('decrypt')
            ->with('encrypted payload', 'test-uuid')
            ->willReturn('{"attributes":{"method":"post"},"parameters":[]}')
        ;

        $form = new Form();
        $form->setUuid('test-uuid');

        $event = new FormSubmitEvent($form, ['formPayload' => 'encrypted payload']);
        Event::trigger(SubmitController::class, SubmitController::EVENT_BEFORE_FORM_SUBMIT, $event);

        self::assertSame(['method' => 'post'], $form->getHtmlAttributes()->toArray());
        self::assertSame([], $form->getParameters()->toArray());
    }

    public function testDecryptsAndSetsPayload()
    {
        $this->hashMock
            ->expects($this->once())
            ->method('decrypt')
            ->with('encrypted payload', 'test-uuid')
            ->willReturn(
                '{"attributes":{"method":"get","id":"test-id","data-test":"value","novalidate":true},"parameters":{"return":"/return/url","some":{"nested":"dictionary"}}}'
            )
        ;

        $form = new Form();
        $form->setUuid('test-uuid');

        $event = new FormSubmitEvent($form, ['formPayload' => 'encrypted payload']);
        Event::trigger(SubmitController::class, SubmitController::EVENT_BEFORE_FORM_SUBMIT, $event);

        self::assertSame(
            ['method' => 'get', 'id' => 'test-id', 'data-test' => 'value', 'novalidate' => true],
            $form->getHtmlAttributes()->toArray()
        );
        self::assertSame(
            ['return' => '/return/url', 'some' => ['nested' => 'dictionary']],
            $form->getParameters()->toArray()
        );
    }
}
