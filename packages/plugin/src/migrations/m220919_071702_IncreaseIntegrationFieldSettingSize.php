<?php

namespace Solspace\ExpressForms\migrations;

use craft\db\Migration;

class m220919_071702_IncreaseIntegrationFieldSettingSize extends Migration
{
    public function safeUp(): bool
    {
        $this->alterColumn(
            '{{%expressforms_resource_fields}}',
            'settings',
            $this->mediumText()
        );

        return true;
    }

    public function safeDown(): bool
    {
        $this->alterColumn(
            '{{%expressforms_resource_fields}}',
            'settings',
            $this->text()
        );

        return true;
    }
}
