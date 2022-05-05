<?php

namespace Solspace\ExpressForms\decorators\Fields;

use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\AbstractTranslatableDecorator;
use Solspace\ExpressForms\events\fields\FieldValidateEvent;
use Solspace\ExpressForms\events\forms\FormCompileTagAttributesEvent;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Files\FileTypeProviderInterface;
use Solspace\ExpressForms\providers\Files\FileUploadInterface;

class FileUploadDecorator extends AbstractTranslatableDecorator
{
    public function __construct(
        TranslatorInterface $translator,
        private FileTypeProviderInterface $fileTypeProvider,
        private FileUploadInterface $fileUploadProvider
    ) {
        parent::__construct($translator);
    }

    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_VALIDATE_FIELD, [$this, 'validateUploadedFields']],
            [Form::class, Form::EVENT_AFTER_SUBMIT, [$this, 'uploadFileToSource']],
            [Form::class, Form::EVENT_COMPILE_HTML_ATTRIBUTES, [$this, 'attachFormEncType']],
        ];
    }

    public function attachFormEncType(FormCompileTagAttributesEvent $event): void
    {
        $form = $event->getForm();
        foreach ($form->getFields() as $field) {
            if ($field instanceof File) {
                $form->getHtmlAttributes()->add('enctype', 'multipart/form-data');

                return;
            }
        }
    }

    public function validateUploadedFields(FieldValidateEvent $event)
    {
        $field = $event->getField();

        if (!$field instanceof File) {
            return;
        }

        $handle = $field->getHandle();

        $exists = isset($_FILES[$handle]) && !empty($_FILES[$handle]['name']);
        if (
            !$exists
            || (
                \is_array($_FILES[$handle]['name'])
                && 1 === \count($_FILES[$handle]['name'])
                && empty($_FILES[$handle]['name'][0])
            )
        ) {
            if ($field->isRequired()) {
                $field->addValidationError('This field is required');
            }

            return null;
        }

        if (!\is_array($_FILES[$handle]['name'])) {
            $_FILES[$handle]['name'] = [$_FILES[$handle]['name']];
            $_FILES[$handle]['tmp_name'] = [$_FILES[$handle]['tmp_name']];
            $_FILES[$handle]['error'] = [$_FILES[$handle]['error']];
            $_FILES[$handle]['size'] = [$_FILES[$handle]['size']];
            $_FILES[$handle]['type'] = [$_FILES[$handle]['type']];
        }

        if (empty($_FILES[$handle]['name'])) {
            if ($field->isRequired()) {
                $field->addValidationError('This field is required');
            }

            return null;
        }

        $fileCount = \count($_FILES[$handle]['name']);

        if ($fileCount > $field->getFileCount()) {
            $field->addValidationError(
                $this->translate(
                    'Tried uploading {count} files. Maximum {max} files allowed.',
                    ['max' => $field->getFileCount(), 'count' => $fileCount]
                )
            );
        }

        foreach ($_FILES[$handle]['name'] as $index => $name) {
            $extension = pathinfo($name, \PATHINFO_EXTENSION);
            $validExtensions = $this->fileTypeProvider->getValidExtensionsForFileKinds($field->getFileKinds());

            if (empty($_FILES[$handle]['tmp_name'][$index])) {
                $errorCode = $_FILES[$handle]['error'][$index];

                switch ($errorCode) {
                    case \UPLOAD_ERR_INI_SIZE:
                    case \UPLOAD_ERR_FORM_SIZE:
                        $field->addValidationError($this->translate('File size too large'));

                        break;

                    case \UPLOAD_ERR_PARTIAL:
                        $field->addValidationError($this->translate('The file was only partially uploaded'));

                        break;
                }

                $field->addValidationError($this->translate('Could not upload file'));
            }

            // Check for the correct file extension
            if ($field->isRestrictFileKinds() && !\in_array(strtolower($extension), $validExtensions, true)) {
                $field->addValidationError(
                    $this->translate(
                        "'{extension}' is not an allowed file extension",
                        ['extension' => $extension]
                    )
                );
            }

            $fileSizeKB = ceil($_FILES[$handle]['size'][$index] / 1024);
            if ($fileSizeKB > $field->getMaxFileSizeKB()) {
                $field->addValidationError(
                    $this->translate(
                        'You tried uploading {fileSize}KB, but the maximum file upload size is {maxFileSize}KB',
                        ['fileSize' => $fileSizeKB, 'maxFileSize' => $field->getMaxFileSizeKB()]
                    )
                );
            }
        }
    }

    public function uploadFileToSource(FormSubmitEvent $event)
    {
        $form = $event->getForm();

        if ($form->isSuccess()) {
            foreach ($form->getFields() as $field) {
                if (!$field instanceof File) {
                    continue;
                }

                if ($field->isValid()) {
                    $field->setValue($this->fileUploadProvider->upload($field));
                }
            }
        }
    }
}
