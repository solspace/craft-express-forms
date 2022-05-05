<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use craft\helpers\StringHelper;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\fields\Checkbox;
use Solspace\ExpressForms\fields\Email;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\fields\Options;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\fields\Textarea;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\resources\bundles\CodePacksIndex;
use Solspace\ExpressForms\services\Settings;
use Solspace\ExpressForms\utilities\CodePack\CodePack;

class CodePackDecorator extends AbstractDecorator
{
    public const FLASH_VAR_KEY = 'code_pack_prefix';
    public const DEMO_FORM_HANDLE = 'express-forms-demo';
    public const DEMO_EMAIL_NOTIFICATION = 'express_forms_demo_notification.twig';

    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
        ];
    }

    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event): void
    {
        $event->addItem('Demo');
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('demo' !== $event->getSelectedItem()) {
            return;
        }

        $event->setTitle('Demo');
        $event->setActionButton('');

        CodePacksIndex::register(Craft::$app->getView());

        $codePack = $this->getCodePack();

        $postInstallPrefix = \Craft::$app->session->getFlash(self::FLASH_VAR_KEY);
        if ($postInstallPrefix) {
            $event->addContent(
                Craft::$app->getView()->renderTemplate(
                    'express-forms/settings/_components/code-pack/post-install',
                    [
                        'codePack' => $codePack,
                        'prefix' => CodePack::getCleanPrefix($postInstallPrefix),
                    ]
                )
            );
        } else {
            $event->addContent(
                Craft::$app->getView()->renderTemplate(
                    'express-forms/settings/_components/code-pack',
                    [
                        'codePack' => $codePack,
                        'prefix' => 'express-forms-demo',
                    ]
                )
            );
        }
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('codePack');

        if (!empty($post) && \is_array($post)) {
            $prefix = $post['prefix'] ?? 'express-forms-demo';
            $codePack = $this->getCodePack();

            $prefix = preg_replace('/[^a-zA-Z_0-9-\/]/', '', $prefix);

            $codePack->install($prefix);

            $this->installEmailNotifications($prefix);
            $this->installDemoForm();

            \Craft::$app->session->setFlash(self::FLASH_VAR_KEY, $prefix);
        }
    }

    public function installEmailNotifications(string $prefix): void
    {
        $templateName = self::DEMO_EMAIL_NOTIFICATION;

        $settings = ExpressForms::getInstance()->settings->getSettingsModel();
        $path = $settings->getEmailNotificationsPath();
        if (!$path || !is_dir($path)) {
            $path = \Craft::$app->path->getSiteTemplatesPath().'/'.$prefix.'/_notifications';
            if (!is_dir($path) && !mkdir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }

            $settings->emailNotificationsDirectoryPath = $path;
            Craft::$app->plugins->savePluginSettings(ExpressForms::getInstance(), $settings->toArray());
        }

        $notification = EmailNotification::create($path, $templateName, true);
        $notification->setReplyTo('{email}');
        $notification->writeToFile($path.'/'.$templateName);
    }

    private function installDemoForm(): void
    {
        $oldForm = ExpressForms::getInstance()->forms->getFormByHandle(self::DEMO_FORM_HANDLE);
        if ($oldForm) {
            ExpressForms::getInstance()->forms->deleteById($oldForm->getId());
        }

        $emailUid = StringHelper::UUID();
        $form = (new Form())
            ->setName('Express Demo Form')
            ->setHandle(self::DEMO_FORM_HANDLE)
            ->setDescription('Example demo form for Express Forms demo.')
            ->setSubmissionTitle('{subject}')
            ->setSaveSubmissions()
            ->setSubmitterNotification(self::DEMO_EMAIL_NOTIFICATION)
            ->setSubmitterEmailField($emailUid)
        ;

        $form
            ->addField(
                new Text(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'First Name',
                        'handle' => 'firstName',
                        'required' => true,
                    ]
                )
            )
            ->addField(
                new Text(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'Last Name',
                        'handle' => 'lastName',
                        'required' => true,
                    ]
                )
            )
            ->addField(
                new Email(
                    [
                        'uid' => $emailUid,
                        'name' => 'Email',
                        'handle' => 'email',
                        'required' => true,
                    ]
                )
            )
            ->addField(
                new Text(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'Subject',
                        'handle' => 'subject',
                        'required' => true,
                    ]
                )
            )
            ->addField(
                new Textarea(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'Message',
                        'handle' => 'message',
                        'required' => false,
                    ]
                )
            )
            ->addField(
                new Options(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'How did you hear about us?',
                        'handle' => 'howHeard',
                        'required' => false,
                    ]
                )
            )
            ->addField(
                new File(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'Attachment',
                        'handle' => 'attachment',
                        'required' => false,
                    ]
                )
            )
            ->addField(
                new Checkbox(
                    [
                        'uid' => StringHelper::UUID(),
                        'name' => 'Accept Terms',
                        'handle' => 'acceptTerms',
                        'required' => true,
                    ]
                )
            )
        ;

        ExpressForms::getInstance()->forms->save($form);
    }

    private function getCodePack(): CodePack
    {
        return new CodePack(__DIR__.'/../../../codepack');
    }
}
