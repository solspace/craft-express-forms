<?php

namespace Solspace\ExpressForms\serializers;

use Solspace\ExpressForms\models\Form;

class FormSerializer
{
    public function __construct(private FieldSerializer $fieldSerializer)
    {
    }

    public function toJson(Form $form): string
    {
        return json_encode($this->toArray($form));
    }

    public function toArray(Form $form): array
    {
        $serialziedFields = [];
        foreach ($form->getFields() as $field) {
            $serialziedFields[] = $this->fieldSerializer->toArray($field);
        }

        return [
            'id' => $form->getId(),
            'uuid' => $form->getUuid(),
            'name' => $form->getName(),
            'handle' => $form->getHandle(),
            'description' => $form->getDescription(),
            'color' => $form->getColor(),
            'submissionTitle' => $form->getSubmissionTitle(),
            'saveSubmissions' => $form->isSaveSubmissions(),
            'adminNotification' => $form->getAdminNotification(),
            'adminEmails' => $form->getAdminEmails(),
            'submitterNotification' => $form->getSubmitterNotification(),
            'submitterEmailField' => $form->getSubmitterEmailField(),
            'fields' => $serialziedFields,
            'integrations' => $form->getIntegrations()->asArray(),
            'spamCount' => $form->getSpamCount(),
        ];
    }
}
