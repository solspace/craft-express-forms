<?php

namespace Solspace\Tests\ExpressForms\decorators\Fields;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\Fields\FileUploadDecorator;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Files\FileTypeProviderInterface;
use Solspace\ExpressForms\providers\Files\FileUploadInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class FileUploadDecoratorTest extends TestCase
{
    /** @var FileUploadDecorator */
    private $decorator;

    /** @var File */
    private $field;

    /** @var FileTypeProviderInterface|MockObject */
    private $fileTypeProvider;

    /** @var FileUploadInterface|MockObject */
    private $fileUploadProvider;

    protected function setUp(): void
    {
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock
            ->method('translate')
            ->willReturnArgument(1)
        ;

        $this->fileTypeProvider = $this->createMock(FileTypeProviderInterface::class);
        $this->fileUploadProvider = $this->createMock(FileUploadInterface::class);

        $this->decorator = new FileUploadDecorator(
            $translatorMock,
            $this->fileTypeProvider,
            $this->fileUploadProvider
        );

        $this->decorator->initEventListeners();

        $this->field = new File();
        $this->field->handle = 'test';
        $this->field->uid = 'test-uid';
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
        $this->field = null;
    }

    public function testNoEnctypeForFormWhenNoFieldsAdded()
    {
        $form = new Form();

        $result = $form->getOpenTag()->jsonSerialize();

        self::assertStringNotContainsString('enctype="multipart/form-data"', $result);
    }

    public function testEnctypeForFormTagAdded()
    {
        $form = new Form();
        $form->addField($this->field);

        $result = $form->getOpenTag()->jsonSerialize();

        self::assertStringContainsString('enctype="multipart/form-data"', $result);
    }

    public function testErrorOnExceedingFilesize()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setMaxFileSizeKB(20);

        $testFilePath = __DIR__.'/resources/test-image.png';

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => $testFilePath,
            'error' => '',
            'size' => (20 * 1024) + 1, // Exceeds the limit by one byte
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'You tried uploading {fileSize}KB, but the maximum file upload size is {maxFileSize}KB',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPassesOnExactFileSize()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setMaxFileSizeKB(20);
        $this->field->setRestrictFileKinds(false);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 20 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            'You tried uploading {fileSize}KB, but the maximum file upload size is {maxFileSize}KB',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPassesOnSmallerFileSize()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setMaxFileSizeKB(20);
        $this->field->setRestrictFileKinds(false);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 10 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            'You tried uploading {fileSize}KB, but the maximum file upload size is {maxFileSize}KB',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPassesOnValidExtension()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileKinds(['image']);

        $this->fileTypeProvider
            ->expects($this->once())
            ->method('getValidExtensionsForFileKinds')
            ->with(['image'])
            ->willReturn(['png'])
        ;

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 20 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            "'{extension}' is not an allowed file extension",
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testAnyExtensionIsOkIfNoFileKindsProvided()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileKinds([]);
        $this->field->setRestrictFileKinds(false);

        $this->fileTypeProvider
            ->expects($this->once())
            ->method('getValidExtensionsForFileKinds')
            ->with([])
            ->willReturn([])
        ;

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.qxc',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 20 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            "'{extension}' is not an allowed file extension",
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testErrorsOnInvalidExtension()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileKinds(['text']);

        $this->fileTypeProvider
            ->expects($this->once())
            ->method('getValidExtensionsForFileKinds')
            ->with(['text'])
            ->willReturn(['txt'])
        ;

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 20 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            "'{extension}' is not an allowed file extension",
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testAllowsUploadingAnyExtensionWhenNotRestricted()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileKinds(['text']);
        $this->field->setRestrictFileKinds(false);

        $this->fileTypeProvider
            ->expects($this->once())
            ->method('getValidExtensionsForFileKinds')
            ->with(['text'])
            ->willReturn(['txt'])
        ;

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '/tmp/name',
            'error' => '',
            'size' => 20 * 1024, // Exact amount
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertTrue($form->isValid());
        self::assertNotContains(
            "'{extension}' is not an allowed file extension",
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testErrorsOnUploadingMoreFilesThanAllowed()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileCount(2);

        $_FILES[$this->field->getHandle()] = [
            'name' => ['test-image1.png', 'test-image2.png', 'test-image3.png'],
            'tmp_name' => ['/tmp/name1', '/tmp/name2', '/tmp/name3'],
            'error' => ['', '', ''],
            'size' => [20, 20, 20],
            'type' => ['file', 'file', 'file'],
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'Tried uploading {count} files. Maximum {max} files allowed.',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testUploadingExactFileCount()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileCount(3);
        $this->field->setRestrictFileKinds(false);

        $_FILES[$this->field->getHandle()] = [
            'name' => ['test-image1.png', 'test-image2.png', 'test-image3.png'],
            'tmp_name' => ['/tmp/name1', '/tmp/name2', '/tmp/name3'],
            'error' => ['', '', ''],
            'size' => [20, 20, 20],
            'type' => ['file', 'file', 'file'],
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            'Tried uploading {count} files. Maximum {max} files allowed.',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testUploadingLessThanSpecifiedFileCount()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->setFileCount(99);
        $this->field->setRestrictFileKinds(false);

        $_FILES[$this->field->getHandle()] = [
            'name' => ['test-image1.png', 'test-image2.png', 'test-image3.png'],
            'tmp_name' => ['/tmp/name1', '/tmp/name2', '/tmp/name3'],
            'error' => ['', '', ''],
            'size' => [20, 20, 20],
            'type' => ['file', 'file', 'file'],
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
            ->with($this->field)
        ;

        $form->submit([]);

        self::assertNotContains(
            'Tried uploading {count} files. Maximum {max} files allowed.',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testRandomErrorCodeShowsGenericError()
    {
        $form = new Form();
        $form->addField($this->field);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '',
            'error' => 999,
            'size' => 20,
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'Could not upload file',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPhpIniFileSizeTooLargeError()
    {
        $form = new Form();
        $form->addField($this->field);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '',
            'error' => \UPLOAD_ERR_INI_SIZE,
            'size' => 20,
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'File size too large',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPhpIniFormSizeTooLargeError()
    {
        $form = new Form();
        $form->addField($this->field);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '',
            'error' => \UPLOAD_ERR_FORM_SIZE,
            'size' => 20,
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'File size too large',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testPhpIniPartialUploadError()
    {
        $form = new Form();
        $form->addField($this->field);

        $_FILES[$this->field->getHandle()] = [
            'name' => 'test-image.png',
            'tmp_name' => '',
            'error' => \UPLOAD_ERR_PARTIAL,
            'size' => 20,
            'type' => 'file',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertContains(
            'The file was only partially uploaded',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }

    public function testUploadingNoFileIsValid()
    {
        $form = new Form();
        $form->addField($this->field);

        $_FILES[$this->field->getHandle()] = [
            'name' => '',
            'tmp_name' => '',
            'error' => '',
            'size' => '',
            'type' => '',
        ];

        $this->fileUploadProvider
            ->expects($this->once())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertEmpty($this->field->getErrors());
    }

    public function testErrorsOnUploadingEmptyWhenRequired()
    {
        $form = new Form();
        $form->addField($this->field);

        $this->field->required = true;

        $_FILES[$this->field->getHandle()] = [
            'name' => '',
            'tmp_name' => '',
            'error' => '',
            'size' => '',
            'type' => '',
        ];

        $this->fileUploadProvider
            ->expects($this->never())
            ->method('upload')
        ;

        $form->submit([]);

        self::assertNotEmpty($this->field->getErrors());
        self::assertContains(
            'This field is required',
            $this->field->getErrors(),
            $this->field->getErrorsAsString()
        );
    }
}
