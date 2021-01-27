import PropTypes from 'prop-types';
import React from 'react';
import { translate } from '../../../functions/translator';
import { Select } from '../../settings/block/inputs';

const Resources = ({ integrationType, selectedResourceId = null, resources = [], changeHandler }) => (
  <Select
    label={translate(integrationType === 'mailing-list' ? 'Mailing List' : 'Resource')}
    description={translate(
      integrationType === 'mailing-list'
        ? 'Choose a mailing list to connect and map form submissions to.'
        : 'Choose a resource for this CRM integration to map form submissions to.'
    )}
    emptyOption={translate('Select...')}
    options={resources}
    value={selectedResourceId ?? ''}
    saveHandler={changeHandler}
  />
);

Resources.propTypes = {
  integrationType: PropTypes.string,
  selectedResourceId: PropTypes.string,
  resources: PropTypes.array,
  changeHandler: PropTypes.func,
};

export default Resources;
