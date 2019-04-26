<?php

namespace Solspace\ExpressForms\models;

use craft\base\Field;
use craft\models\FieldLayout;
use Ramsey\Uuid\Uuid;
use Solspace\Commons\Helpers\ColorHelper;
use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\events\fields\FieldValidateEvent;
use Solspace\ExpressForms\events\forms\FormCompileTagAttributesEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\events\forms\FormValidateEvent;
use Solspace\ExpressForms\exceptions\Form\FormAlreadySubmittedException;
use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\objects\Collections\FieldCollection;
use Solspace\ExpressForms\objects\Collections\IntegrationMappingCollection;
use Solspace\ExpressForms\objects\ParameterBag;
use Twig\Markup;
use yii\base\Event;

/**
 * @property mixed submittedSuccessfully
 */
class Form
{
    const VALIDATION_ERROR_KEY = 'expressformsValidationErrors';

    const EVENT_BEFORE_SUBMIT = 'beforeSubmit';
    const EVENT_AFTER_SUBMIT  = 'afterSubmit';

    const EVENT_VALIDATE_FIELD = 'onFieldValidate';
    const EVENT_VALIDATE_FORM  = 'onFormValidate';

    const EVENT_RENDER_OPENING_TAG = 'onRenderOpeningTag';
    const EVENT_RENDER_CLOSING_TAG = 'onRenderClosingTag';

    const EVENT_COMPILE_HTML_ATTRIBUTES = 'onCompileHtmlAttributes';

    const DEFAULT_HTML_ATTRIBUTES = [
        'method' => 'post',
    ];

    /** @var int */
    private $id;

    /** @var string */
    private $uuid;

    /** @var int */
    private $fieldLayoutId;

    /** @var string */
    private $name;

    /** @var string */
    private $handle;

    /** @var string */
    private $description;

    /** @var string */
    private $color;

    /** @var string */
    private $submissionTitle = '{{ dateCreated|date("Y-m-d H:i:s") }}';

    /** @var bool */
    private $saveSubmissions;

    /** @var string */
    private $adminNotification;

    /** @var string */
    private $adminEmails;

    /** @var string */
    private $submitterNotification;

    /** @var string[] */
    private $submitterEmailField;

    /** @var IntegrationMappingCollection */
    private $integrations;

    /** @var int */
    private $spamCount = 0;

    /** @var int */
    private $submissionCount = 0;

    /** @var FieldCollection */
    private $fields;

    /** @var bool */
    private $submitted = false;

    /** @var bool */
    private $valid = true;

    /** @var bool */
    private $success = false;

    /** @var ParameterBag */
    private $parameters;

    /** @var ParameterBag */
    private $htmlAttributes;

    /** @var array */
    private $errors = [];

    /** @var bool */
    private $markedAsSpam = false;

    /** @var bool */
    private $skipped = false;

    /** @var ParameterBag */
    private $extraParameters;

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->uuid            = Uuid::uuid4()->toString();
        $this->parameters      = new ParameterBag();
        $this->extraParameters = new ParameterBag();
        $this->htmlAttributes  = new ParameterBag(self::DEFAULT_HTML_ATTRIBUTES);
        $this->color           = ColorHelper::randomColor();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->getExtraParameters()->has($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getExtraParameters()->get($name);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Form
     */
    public function setId(int $id = null): Form
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return Form
     */
    public function setUuid(string $uuid): Form
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFieldLayoutId()
    {
        return $this->fieldLayoutId;
    }

    /**
     * @param int $fieldLayoutId
     *
     * @return Form
     */
    public function setFieldLayoutId(int $fieldLayoutId = null): Form
    {
        $this->fieldLayoutId = $fieldLayoutId;

        return $this;
    }

    /**
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {
        if (!$this->getFieldLayoutId()) {
            return null;
        }

        return \Craft::$app->fields->getLayoutById($this->getFieldLayoutId());
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Form
     */
    public function setName(string $name = null): Form
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     *
     * @return Form
     */
    public function setHandle(string $handle = null): Form
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Form
     */
    public function setDescription(string $description = null): Form
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return Form
     */
    public function setColor(string $color = null): Form
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubmissionTitle()
    {
        return $this->submissionTitle;
    }

    /**
     * @param string $submissionTitle
     *
     * @return Form
     */
    public function setSubmissionTitle(string $submissionTitle = null): Form
    {
        $this->submissionTitle = $submissionTitle;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSaveSubmissions(): bool
    {
        return $this->saveSubmissions ?? true;
    }

    /**
     * @param bool $saveSubmissions
     *
     * @return Form
     */
    public function setSaveSubmissions(bool $saveSubmissions = true): Form
    {
        $this->saveSubmissions = $saveSubmissions;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdminNotification()
    {
        return $this->adminNotification;
    }

    /**
     * @param string $adminNotification
     *
     * @return Form
     */
    public function setAdminNotification(string $adminNotification = null): Form
    {
        $this->adminNotification = $adminNotification;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdminEmails()
    {
        return $this->adminEmails;
    }

    /**
     * @param string $emails
     *
     * @return $this
     */
    public function setAdminEmails(string $emails = null): Form
    {
        $this->adminEmails = $emails;

        return $this;
    }

    /**
     * @param string $adminEmail
     *
     * @return Form
     */
    public function addAdminEmail($adminEmail = null): Form
    {
        if (null !== $adminEmail) {
            $this->adminEmails = $this->adminEmails . "\n" . $adminEmail;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubmitterNotification()
    {
        return $this->submitterNotification;
    }

    /**
     * @param string $submitterNotification
     *
     * @return Form
     */
    public function setSubmitterNotification(string $submitterNotification = null): Form
    {
        $this->submitterNotification = $submitterNotification;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubmitterEmailField()
    {
        return $this->submitterEmailField;
    }

    /**
     * @param string $submitterEmailField
     *
     * @return Form
     */
    public function setSubmitterEmailField(string $submitterEmailField = null): Form
    {
        $this->submitterEmailField = $submitterEmailField;

        return $this;
    }

    /**
     * @return IntegrationMappingCollection|IntegrationMappingInterface[]
     */
    public function getIntegrations(): IntegrationMappingCollection
    {
        return $this->integrations ?? new IntegrationMappingCollection();
    }

    /**
     * @param IntegrationMappingCollection $collection
     *
     * @return $this
     */
    public function setIntegrations(IntegrationMappingCollection $collection): Form
    {
        $this->integrations = $collection;

        return $this;
    }

    /**
     * @return int
     */
    public function getSpamCount(): int
    {
        return $this->spamCount;
    }

    /**
     * @param int $spamCount
     *
     * @return Form
     */
    public function setSpamCount(int $spamCount = 0): Form
    {
        $this->spamCount = $spamCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubmissionCount(): int
    {
        return $this->submissionCount;
    }

    /**
     * @param int $submissionCount
     *
     * @return Form
     */
    public function setSubmissionCount(int $submissionCount = 0): Form
    {
        $this->submissionCount = $submissionCount;

        return $this;
    }

    /**
     * @return FieldCollection|FieldInterface[]|Field
     */
    public function getFields(): FieldCollection
    {
        if (null === $this->fields) {
            $layout       = $this->getFieldLayout();
            $this->fields = new FieldCollection();

            if ($layout) {
                foreach ($layout->getFields() as $field) {
                    if ($field instanceof FieldInterface) {
                        $this->fields->addField($field);
                    }
                }
            }
        }

        return $this->fields;
    }

    /**
     * @param FieldInterface $field
     *
     * @return Form
     */
    public function addField(FieldInterface $field = null): Form
    {
        if (null !== $field) {
            $this->getFields()->addField($field);
        }

        return $this;
    }

    /**
     * @param FieldCollection $fields
     *
     * @return Form
     */
    public function setFields(FieldCollection $fields): Form
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     *
     * @return Form
     */
    public function setValid(bool $valid): Form
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return ParameterBag
     */
    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    /**
     * @return ParameterBag
     */
    public function getHtmlAttributes(): ParameterBag
    {
        return $this->htmlAttributes;
    }

    /**
     * @return ParameterBag
     */
    public function getExtraParameters(): ParameterBag
    {
        return $this->extraParameters;
    }

    /**
     * @param array $config
     *
     * @return Markup
     */
    public function getOpenTag(array $config = []): Markup
    {
        $this->parseConfig($config);

        $attributeEvent = new FormCompileTagAttributesEvent($this);
        Event::trigger($this, self::EVENT_COMPILE_HTML_ATTRIBUTES, $attributeEvent);

        $content = sprintf(
            '<form%s>',
            rtrim(StringHelper::compileAttributeStringFromArray($this->getHtmlAttributes()->toArray()))
        );

        $event = new FormRenderTagEvent($this, $content);
        Event::trigger($this, self::EVENT_RENDER_OPENING_TAG, $event);

        return new Markup($event->getOutput(), 'utf-8');
    }

    /**
     * @param array $config
     *
     * @return Markup
     */
    public function getCloseTag(array $config = []): Markup
    {
        $this->parseConfig($config);

        $content = '</form>';

        $event = new FormRenderTagEvent($this, $content);
        Event::trigger($this, self::EVENT_RENDER_CLOSING_TAG, $event);

        return new Markup($event->getOutput(), 'utf-8');
    }

    /**
     * @param array $submittedData
     *
     * @throws FormAlreadySubmittedException
     */
    public function submit(array $submittedData)
    {
        if ($this->submitted) {
            throw new FormAlreadySubmittedException('Form has already been submitted');
        }

        $this->submitted = true;

        Event::trigger($this, self::EVENT_BEFORE_SUBMIT, new FormSubmitEvent($this, $submittedData));

        /** @var FieldInterface $field */
        foreach ($this->getFields() as $field) {
            if (isset($submittedData[$field->getHandle()])) {
                $field->setValue($submittedData[$field->getHandle()]);
            }

            Event::trigger($this, self::EVENT_VALIDATE_FIELD, new FieldValidateEvent($field));

            if (!$field->isValid()) {
                $this->valid = false;
            }
        }

        Event::trigger($this, self::EVENT_VALIDATE_FORM, new FormValidateEvent($this, $submittedData));

        if ($this->hasErrors()) {
            $this->valid = false;
        }

        $this->success = $this->isSubmitted() && $this->isValid();

        Event::trigger($this, self::EVENT_AFTER_SUBMIT, new FormSubmitEvent($this, $submittedData));
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * @param string $error
     *
     * @return Form
     */
    public function addError(string $error): Form
    {
        if (!in_array($error, $this->errors, true)) {
            $this->errors[] = $error;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorsAsString(): string
    {
        return implode(', ', $this->getErrors());
    }

    /**
     * @return bool
     */
    public function isMarkedAsSpam(): bool
    {
        return $this->markedAsSpam;
    }

    /**
     * @return Form
     */
    public function markAsSpam(): Form
    {
        $this->markedAsSpam = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    /**
     * @param bool $skipped
     *
     * @return Form
     */
    public function setSkipped(bool $skipped): Form
    {
        $this->skipped = $skipped;

        return $this;
    }

    /**
     * @param array $config
     */
    private function parseConfig(array $config)
    {
        if (isset($config['attributes'])) {
            $this->getHtmlAttributes()->merge($config['attributes']);
            unset($config['attributes']);
        }

        $this->getParameters()->merge($config);
    }
}
