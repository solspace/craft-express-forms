import url from './functions/url';

// Url's
export const newFormUrl = url('express-forms/forms/new');
export const saveFormUrl = url('express-forms/forms/save');
export const duplicateFormUrl = url('express-forms/forms/duplicate');
export const editFormUrl = (handle) => url('express-forms/forms/{handle}', { handle });
export const formsIndexUrl = url('express-forms/forms');

export const refreshIntegrationResourcesUrl = url('express-forms/integrations/refresh-resources');
