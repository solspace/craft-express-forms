<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace Solspace\ExpressForms\services;

use Solspace\Commons\Translators\CraftTranslator;
use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\ExpressFormDecoratorInterface;
use Solspace\ExpressForms\factories\FieldFactory;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\factories\IntegrationMappingFactory;
use Solspace\ExpressForms\providers\Files\FileTypeProvider;
use Solspace\ExpressForms\providers\Files\FileTypeProviderInterface;
use Solspace\ExpressForms\providers\Files\FileUploadInterface;
use Solspace\ExpressForms\providers\Files\FileUploadProvider;
use Solspace\ExpressForms\providers\Integrations\IntegrationTypeProvider;
use Solspace\ExpressForms\providers\Integrations\IntegrationTypeProviderInterface;
use Solspace\ExpressForms\providers\Logging\LoggerProvider;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProvider;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProviderInterface;
use Solspace\ExpressForms\providers\Plugin\ConfigProvider;
use Solspace\ExpressForms\providers\Plugin\ConfigProviderInterface;
use Solspace\ExpressForms\providers\Plugin\SettingsProvider;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\Security\Hashing;
use Solspace\ExpressForms\providers\Security\HashingInterface;
use Solspace\ExpressForms\providers\Session\FlashBagProvider;
use Solspace\ExpressForms\providers\Session\FlashBagProviderInterface;
use Solspace\ExpressForms\providers\Session\SessionProvider;
use Solspace\ExpressForms\providers\Session\SessionProviderInterface;
use Solspace\ExpressForms\providers\View\RenderProvider;
use Solspace\ExpressForms\providers\View\RenderProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProvider;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;
use Solspace\ExpressForms\serializers\FieldSerializer;
use Solspace\ExpressForms\serializers\FormSerializer;
use yii\di\Container as YiiContainer;

class Container extends BaseService
{
    public function init()
    {
        $this->initDependencies();
    }

    /**
     * @return FormSerializer
     */
    public function formSerializer(): FormSerializer
    {
        return $this->getContainer()->get(FormSerializer::class);
    }

    /**
     * @return FieldSerializer
     */
    public function fieldSerializer(): FieldSerializer
    {
        return $this->getContainer()->get(FieldSerializer::class);
    }

    /**
     * @return FormFactory
     */
    public function formFactory(): FormFactory
    {
        return $this->getContainer()->get(FormFactory::class);
    }

    /**
     * @return FieldFactory
     */
    public function fieldFactory(): FieldFactory
    {
        return $this->getContainer()->get(FieldFactory::class);
    }

    /**
     * @return IntegrationMappingFactory
     */
    public function integrationMappingFactory(): IntegrationMappingFactory
    {
        return $this->getContainer()->get(IntegrationMappingFactory::class);
    }

    /**
     * @return FileTypeProviderInterface
     */
    public function getFileTypeProvider(): FileTypeProviderInterface
    {
        return $this->getContainer()->get(FileTypeProviderInterface::class);
    }

    /**
     * @param string $class
     *
     * @return ExpressFormDecoratorInterface
     */
    public function getDecorator(string $class): ExpressFormDecoratorInterface
    {
        return $this->getContainer()->get($class);
    }

    /**
     * @param string $class
     * @param array  $params
     * @param array  $config
     *
     * @return object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function get(string $class, array $params = [], array $config = [])
    {
        return $this->getContainer()->get($class, $params, $config);
    }

    /**
     * @return YiiContainer
     */
    private function getContainer(): YiiContainer
    {
        return \Craft::$container;
    }

    private function initDependencies()
    {
        $container = $this->getContainer();

        $container->set(HashingInterface::class, Hashing::class);
        $container->set(RequestProviderInterface::class, RequestProvider::class);
        $container->set(TranslatorInterface::class, CraftTranslator::class);
        $container->set(FileTypeProviderInterface::class, FileTypeProvider::class);
        $container->set(FileUploadInterface::class, FileUploadProvider::class);
        $container->set(RenderProviderInterface::class, RenderProvider::class);
        $container->set(LoggerProviderInterface::class, LoggerProvider::class);
        $container->set(SettingsProviderInterface::class, SettingsProvider::class);
        $container->set(EmailNotificationsProviderInterface::class, EmailNotificationsProvider::class);
        $container->set(IntegrationTypeProviderInterface::class, IntegrationTypeProvider::class);
        $container->set(ConfigProviderInterface::class, ConfigProvider::class);
        $container->set(FlashBagProviderInterface::class, FlashBagProvider::class);
        $container->set(SessionProviderInterface::class, SessionProvider::class);
    }
}
