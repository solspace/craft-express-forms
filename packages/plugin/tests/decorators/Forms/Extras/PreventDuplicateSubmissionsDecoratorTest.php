<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Extras;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\Extras\PreventDuplicateSubmissionsDecorator;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\models\Settings;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\Security\HashingInterface;
use Solspace\ExpressForms\providers\Session\SessionProviderInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class PreventDuplicateSubmissionsDecoratorTest extends TestCase
{
    /** @var PreventDuplicateSubmissionsDecorator */
    private $decorator;

    /** @var MockObject|SessionProviderInterface */
    private $sessionMock;

    /** @var HashingInterface|MockObject */
    private $hashingMock;

    /** @var MockObject|SettingsProviderInterface */
    private $settingsMock;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(SessionProviderInterface::class);
        $this->hashingMock = $this->createMock(HashingInterface::class);
        $this->settingsMock = $this->createMock(SettingsProviderInterface::class);

        $this->decorator = new PreventDuplicateSubmissionsDecorator(
            $this->sessionMock,
            $this->hashingMock,
            $this->settingsMock
        );

        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testAttachesInputToForm()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->hashingMock
            ->expects(self::once())
            ->method('getUuid4')
            ->willReturn('uuid')
        ;

        $this->sessionMock
            ->expects(self::once())
            ->method('set')
            ->with('fdchk-uuid')
        ;

        $result = $form->getCloseTag()->jsonSerialize();
        self::assertStringContainsString('<input type="hidden" name="fdchk-uuid" value="fdchk-uuid" />', $result);
    }

    public function testDoesNotAttachIfDisabled()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => false]))
        ;

        $this->hashingMock
            ->expects(self::never())
            ->method('getUuid4')
            ->willReturn('uuid')
        ;

        $this->sessionMock
            ->expects(self::never())
            ->method('set')
            ->with('fdchk-uuid')
        ;

        $result = $form->getCloseTag()->jsonSerialize();
        self::assertStringNotContainsString('<input type="hidden" name="fdchk-uuid" value="fdchk-uuid" />', $result);
    }

    public function testFailsWhenNoHashPresent()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $form->getCloseTag();
        $form->submit([]);

        self::assertTrue($form->isSkipped());
    }

    public function testDoesNotFailWhenNoHashPresentButDisabled()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => false]))
        ;

        $form->getCloseTag();
        $form->submit([]);

        self::assertFalse($form->isSkipped());
    }

    public function testFailsWhenInvalidHashPresent()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::once())
            ->method('get')
            ->with('fdchk-value')
            ->willReturn(null)
        ;

        $form->getCloseTag();
        $form->submit(['fdchk-test' => 'fdchk-value']);

        self::assertTrue($form->isSkipped());
    }

    public function testPassesWhenValueIsInList()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::once())
            ->method('get')
            ->with('fdchk-value')
            ->willReturn('valid-value')
        ;

        $form->getCloseTag();
        $form->submit(['fdchk-test' => 'fdchk-value']);

        self::assertFalse($form->isSkipped());
    }

    public function testCleansUpSessionValue()
    {
        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::once())
            ->method('get')
            ->with('fdchk-value')
            ->willReturn('valid-value')
        ;

        $this->sessionMock
            ->expects(self::once())
            ->method('remove')
            ->with('fdchk-value')
        ;

        $form->getCloseTag();
        $form->submit(['fdchk-test' => 'fdchk-value']);
    }

    public function testCleansUpOldSessionValues()
    {
        for ($i = 0; $i < 20; ++$i) {
            $_SESSION['fdchk-'.$i] = time() - (60 * 60 * 4);
        }

        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::exactly(20))
            ->method('remove')
        ;

        $form->getCloseTag();
        $form->submit([]);
    }

    public function testDoesNotCleanUpNewValues()
    {
        for ($i = 0; $i < 20; ++$i) {
            $_SESSION['fdchk-'.$i] = time() - (60 * 60 * 2);
        }

        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::never())
            ->method('remove')
        ;

        $form->getCloseTag();
        $form->submit([]);
    }

    public function testCleansValuesThatNeedCleaning()
    {
        for ($i = 0; $i < 10; ++$i) {
            $_SESSION['fdchk-'.$i] = time() - (60 * 60 * 2);
        }

        for ($i = 10; $i < 20; ++$i) {
            $_SESSION['fdchk-'.$i] = time() - (61 * 60 * 3);
        }

        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::exactly(10))
            ->method('remove')
        ;

        $form->getCloseTag();
        $form->submit([]);
    }

    public function testRemovesStaleItems()
    {
        $range = range(0, 42);
        shuffle($range);
        foreach ($range as $i) {
            $_SESSION['fdchk-'.$i] = $i;
        }

        $form = new Form();

        $this->settingsMock
            ->method('get')
            ->willReturn(new Settings(['duplicatePreventionEnabled' => true]))
        ;

        $this->sessionMock
            ->expects(self::exactly(3))
            ->method('remove')
            ->withConsecutive(['fdchk-42'], ['fdchk-41'], ['fdchk-40'])
        ;

        $form->getCloseTag();
    }
}
