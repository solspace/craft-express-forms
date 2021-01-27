<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\BaseFunctionality;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\ReturnUrlExpressFormDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\SubmitFlashMessageDecorator;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\factories\IntegrationMappingFactory;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Session\FlashBagProviderInterface;

/**
 * @internal
 * @coversNothing
 */
class SubmitFlashMessageDecoratorTest extends TestCase
{
    /** @var ReturnUrlExpressFormDecorator */
    private $decorator;

    /** @var FlashBagProviderInterface|MockObject */
    private $flashBagMock;

    /** @var FormFactory */
    private $formFactory;

    protected function setUp(): void
    {
        $this->flashBagMock = $this->createMock(FlashBagProviderInterface::class);

        $this->decorator = new SubmitFlashMessageDecorator($this->flashBagMock);
        $this->decorator->initEventListeners();

        $this->formFactory = new FormFactory($this->createMock(IntegrationMappingFactory::class));
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testParameterAddedToForm()
    {
        $this->flashBagMock
            ->expects($this->once())
            ->method('get')
            ->with('form-submitted-successfully-test-uuid', false)
            ->willReturn(false)
        ;

        $form = new Form();
        $form->setUuid('test-uuid');
        $this->formFactory->populateFromArray($form, []);

        self::assertFalse($form->submittedSuccessfully);
    }

    public function testFormSubmitParameterSetToTrueIfFormSuccessfull()
    {
        $this->flashBagMock
            ->expects($this->once())
            ->method('get')
            ->with('form-submitted-successfully-test-uuid', false)
            ->willReturn(true)
        ;

        $form = new Form();
        $form->setUuid('test-uuid');
        $this->formFactory->populateFromArray($form, []);

        self::assertTrue($form->submittedSuccessfully);
    }
}
