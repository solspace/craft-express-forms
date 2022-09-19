<?php

namespace Solspace\Tests\ExpressForms\decorators\Forms\Export;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\decorators\Forms\Export\CsvExporterDecorator;
use Solspace\ExpressForms\decorators\Forms\Export\XmlExporterDecorator;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\services\Export;
use yii\base\Event;
use yii\web\Response;

/**
 * @internal
 *
 * @coversNothing
 */
class XmlExporterDecoratorTest extends TestCase
{
    /** @var CsvExporterDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->decorator = new XmlExporterDecorator();
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
                'id' => 123,
                'dateCreated' => new \DateTime('2019-03-07 12:00:00', new \DateTimeZone('UTC')),
                'Title' => 'Some Title',
                'An Array' => ['one', 'two', 'three' => 'four'],
                'Boolean' => true,
                'False' => false,
            ],
            [
                'id' => 456,
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
        $event = new ExportSubmissionsEvent('xml', $form, $submissions, $response);
        Event::trigger(
            Export::class,
            Export::EVENT_EXPORT_SUBMISSIONS,
            $event
        );

        self::assertSame('application/xml', $response->headers['content-type']);
        self::assertStringContainsString(
            'attachment; filename="Test Form submissions',
            $response->headers['content-disposition']
        );

        $expected = '<?xml version="1.0"?>
<root><submission><id>123</id><dateCreated>2019-03-07 12:00:00</dateCreated><title>Some Title</title><anArray><item>one</item><item>two</item><item>four</item></anArray><boolean>1</boolean><false/></submission><submission><id>456</id><dateCreated/><title>Another &lt;script&gt;alert("hello");&lt;/script&gt; title</title><anArray><item>one</item><item>two</item><item>four</item></anArray><boolean>1</boolean><false/></submission></root>
';

        self::assertSame($expected, $response->content);
    }
}
