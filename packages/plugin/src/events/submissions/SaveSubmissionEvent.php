<?php

namespace Solspace\ExpressForms\events\submissions;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\elements\Submission;

class SaveSubmissionEvent extends CancelableEvent
{
    /** @var Submission */
    private $submission;

    /**
     * SaveSubmissionEvent constructor.
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;

        parent::__construct();
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
    }
}
