<?php

namespace Solspace\ExpressForms\serializers;

use Solspace\ExpressForms\models\Form;

class FormSerializer
{
    /** @var FieldSerializer */
    private $fieldSerializer;

    /**
     * FormSerializer constructor.
     *
     * @param FieldSerializer $fieldSerializer
     */
    public function __construct(FieldSerializer $fieldSerializer)
    {
        $this->fieldSerializer = $fieldSerializer;
    }

    /**
     * @param Form $form
     *
     * @return string
     */
    public function toJson(Form $form): string
    {
        return \GuzzleHttp\json_encode($this->toArray($form));
    }

    /**
     * @param Form $form
     *
     * @return array|null
     */
    public function toArray(Form $form)
    {
        if (null === $form) {
            return null;
        }

        $serialziedFields = [];
        foreach ($form->getFields() as $field) {
            $serialziedFields[] = $this->fieldSerializer->toArray($field);
        }

        return [
            'id'                    => $form->getId(),
            'uuid'                  => $form->getUuid(),
            'name'                  => $form->getName(),
            'handle'                => $form->getHandle(),
            'description'           => $form->getDescription(),
            'color'                 => $form->getColor(),
            'submissionTitle'       => $form->getSubmissionTitle(),
            'saveSubmissions'       => $form->isSaveSubmissions(),
            'adminNotification'     => $form->getAdminNotification(),
            'adminEmails'           => $form->getAdminEmails(),
            'submitterNotification' => $form->getSubmitterNotification(),
            'submitterEmailField'   => $form->getSubmitterEmailField(),
            'fields'                => $serialziedFields,
            'integrations'          => $form->getIntegrations()->asArray(),
            'spamCount'             => $form->getSpamCount(),
        ];
    }
}
