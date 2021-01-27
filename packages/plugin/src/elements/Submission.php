<?php

namespace Solspace\ExpressForms\elements;

use craft\base\Element;
use craft\db\Query;
use craft\elements\actions\Restore;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
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
    const TABLE_STD = 'expressforms_submissions';
    const TABLE = '{{%expressforms_submissions}}';

    /** @var int */
    public $formId;

    /** @var string */
    public $formName;

    /** @var int */
    public $incrementalId;

    /**
     * @return null|string
     */
    public function __toString()
    {
        return (string) $this->title;
    }

    /**
     * @return ElementQueryInterface|SubmissionQuery
     */
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

    /**
     * {@inheritdoc}
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function displayName(): string
    {
        return ExpressForms::t('Submission');
    }

    public function getForm(): Form
    {
        $formService = ExpressForms::getInstance()->forms;

        return $formService->getFormById((int) $this->formId);
    }

    /**
     * @return null|FieldLayout
     */
    public function getFieldLayout()
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

    /**
     * @return null|string
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('express-forms/submissions/'.$this->id);
    }

    /**
     * {@inheritdoc}
     */
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
            list($_, $id) = $matches;
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
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterSave(bool $isNew)
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

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['id', 'title', 'formName'];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title' => ['label' => ExpressForms::t('Title')],
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
