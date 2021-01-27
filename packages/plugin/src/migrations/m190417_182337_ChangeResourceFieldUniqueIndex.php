<?php

namespace Solspace\ExpressForms\migrations;

use craft\db\Migration;

/**
 * m190417_182337_ChangeResourceFieldUniqueIndex migration.
 */
class m190417_182337_ChangeResourceFieldUniqueIndex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'expressforms_resource_fields_resourceId_fk',
            '{{%expressforms_resource_fields}}'
        );

        $this->dropIndex(
            'expressforms_resource_fields_resourceId_handle_unq_idx',
            '{{%expressforms_resource_fields}}'
        );

        $this->addForeignKey(
            'expressforms_resource_fields_resourceId_fk',
            '{{%expressforms_resource_fields}}',
            'resourceId',
            '{{%expressforms_resources}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'expressforms_resource_fields_resourceId_handle_category_unq_idx',
            '{{%expressforms_resource_fields}}',
            ['resourceId', 'handle', 'category'],
            true
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'expressforms_resource_fields_resourceId_fk',
            '{{%expressforms_resource_fields}}'
        );

        $this->dropIndex(
            'expressforms_resource_fields_resourceId_handle_category_unq_idx',
            '{{%expressforms_resource_fields}}'
        );

        $this->addForeignKey(
            'expressforms_resource_fields_resourceId_fk',
            '{{%expressforms_resource_fields}}',
            'resourceId',
            '{{%expressforms_resources}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'expressforms_resource_fields_resourceId_handle_unq_idx',
            '{{%expressforms_resource_fields}}',
            ['resourceId', 'handle'],
            true
        );

        return true;
    }
}
