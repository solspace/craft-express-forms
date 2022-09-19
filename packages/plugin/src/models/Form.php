<?php

namespace Solspace\ExpressForms\models;

use craft\base\Field;
use craft\models\FieldLayout;
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
    public const VALIDATION_ERROR_KEY = 'expressformsValidationErrors';

    public const EVENT_BEFORE_SUBMIT = 'beforeSubmit';
    public const EVENT_AFTER_SUBMIT = 'afterSubmit';

    public const EVENT_VALIDATE_FIELD = 'onFieldValidate';
    public const EVENT_VALIDATE_FORM = 'onFormValidate';

    public const EVENT_RENDER_OPENING_TAG = 'onRenderOpeningTag';
    public const EVENT_RENDER_CLOSING_TAG = 'onRenderClosingTag';

    public const EVENT_COMPILE_HTML_ATTRIBUTES = 'onCompileHtmlAttributes';

    public const DEFAULT_HTML_ATTRIBUTES = [
        'method' => 'post',
    ];

    private ?int $id = null;
    private ?string $uuid;
    private ?int $fieldLayoutId = null;
    private ?string $name = null;
    private ?string $handle = null;
    private ?string $description = null;
    private ?string $color;
    private ?string $submissionTitle = '{{ dateCreated|date("Y-m-d H:i:s") }}';
    private ?bool $saveSubmissions = null;
    private ?string $adminNotification = null;
    private ?string $adminEmails = null;
    private ?string $submitterNotification = null;
    private ?string $submitterEmailField = null;
    private ?IntegrationMappingCollection $integrations = null;
    private ?int $spamCount = 0;
    private ?int $submissionCount = 0;
    private ?FieldCollection $fields = null;
    private ?bool $submitted = false;
    private ?bool $valid = true;
    private ?bool $success = false;
    private ParameterBag $parameters;
    private ParameterBag $htmlAttributes;
    private ?array $errors = [];
    private ?bool $markedAsSpam = false;
    private ?bool $skipped = false;
    private ParameterBag $extraParameters;

    public function __construct()
    {
        $this->uuid = \craft\helpers\StringHelper::UUID();
        $this->parameters = new ParameterBag();
        $this->extraParameters = new ParameterBag();
        $this->htmlAttributes = new ParameterBag(self::DEFAULT_HTML_ATTRIBUTES);
        $this->color = ColorHelper::randomColor();
    }

    public function __isset(string $name): bool
    {
        return $this->getExtraParameters()->has($name);
    }

    public function __get(string $name)
    {
        return $this->getExtraParameters()->get($name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id = null): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFieldLayoutId(): ?int
    {
        return $this->fieldLayoutId;
    }

    public function setFieldLayoutId(?int $fieldLayoutId): self
    {
        $this->fieldLayoutId = $fieldLayoutId;

        return $this;
    }

    public function getFieldLayout(): ?FieldLayout
    {
        if (!$this->getFieldLayoutId()) {
            return null;
        }

        return \Craft::$app->fields->getLayoutById($this->getFieldLayoutId());
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function setHandle(?string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getSubmissionTitle(): ?string
    {
        return $this->submissionTitle;
    }

    public function setSubmissionTitle(?string $submissionTitle): self
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

    public function getAdminNotification(): ?string
    {
        return $this->adminNotification;
    }

    public function setAdminNotification(?string $adminNotification): self
    {
        $this->adminNotification = $adminNotification;

        return $this;
    }

    public function getAdminEmails(): ?string
    {
        return $this->adminEmails;
    }

    public function setAdminEmails(?string $emails): self
    {
        $this->adminEmails = $emails;

        return $this;
    }

    public function addAdminEmail(?string $adminEmail): self
    {
        if (null !== $adminEmail) {
            $this->adminEmails = $this->adminEmails."\n".$adminEmail;
        }

        return $this;
    }

    public function getSubmitterNotification(): ?string
    {
        return $this->submitterNotification;
    }

    public function setSubmitterNotification(?string $submitterNotification): self
    {
        $this->submitterNotification = $submitterNotification;

        return $this;
    }

    public function getSubmitterEmailField(): array|string|null
    {
        return $this->submitterEmailField;
    }

    public function setSubmitterEmailField(?string $submitterEmailField): self
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

    public function setIntegrations(?IntegrationMappingCollection $collection): self
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
                foreach ($layout->getCustomFields() as $field) {
                    if ($field instanceof FieldInterface) {
                        $this->fields->addField($field);
                    }
                }
            }
        }

        return $this->fields;
    }

    public function addField(?FieldInterface $field): self
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

    public function submit(array $submittedData): void
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

    private function parseConfig(array $config): void
    {
        if (isset($config['attributes'])) {
            $this->getHtmlAttributes()->merge($config['attributes']);
            unset($config['attributes']);
        }

        $this->getParameters()->merge($config);
    }
}
