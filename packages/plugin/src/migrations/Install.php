<?php

namespace Solspace\ExpressForms\migrations;

use Solspace\Commons\Migrations\ForeignKey;
use Solspace\Commons\Migrations\StreamlinedInstallMigration;
use Solspace\Commons\Migrations\Table;

class Install extends StreamlinedInstallMigration
{
    protected function defineTableData(): array
    {
        return [
            (new Table('expressforms_forms'))
                ->addField('id', $this->primaryKey())
                ->addField('uuid', $this->string(100)->notNull())
                ->addField('fieldLayoutId', $this->integer(11))
                ->addField('name', $this->string(100)->notNull())
                ->addField('handle', $this->string(100)->notNull()->unique())
                ->addField('description', $this->text())
                ->addField('color', $this->string(20))
                ->addField('submissionTitle', $this->string(255)->notNull())
                ->addField('saveSubmissions', $this->boolean()->notNull()->defaultValue(true))
                ->addField('adminNotification', $this->string(255))
                ->addField('adminEmails', $this->text())
                ->addField('submitterNotification', $this->string(255))
                ->addField('submitterEmailField', $this->string(100))
                ->addField('spamCount', $this->integer()->unsigned()->notNull()->defaultValue(0))
                ->addField('fields', $this->mediumText())
                ->addField('integrations', $this->mediumText())
                ->addField('sortOrder', $this->integer()->defaultValue(0))
                ->addIndex(['sortOrder'])
                ->addIndex(['handle'], true)
                ->addForeignKey('fieldLayoutId', 'fieldlayouts', 'id', ForeignKey::CASCADE),

            (new Table('expressforms_submissions'))
                ->addField('id', $this->primaryKey())
                ->addField('formId', $this->integer()->notNull())
                ->addField('incrementalId', $this->integer()->notNull())
                ->addIndex(['incrementalId'], true)
                ->addForeignKey('id', 'elements', 'id', ForeignKey::CASCADE)
                ->addForeignKey('formId', 'expressforms_forms', 'id', ForeignKey::CASCADE),

            (new Table('expressforms_resources'))
                ->addField('id', $this->primaryKey())
                ->addField('typeClass', $this->string()->notNull())
                ->addField('handle', $this->string()->notNull())
                ->addField('name', $this->string()->notNull())
                ->addField('settings', $this->text())
                ->addField('sortOrder', $this->integer()->notNull()->defaultValue(0))
                ->addIndex(['sortOrder'])
                ->addIndex(['typeClass', 'handle'], true),

            (new Table('expressforms_resource_fields'))
                ->addField('id', $this->primaryKey())
                ->addField('resourceId', $this->integer()->notNull())
                ->addField('handle', $this->string()->notNull())
                ->addField('name', $this->string()->notNull())
                ->addField('type', $this->string()->notNull())
                ->addField('required', $this->boolean())
                ->addField('settings', $this->text())
                ->addField('category', $this->string())
                ->addField('sortOrder', $this->integer()->notNull()->defaultValue(0))
                ->addIndex(['sortOrder'])
                ->addIndex(['resourceId', 'handle', 'category'], true)
                ->addForeignKey('resourceId', 'expressforms_resources', 'id', ForeignKey::CASCADE),
        ];
    }
}
