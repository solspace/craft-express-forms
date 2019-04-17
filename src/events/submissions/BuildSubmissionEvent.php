<?php

namespace Solspace\ExpressForms\events\submissions;

use Solspace\ExpressForms\elements\Submission;
use yii\base\Event;

class BuildSubmissionEvent extends Event
{
    /** @var Submission */
    private $submission;

    /** @var array */
    private $postedData;

    /**
     * BuildSubmissionEvent constructor.
     *
     * @param Submission $submission
     */
    public function __construct(Submission $submission, array $postedData)
    {
        $this->submission = $submission;
        $this->postedData = $postedData;

        parent::__construct();
    }

    /**
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * @return array
     */
    public function getPostedData(): array
    {
        return $this->postedData;
    }

    /**
     * @param array $postedData
     *
     * @return BuildSubmissionEvent
     */
    public function setPostedData(array $postedData): BuildSubmissionEvent
    {
        $this->postedData = $postedData;

        return $this;
    }
}
