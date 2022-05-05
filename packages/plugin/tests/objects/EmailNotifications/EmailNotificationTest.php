<?php

namespace Solspace\Tests\ExpressForms\objects\EmailNotifications;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\models\EmailNotification;

/**
 * @internal
 * @coversNothing
 */
class EmailNotificationTest extends TestCase
{
    public function testParsesContentCorrectly()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/valid_template.twig');

        self::assertSame('Admin Contact', $notification->getName());
        self::assertSame('valid_template.twig', $notification->getFileName());
        self::assertSame('Description', $notification->getDescription());
        self::assertSame('Solspace', $notification->getFromName());
        self::assertSame('test@solspace.com', $notification->getFromEmail());
        self::assertSame('{email}', $notification->getReplyTo());
        self::assertSame('New submission from {firstName} {lastName}', $notification->getSubject());
        self::assertSame('Lorem ipsum dolor sit amet', $notification->getBody());
        self::assertTrue($notification->isIncludeAttachments());
    }

    public function testThrowsOnMissingMetaBlock()
    {
        $this->expectException(\Solspace\ExpressForms\exceptions\EmailNotifications\CouldNotParseNotificationException::class);
        $this->expectExceptionMessage('Email notification "only_body.twig" does not contain any needed meta information');

        EmailNotification::fromFile(__DIR__.'/resources/only_body.twig');
    }

    public function testMissingFromEmailMetadata()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/missing_fromEmail.twig');
        $notification->validate();

        self::assertSame(['fromEmail' => ['From Email cannot be blank.']], $notification->getErrors());
    }

    public function testMissingFromNameMetadata()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/missing_fromName.twig');
        $notification->validate();

        self::assertSame(['fromName' => ['From Name cannot be blank.']], $notification->getErrors());
    }

    public function testDefaultValues()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/minimum_viable_template.twig');

        self::assertNull($notification->getName());
        self::assertSame('minimum_viable_template.twig', $notification->getFileName());
        self::assertNull($notification->getDescription());
        self::assertSame('Solspace', $notification->getFromName());
        self::assertSame('test@solspace.com', $notification->getFromEmail());
        self::assertNull($notification->getReplyTo());
        self::assertNull($notification->getSubject());
        self::assertSame('', $notification->getBody());
        self::assertFalse($notification->isIncludeAttachments());
    }

    public function testExtraValues()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/valid_template.twig');

        self::assertSame('extra', $notification->some);
        self::assertSame(['one', 'two'], $notification->values);
        self::assertSame(['one' => 1, 'two' => 2, 'three' => 3], $notification->anObject);
    }

    public function testNonExistingExtraValuesReturnNull()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/valid_template.twig');

        self::assertNull($notification->nonExisting);
    }

    public function testWriteToFile()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/valid_template.twig');

        @unlink(__DIR__.'/resources/test_write.twig');
        $notification->writeToFile(__DIR__.'/resources/test_write.twig');

        $expected = "---\nname: 'Admin Contact'\ndescription: Description\nfromName: Solspace\nfromEmail: test@solspace.com\nreplyTo: '{email}'\ncc: null\nbcc: null\nsubject: 'New submission from {firstName} {lastName}'\nincludeAttachments: true\nsome: extra\nvalues:\n    - one\n    - two\nanObject:\n    one: 1\n    two: 2\n    three: 3\n---\nLorem ipsum dolor sit amet\n";

        self::assertSame($expected, file_get_contents(__DIR__.'/resources/test_write.twig'));
    }

    public function testWriteToExistingFileOverwrites()
    {
        $notification = EmailNotification::fromFile(__DIR__.'/resources/valid_template.twig');

        $path = __DIR__.'/resources/test_write.twig';
        @unlink($path);
        touch($path);
        file_put_contents($path, 'test string');
        self::assertSame('test string', file_get_contents($path));

        $notification->writeToFile($path);

        $expected = "---\nname: 'Admin Contact'\ndescription: Description\nfromName: Solspace\nfromEmail: test@solspace.com\nreplyTo: '{email}'\ncc: null\nbcc: null\nsubject: 'New submission from {firstName} {lastName}'\nincludeAttachments: true\nsome: extra\nvalues:\n    - one\n    - two\nanObject:\n    one: 1\n    two: 2\n    three: 3\n---\nLorem ipsum dolor sit amet\n";

        self::assertSame($expected, file_get_contents($path));
    }

    public function testSetFileNameWithTwigExtension()
    {
        $notification = new EmailNotification();

        self::assertNull($notification->getFileName());
        $notification->setFileName('test_filename.twig');
        self::assertSame('test_filename.twig', $notification->getFileName());
    }

    public function testSetFileNameWithHtmlExtension()
    {
        $notification = new EmailNotification();

        self::assertNull($notification->getFileName());
        $notification->setFileName('test_filename.html');
        self::assertSame('test_filename.html', $notification->getFileName());
    }

    public function testSetFileNameWithoutExtensionDefaultsToTwig()
    {
        $notification = new EmailNotification();

        self::assertNull($notification->getFileName());
        $notification->setFileName('test_filename');
        self::assertSame('test_filename.twig', $notification->getFileName());
    }

    public function testSetFileNameWithInvalidExtensionDefaultsToTwig()
    {
        $notification = new EmailNotification();

        self::assertNull($notification->getFileName());
        $notification->setFileName('test_filename.exe');
        self::assertSame('test_filename.twig', $notification->getFileName());
    }

    public function testFactoryMethodContent()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/template.twig');

        $expected = <<<'CONTENT'
            ---
            name: 'Email Notification Template'
            description: 'A description of what this template does.'
            fromName: '{{ craft.app.systemSettings.getSettings("email").fromName }}'
            fromEmail: '{{ craft.app.systemSettings.getSettings("email").fromEmail }}'
            replyTo: '{{ craft.app.systemSettings.getSettings("email").fromEmail }}'
            cc: null
            bcc: null
            subject: 'New submission from your {{ form.name }} form'
            includeAttachments: true
            ---
            <p>The following submission came in on {{ dateCreated|date('l, F j, Y \\a\\t g:ia') }}.</p>

            <ul>
                {% for field in form.fields %}
                    <li>{{ field.label }}: {{ field.valueAsString }}</li>
                {% endfor %}
            </ul>

            CONTENT;

        self::assertStringEqualsFile("{$path}/template.twig", $expected);
    }

    public function testFactoryMethodCreatesFileWithoutIndex()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());

        self::assertFileExists("{$path}/template.twig");
        self::assertFileDoesNotExist("{$path}/template_1.twig");
    }

    public function testFactoryMethodCreatesFilesWithIncrementingIndex()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertFileExists("{$path}/template.twig");
        self::assertFileExists("{$path}/template_1.twig");
        self::assertFileExists("{$path}/template_2.twig");
    }

    public function testFactoryMethodSkipsExistingFiles()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        touch("{$path}/template_1.twig");
        self::assertStringEqualsFile("{$path}/template_1.twig", '');

        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertFileExists("{$path}/template.twig");
        self::assertFileExists("{$path}/template_1.twig");
        self::assertFileExists("{$path}/template_2.twig");
        self::assertFileExists("{$path}/template_3.twig");

        self::assertStringEqualsFile("{$path}/template_1.twig", '');
    }

    public function testFactoryMethodOverwritesExistingFiles()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        touch("{$path}/template.twig");
        self::assertStringEqualsFile("{$path}/template.twig", '');

        $notification = EmailNotification::create($path, 'template', true);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertStringNotEqualsFile("{$path}/template.twig", '');

        $notification = EmailNotification::create($path, 'template', true);
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertStringNotEqualsFile("{$path}/template.twig", '');
    }

    public function testFactoryMethodWritesToCustomFileName()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        self::assertFileDoesNotExist("{$path}/custom-file_name.twig");
        $notification = EmailNotification::create($path, 'custom-file_name.twig');
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertFileExists("{$path}/custom-file_name.twig");
    }

    public function testFactoryMethodGeneratesIndexesForCustomFileNames()
    {
        $path = __DIR__.'/resources/factory_method';
        array_map('unlink', array_filter((array) glob("{$path}/*")));

        $notification = EmailNotification::create($path, 'custom-file_name.twig');
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path, 'custom-file_name.twig');
        $notification->writeToFile($path.'/'.$notification->getFileName());
        $notification = EmailNotification::create($path, 'custom-file_name.twig');
        $notification->writeToFile($path.'/'.$notification->getFileName());
        self::assertFileExists("{$path}/custom-file_name.twig");
        self::assertFileExists("{$path}/custom-file_name_1.twig");
        self::assertFileExists("{$path}/custom-file_name_2.twig");
    }
}
