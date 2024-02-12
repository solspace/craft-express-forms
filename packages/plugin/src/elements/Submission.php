<?php

namespace Solspace\ExpressForms\elements;

use craft\base\Element;
use craft\db\Query;
use craft\elements\actions\Restore;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\elements\actions\DeleteSubmission;
use Solspace\ExpressForms\elements\db\SubmissionQuery;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\fields\BaseField;
use Solspace\ExpressForms\fields\Checkbox;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\fields\MultipleValueInterface;
use Solspace\ExpressForms\models\Form;
use yii\base\Exception;

class Submission extends Element
{
    public const TABLE_STD = 'expressforms_submissions';
    public const TABLE = '{{%expressforms_submissions}}';

    public ?int $formId = null;
    public ?string $formName = null;
    public ?int $incrementalId = null;

    private static $permissionCache = [];

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public static function find(): ElementQueryInterface
    {
        return new SubmissionQuery(self::class);
    }

    public static function getContentTableName(Form $form): string
    {
        return self::getContentTableNameFromHandle($form->getHandle());
    }

    public static function getContentTableNameFromHandle(string $handle): string
    {
        return "{{%expressforms_submissions_{$handle}}}";
    }

    public static function getFieldContextName(Form $form): string
    {
        return self::getFieldContextNameFromId($form->getId());
    }

    public static function getFieldContextNameFromId(int $id): string
    {
        return 'expressforms:'.$id;
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function displayName(): string
    {
        return ExpressForms::t('Submission');
    }

    public function getForm(): Form
    {
        $formService = ExpressForms::getInstance()->forms;

        return $formService->getFormById((int) $this->formId);
    }

    public function getFieldLayout(): ?FieldLayout
    {
        return $this->getForm()->getFieldLayout();
    }

    public function getFieldContext(): string
    {
        return self::getFieldContextName($this->getForm());
    }

    public function getContentTable(): string
    {
        return self::getContentTableName($this->getForm());
    }

    public function canManageSubmissions(): bool
    {
        if (!isset(self::$permissionCache[$this->formId])) {
            if (PermissionHelper::checkPermission(ExpressForms::PERMISSION_SUBMISSIONS)) {
                self::$permissionCache[$this->formId] = true;
            } else {
                self::$permissionCache[$this->formId] = PermissionHelper::checkPermission(
                    PermissionHelper::prepareNestedPermission(
                        ExpressForms::PERMISSION_SUBMISSIONS,
                        $this->formId
                    )
                );
            }
        }

        return self::$permissionCache[$this->formId];
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }

        return $this->canManageSubmissions();
    }

    public function getCpEditUrl(): ?string
    {
        return UrlHelper::cpUrl('express-forms/submissions/'.$this->id);
    }

    public static function defaultTableAttributes(string $source): array
    {
        if (!preg_match('/form:(\d+)$/', $source, $matches)) {
            return parent::defaultTableAttributes($source);
        }

        $attributes = [];

        $form = ExpressForms::getInstance()->forms->getFormById($matches[1]);
        if (!$form) {
            return $attributes;
        }

        foreach ($form->getFields() as $field) {
            $attributes[] = 'field:'.$field->getId();
        }

        return $attributes;
    }

    public function getTableAttributeHtml(string $attribute): string
    {
        if (preg_match('/^field:(\d+)$/', $attribute, $matches)) {
            [$_, $id] = $matches;

            /** @var BaseField $field */
            $field = \Craft::$app->getFields()->getFieldById($id);

            try {
                $value = $this->getFieldValue($field->getHandle());
            } catch (Exception $exception) {
                $value = null;
            }

            if ($field instanceof Checkbox) {
                return ExpressForms::t($value ? 'yes' : 'no');
            }

            if ($field instanceof MultipleValueInterface) {
                if ($value && $field instanceof File) {
                    $output = '';
                    foreach (Asset::find()->id($value)->all() as $asset) {
                        $output .= \Craft::$app->view->renderTemplate(
                            'express-forms/submissions/_indexComponents/asset.twig',
                            ['asset' => $asset]
                        );
                    }

                    return $output;
                }

                if (\is_array($value)) {
                    return implode(', ', $value);
                }
            }

            if (\is_string($value)) {
                $value = htmlentities($value, \ENT_QUOTES);
            }

            return $value ?? '';
        }

        return parent::getTableAttributeHtml($attribute);
    }

    public function afterSave(bool $isNew): void
    {
        if ($isNew) {
            $insertData = [
                'id' => $this->id,
                'formId' => $this->formId,
                'incrementalId' => $this->incrementalId ?? $this->getNewIncrementalId(),
            ];

            \Craft::$app->db->createCommand()
                ->insert(self::TABLE, $insertData)
                ->execute()
            ;
        }

        parent::afterSave($isNew);
    }

    protected static function defineSources(string $context = null): array
    {
        static $sources;

        if (null === $sources) {
            $items = [
                // [
                //     'key' => '*',
                //     'label' => ExpressForms::t('All Submissions'),
                // ],
                ['heading' => ExpressForms::t('Forms')],
            ];

            $formsService = ExpressForms::getInstance()->forms;
            foreach ($formsService->getAllForms() as $form) {
                $items[] = [
                    'key' => 'form:'.$form->getId(),
                    'label' => $form->getName(),
                    'data' => [
                        'handle' => $form->getHandle(),
                    ],
                    'criteria' => [
                        'formId' => $form->getId(),
                    ],
                ];
            }

            $sources = $items;
        }

        return $sources;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['id', 'title', 'formName'];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'id' => ExpressForms::t('Element ID'),
            'incrementalId' => ExpressForms::t('ID'),
            'title' => ExpressForms::t('Title'),
            'dateCreated' => ExpressForms::t('Date Created'),
            'formName' => ExpressForms::t('Form'),
            'dateUpdated' => ExpressForms::t('Date Updated'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'id' => ['label' => ExpressForms::t('Element ID')],
            'incrementalId' => ['label' => ExpressForms::t('ID')],
            'formName' => ['label' => ExpressForms::t('Form')],
            'dateCreated' => ['label' => ExpressForms::t('Date Created')],
            'dateUpdated' => ['label' => ExpressForms::t('Date Updated')],
        ];

        $forms = ExpressForms::getInstance()->forms->getAllForms();
        foreach ($forms as $form) {
            foreach ($form->getFields() as $field) {
                $attributes['field:'.$field->getId()] = ['label' => $field->getName()];
            }
        }

        return $attributes;
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [
            \Craft::$app->elements->createAction(
                [
                    'type' => DeleteSubmission::class,
                    'confirmationMessage' => ExpressForms::t(
                        'Are you sure you want to delete the selected submissions?'
                    ),
                    'successMessage' => ExpressForms::t('Submissions deleted.'),
                ]
            ),
        ];

        if (version_compare(\Craft::$app->getVersion(), '3.1', '>=')) {
            $actions[] = \Craft::$app->elements->createAction(
                [
                    'type' => Restore::class,
                    'successMessage' => \Craft::t('app', 'Submissions restored.'),
                    'partialSuccessMessage' => \Craft::t('app', 'Some submissions restored.'),
                    'failMessage' => \Craft::t('app', 'Submissions not restored.'),
                ]
            );
        }

        return $actions;
    }

    private function getNewIncrementalId(): int
    {
        $maxIncrementalId = (int) (new Query())
            ->select(['MAX([[incrementalId]])'])
            ->from(self::TABLE)
            ->scalar()
        ;

        return ++$maxIncrementalId;
    }
}
