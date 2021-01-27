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
    const EVENT_AFTER_SUBMIT = 'afterSubmit';

    const EVENT_VALIDATE_FIELD = 'onFieldValidate';
    const EVENT_VALIDATE_FORM = 'onFormValidate';

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
        $this->uuid = Uuid::uuid4()->toString();
        $this->parameters = new ParameterBag();
        $this->extraParameters = new ParameterBag();
        $this->htmlAttributes = new ParameterBag(self::DEFAULT_HTML_ATTRIBUTES);
        $this->color = ColorHelper::randomColor();
    }

    public function __isset(string $name): bool
    {
        return $this->getExtraParameters()->has($name);
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getExtraParameters()->get($name);
    }

    /**
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id = null): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getFieldLayoutId()
    {
        return $this->fieldLayoutId;
    }

    /**
     * @param int $fieldLayoutId
     */
    public function setFieldLayoutId(int $fieldLayoutId = null): self
    {
        $this->fieldLayoutId = $fieldLayoutId;

        return $this;
    }

    /**
     * @return null|FieldLayout
     */
    public function getFieldLayout()
    {
        if (!$this->getFieldLayoutId()) {
            return null;
        }

        return \Craft::$app->fields->getLayoutById($this->getFieldLayoutId());
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     */
    public function setHandle(string $handle = null): self
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color = null): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubmissionTitle()
    {
        return $this->submissionTitle;
    }

    /**
     * @param string $submissionTitle
     */
    public function setSubmissionTitle(string $submissionTitle = null): self
    {
        $this->submissionTitle = $submissionTitle;

        return $this;
    }

    public function isSaveSubmissions(): bool
    {
        return $this->saveSubmissions ?? true;
    }

    public function setSaveSubmissions(bool $saveSubmissions = true): self
    {
        $this->saveSubmissions = $saveSubmissions;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAdminNotification()
    {
        return $this->adminNotification;
    }

    /**
     * @param string $adminNotification
     */
    public function setAdminNotification(string $adminNotification = null): self
    {
        $this->adminNotification = $adminNotification;

        return $this;
    }

    /**
     * @return null|string
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
    public function setAdminEmails(string $emails = null): self
    {
        $this->adminEmails = $emails;

        return $this;
    }

    /**
     * @param string $adminEmail
     */
    public function addAdminEmail($adminEmail = null): self
    {
        if (null !== $adminEmail) {
            $this->adminEmails = $this->adminEmails."\n".$adminEmail;
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubmitterNotification()
    {
        return $this->submitterNotification;
    }

    /**
     * @param string $submitterNotification
     */
    public function setSubmitterNotification(string $submitterNotification = null): self
    {
        $this->submitterNotification = $submitterNotification;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubmitterEmailField()
    {
        return $this->submitterEmailField;
    }

    /**
     * @param string $submitterEmailField
     */
    public function setSubmitterEmailField(string $submitterEmailField = null): self
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
     * @return $this
     */
    public function setIntegrations(IntegrationMappingCollection $collection): self
    {
        $this->integrations = $collection;

        return $this;
    }

    public function getSpamCount(): int
    {
        return $this->spamCount;
    }

    public function setSpamCount(int $spamCount = 0): self
    {
        $this->spamCount = $spamCount;

        return $this;
    }

    public function getSubmissionCount(): int
    {
        return $this->submissionCount;
    }

    public function setSubmissionCount(int $submissionCount = 0): self
    {
        $this->submissionCount = $submissionCount;

        return $this;
    }

    /**
     * @return Field|FieldCollection|FieldInterface[]
     */
    public function getFields(): FieldCollection
    {
        if (null === $this->fields) {
            $layout = $this->getFieldLayout();
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
     */
    public function addField(FieldInterface $field = null): self
    {
        if (null !== $field) {
            $this->getFields()->addField($field);
        }

        return $this;
    }

    public function setFields(FieldCollection $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    public function getHtmlAttributes(): ParameterBag
    {
        return $this->htmlAttributes;
    }

    public function getExtraParameters(): ParameterBag
    {
        return $this->extraParameters;
    }

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

    public function getCloseTag(array $config = []): Markup
    {
        $this->parseConfig($config);

        $content = '</form>';

        $event = new FormRenderTagEvent($this, $content);
        Event::trigger($this, self::EVENT_RENDER_CLOSING_TAG, $event);

        return new Markup($event->getOutput(), 'utf-8');
    }

    /**
     * @throws FormAlreadySubmittedException
     */
    public function submit(array $submittedData)
    {
        if ($this->submitted) {
            throw new FormAlreadySubmittedException('Form has already been submitted');
        }

        $this->submitted = true;

        $event = new FormSubmitEvent($this, $submittedData);
        Event::trigger($this, self::EVENT_BEFORE_SUBMIT, $event);

        $submittedData = $event->getSubmittedData();

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

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    public function addError(string $error): self
    {
        if (!\in_array($error, $this->errors, true)) {
            $this->errors[] = $error;
        }

        return $this;
    }

    public function getErrorsAsString(): string
    {
        return implode(', ', $this->getErrors());
    }

    public function isMarkedAsSpam(): bool
    {
        return $this->markedAsSpam;
    }

    public function markAsSpam(): self
    {
        $this->markedAsSpam = true;

        return $this;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): self
    {
        $this->skipped = $skipped;

        return $this;
    }

    private function parseConfig(array $config)
    {
        if (isset($config['attributes'])) {
            $this->getHtmlAttributes()->merge($config['attributes']);
            unset($config['attributes']);
        }

        $this->getParameters()->merge($config);
    }
}
