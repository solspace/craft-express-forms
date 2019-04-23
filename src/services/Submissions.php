<?php

namespace Solspace\ExpressForms\services;

use Craft;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\submissions\BuildSubmissionEvent;
use Solspace\ExpressForms\events\submissions\BuildTitleEvent;
use Solspace\ExpressForms\events\submissions\SaveSubmissionEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\models\Form;

class Submissions extends BaseService
{
    const EVENT_BEFORE_BUILD_SUBMISSION_TITLE = 'beforeBuildSubmissionTitle';
    const EVENT_AFTER_BUILD_SUBMISSION        = 'afterBuildSubmission';
    const EVENT_BEFORE_SAVE_SUBMISSION        = 'beforeSaveSubmission';
    const EVENT_AFTER_SAVE_SUBMISSION         = 'afterSaveSubmission';

    /** @var Submission[] */
    private static $submissionCache = [];
    private static $submissionsByFormId = [];

    /**
     * @param int $id
     *
     * @return null|Submission
     */
    public function getSubmissionById(int $id = null)
    {
        if (!isset(self::$submissionCache[$id])) {
            self::$submissionCache[$id] = Submission::find()->id($id)->one();
        }

        return self::$submissionCache[$id];
    }

    /**
     * @param int $formId
     *
     * @return Submission[]
     */
    public function getSubmissions(int $formId): array
    {
        if (!isset(self::$submissionsByFormId[$formId])) {
            self::$submissionsByFormId[$formId] = Submission::find()->formId($formId)->all();
        }

        return self::$submissionsByFormId[$formId];
    }

    /**
     * @param Form  $form
     * @param array $postData
     *
     * @return Submission
     */
    public function buildSubmission(Form $form, array $postData): Submission
    {
        $dateCreated = new \DateTime();

        $twigVariables = array_merge(
            [
                'form'        => $form,
                'dateCreated' => $dateCreated,
            ],
            $postData
        );

        $titleEvent = new BuildTitleEvent($form, $form->getSubmissionTitle(), $twigVariables);
        $this->trigger(self::EVENT_BEFORE_BUILD_SUBMISSION_TITLE, $titleEvent);

        $submission              = new Submission();
        $submission->dateCreated = $dateCreated;
        $submission->siteId      = Craft::$app->sites->getCurrentSite()->id;
        $submission->formId      = $form->getId();

        foreach ($form->getFields() as $field) {
            $submission->setFieldValue($field->getHandle(), $field->getValue());
        }

        try {
            $title = Craft::$app->view->renderObjectTemplate(
                $titleEvent->getTitle(),
                $submission,
                $titleEvent->getTwigVariables()
            );
        } catch (\Exception $e) {
            ExpressFormsLogger::getInstance(ExpressFormsLogger::EXPRESS_FORMS)
                ->error($e->getMessage(), ['Creating a submission for ' . $form->getName()]);

            $title = Craft::$app->view->renderString('{{ "now"|date("Y-m-d H:i:s") }}');
        }

        $submission->title = $title;

        $this->trigger(self::EVENT_AFTER_BUILD_SUBMISSION, new BuildSubmissionEvent($submission, $postData));

        return $submission;
    }

    /**
     * @param Submission $submission
     *
     * @return bool
     */
    public function saveSubmission(Submission $submission): bool
    {
        $saveEvent = new SaveSubmissionEvent($submission);
        $this->trigger(self::EVENT_BEFORE_SAVE_SUBMISSION, $saveEvent);
        if (!$saveEvent->isValid) {
            return false;
        }

        if ($submission->getForm()->isMarkedAsSpam()) {
            $result = false;
            ExpressForms::getInstance()->forms->incrementSpamCount($submission->getForm());
        } else {
            $result = Craft::$app->elements->saveElement($submission);
        }

        $this->trigger(self::EVENT_AFTER_SAVE_SUBMISSION, new SaveSubmissionEvent($submission));

        return $result;
    }
}
