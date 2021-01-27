<?php

namespace Solspace\Tests\ExpressForms\factories;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\factories\FieldFactory;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\factories\IntegrationMappingFactory;
use Solspace\ExpressForms\models\Form;

/**
 * @internal
 * @coversNothing
 */
class FormFactoryTest extends TestCase
{
    /** @var FormFactory */
    private $instance;

    /** @var FieldFactory|MockObject */
    private $fieldFactoryMock;

    /** @var IntegrationMappingFactory|MockObject */
    private $integrationFactoryMock;

    protected function setUp(): void
    {
        $this->fieldFactoryMock = $this->getMockBuilder(FieldFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['fromArray'])
            ->getMock()
        ;

        $this->integrationFactoryMock = $this->getMockBuilder(IntegrationMappingFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['fromArray'])
            ->getMock()
        ;

        $this->instance = new FormFactory($this->integrationFactoryMock);
    }

    public function testFromArrayWithAllFormData()
    {
        $data = [
            'id' => 32,
            'uuid' => '43219-93941-1991-33344',
            'name' => 'test form',
            'description' => 'test description',
            'submissionTitle' => 'title',
            'saveSubmissions' => true,
            'adminNotification' => 'filepath.twig',
            'adminEmails' => 'some@email.com, someOther@email.com',
            'submitterNotification' => 'filepath.twig',
            'submitterEmailField' => 'firstName',
            'spamCount' => 10,
        ];

        $this->fieldFactoryMock->expects($this->never())->method('fromArray');

        $form = new Form();
        $form = $this->instance->populateFromArray($form, $data);

        self::assertEquals(32, $form->getId());
        self::assertEquals('43219-93941-1991-33344', $form->getUuid());
        self::assertEquals('test form', $form->getName());
        self::assertEquals('test description', $form->getDescription());
        self::assertEquals('title', $form->getSubmissionTitle());
        self::assertTrue($form->isSaveSubmissions());
        self::assertEquals('filepath.twig', $form->getAdminNotification());
        self::assertEquals('some@email.com, someOther@email.com', $form->getAdminEmails());
        self::assertEquals('filepath.twig', $form->getSubmitterNotification());
        self::assertEquals('firstName', $form->getSubmitterEmailField());
        self::assertEquals(10, $form->getSpamCount());
    }
}
