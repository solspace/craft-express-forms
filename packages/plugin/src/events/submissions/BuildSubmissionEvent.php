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
     */
    public function __construct(Submission $submission, array $postedData)
    {
        $this->submission = $submission;
        $this->postedData = $postedData;

        parent::__construct();
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    public function getPostedData(): array
    {
        return $this->postedData;
    }
}
