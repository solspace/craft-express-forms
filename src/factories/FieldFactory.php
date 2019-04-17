<?php

namespace Solspace\ExpressForms\factories;

use Ramsey\Uuid\Uuid;
use Solspace\ExpressForms\events\fields\FieldBuildFromArrayEvent;
use Solspace\ExpressForms\exceptions\Field\FieldClassDoesNotExist;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\fields\BaseField;
use Solspace\ExpressForms\fields\Checkbox;
use Solspace\ExpressForms\fields\Email;
use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\fields\Hidden;
use Solspace\ExpressForms\fields\Options;
use Solspace\ExpressForms\fields\Textarea;
use Solspace\ExpressForms\fields\Text;
use Symfony\Component\PropertyAccess\PropertyAccess;
use yii\base\Event;
use yii\base\UnknownPropertyException;

class FieldFactory
{
    const EVENT_BEFORE_BUILD_FROM_ARRAY = 'beforeBuildFromArray';
    const EVENT_AFTER_BUILD_FROM_ARRAY  = 'afterBuildFromArray';

    const TYPE_MAP = [
        FieldInterface::TYPE_TEXT     => Text::class,
        FieldInterface::TYPE_TEXTAREA => Textarea::class,
        FieldInterface::TYPE_CHECKBOX => Checkbox::class,
        FieldInterface::TYPE_OPTIONS  => Options::class,
        FieldInterface::TYPE_EMAIL    => Email::class,
        FieldInterface::TYPE_HIDDEN   => Hidden::class,
        FieldInterface::TYPE_FILE     => File::class,
    ];

    /**
     * @param array $data
     *
     * @return FieldInterface
     */
    public function fromArray(array $data): FieldInterface
    {
        $type  = $data['type'] ?? null;
        $class = self::TYPE_MAP[$type] ?? null;
        if (!$class || !class_exists($class)) {
            throw new FieldClassDoesNotExist(
                ExpressForms::t('Cannot instantiate "{class}". Class not found.', ['class' => $class])
            );
        }

        if (isset($data['uid'])) {
            $field = \Craft::$app->getFields()->getFieldByUid($data['uid']);
        } else {
            $field       = null;
            $data['uid'] = Uuid::uuid4();
        }

        if (null === $field) {
            /** @var FieldInterface|BaseField $field */
            $field      = new $class;
            $field->uid = $data['uid'];
        } else if (!$field instanceof $class) {
            $oldField = $field;
            $field = new $class;
            $field->setAttributes($oldField->getAttributes());
            $field->id = $oldField->id;
            $field->uid = $oldField->uid;
        }

        unset($data['id'], $data['uid'], $data['type']);

        $event = new FieldBuildFromArrayEvent(['field' => $field, 'data' => $data]);
        Event::trigger($this, self::EVENT_BEFORE_BUILD_FROM_ARRAY, $event);

        if (!$event->isValid) {
            return null;
        }

        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($data as $key => $value) {
            if ($propertyAccess->isWritable($field, $key)) {
                try {
                    $propertyAccess->setValue($field, $key, $value);
                } catch (UnknownPropertyException $exception) {
                }
            }
        }

        $event = new FieldBuildFromArrayEvent(['field' => $field]);
        Event::trigger($this, self::EVENT_AFTER_BUILD_FROM_ARRAY, $event);

        if (!$event->isValid) {
            return null;
        }

        return $field;
    }
}
