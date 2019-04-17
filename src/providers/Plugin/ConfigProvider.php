<?php

namespace Solspace\ExpressForms\providers\Plugin;

use Symfony\Component\Yaml\Yaml;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param string $name
     *
     * @return array
     */
    public function getConfig(string $name): array
    {
        $path = $this->getConfigDir() . '/' . $name . '.yaml';
        if (!file_exists($path)) {
            touch($path);
        }

        $config = Yaml::parse(file_get_contents($path));
        if (empty($config)) {
            $config = [];
        }

        return $config;
    }

    /**
     * @param string $name
     * @param array  $configuration
     *
     * @return bool
     */
    public function setConfig(string $name, array $configuration): bool
    {
        $path = $this->getConfigDir() . '/' . $name . '.yaml';
        if (!file_exists($path)) {
            touch($path);
        }

        return (bool) file_put_contents($path, Yaml::dump($configuration, 5, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }

    /**
     * @return string
     */
    private function getConfigDir(): string
    {
        return \Yii::getAlias('@config');
    }
}
