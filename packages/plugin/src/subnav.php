<?php

use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\ExpressForms;

$navItems = [];

if (PermissionHelper::checkPermission(ExpressForms::PERMISSION_FORMS)) {
    $navItems['forms'] = ['label' => ExpressForms::t('Forms'), 'url' => 'express-forms/forms'];
}

if (PermissionHelper::checkPermission(ExpressForms::PERMISSION_SUBMISSIONS)) {
    $navItems['submissions'] = ['label' => ExpressForms::t('Submissions'), 'url' => 'express-forms/submissions'];
}

if (
    PermissionHelper::checkPermission(ExpressForms::PERMISSION_SETTINGS)
    && Craft::$app->getConfig()->getGeneral()->allowAdminChanges
) {
    $navItems['settings'] = ['label' => ExpressForms::t('Settings'), 'url' => 'express-forms/settings'];
}

if (PermissionHelper::checkPermission(ExpressForms::PERMISSION_RESOURCES)) {
    $navItems['resources'] = ['label' => ExpressForms::t('Resources'), 'url' => 'express-forms/resources'];
}

return $navItems;
