<?php

namespace Solspace\ExpressForms\objects\Export;

class AssetField extends ArrayField
{
    public function transformValue(mixed $value): ?array
    {
        $values = parent::transformValue($value);

        $transformed = [];
        foreach ($values as $assetId) {
            $asset = \Craft::$app->assets->getAssetById((int) $assetId);
            if ($asset) {
                $assetValue = $asset->filename;
                if ($asset->getUrl()) {
                    $assetValue = $asset->getUrl();
                }

                $transformed[] = $assetValue;
            } else {
                $transformed[] = $assetId;
            }
        }

        return $transformed;
    }
}
