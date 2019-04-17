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
     *
     * @param Submission $submission
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;

        parent::__construct();
    }

    /**
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }
}
