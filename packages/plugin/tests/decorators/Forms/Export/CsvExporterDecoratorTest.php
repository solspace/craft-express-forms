<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Export;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\Export\CsvExporterDecorator;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\services\Export;
use yii\base\Event;
use yii\web\Response;

/**
 * @internal
 * @coversNothing
 */
class CsvExporterDecoratorTest extends TestCase
{
    /** @var CsvExporterDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->decorator = new CsvExporterDecorator();
        $this->decorator->initEventListeners();
    }

    protected function tearDown(): void
    {
        $this->decorator->destructEventListeners();
    }

    public function testExportSubmissions()
    {
        $form = new Form();
        $form->setName('Test Form');

        $submissions = [
            [
                'ID' => 123,
                'dateCreated' => new \DateTime('2019-03-07 12:00:00', new \DateTimeZone('UTC')),
                'Title' => 'Some Title',
                'An Array' => ['one', 'two', 'three' => 'four'],
                'Boolean' => true,
                'False' => false,
            ],
            [
                'ID' => 456,
                'dateCreated' => null,
                'Title' => 'Another <script>alert("hello");</script> title',
                'An Array' => ['one', 'two', 'three' => 'four'],
                'Boolean' => true,
                'False' => false,
            ],
        ];

        new \yii\web\Application(
            [
                'id' => 'yii-test',
                'basePath' => __DIR__,
                'controllerNamespace' => 'yii\console\controllers',
            ]
        );

        $response = new Response();
        $event = new ExportSubmissionsEvent('csv', $form, $submissions, $response);
        Event::trigger(
            Export::class,
            Export::EVENT_EXPORT_SUBMISSIONS,
            $event
        );

        self::assertSame('text/csv', $response->headers['content-type']);
        self::assertStringContainsString(
            'attachment; filename="Test Form submissions',
            $response->headers['content-disposition']
        );

        $expected = <<<'STRING'
            ID,dateCreated,Title,"An Array",Boolean,False
            123,"2019-03-07 12:00:00","Some Title","one, two, four",yes,no
            456,,"Another &lt;script&gt;alert(&quot;hello&quot;);&lt;/script&gt; title","one, two, four",yes,no

            STRING;

        self::assertSame($expected, $response->content);
    }
}
