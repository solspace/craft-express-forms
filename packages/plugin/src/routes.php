<?php

return [
    // Forms
    'express-forms' => 'express-forms/index/index',
    'express-forms/forms' => 'express-forms/forms/index',
    'express-forms/forms/new' => 'express-forms/forms/create',
    'express-forms/forms/save' => 'express-forms/forms/save',
    'express-forms/forms/sort' => 'express-forms/forms/sort',
    'express-forms/forms/reset-spam' => 'express-forms/forms/reset-spam',
    'express-forms/forms/delete' => 'express-forms/forms/delete',
    'express-forms/forms/duplicate' => 'express-forms/forms/duplicate',
    'express-forms/forms/<handle:(?:[^\/]*)>' => 'express-forms/forms/edit',
    // Submissions
    'express-forms/submissions' => 'express-forms/submissions/index',
    'express-forms/submissions/save' => 'express-forms/submissions/save',
    'express-forms/submissions/<id:\d+>' => 'express-forms/submissions/edit',
    'express-forms/submissions/<form:(?:[^\/]*)>/export' => 'express-forms/submissions/export',
    'express-forms/submissions/<form:(?:[^\/]*)>' => 'express-forms/submissions/index',
    // Settings
    'express-forms/settings/<category:(?:[^\/]*)>' => 'express-forms/settings/index',
    'express-forms/settings' => 'express-forms/settings/index',
    // Resources
    'express-forms/resources' => 'express-forms/resources/community',
    'express-forms/resources/community' => 'express-forms/resources/community',
    'express-forms/resources/explore' => 'express-forms/resources/explore',
    'express-forms/resources/support' => 'express-forms/resources/support',
    // Reports
    'express-forms/reports/submissions-index' => 'express-forms/reports/submissions-index',
    // Misc
    'express-forms/logs/clear' => 'express-forms/logs/clear',
];
