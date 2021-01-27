import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import styled from 'styled-components';
import { translate } from '../../functions/translator';
import { createOption } from '../../functions/utility';
import { refreshResourcesAction } from '../../reducers/extra';
import { changeResource, toggleMapping, updateFieldMap } from '../../reducers/integrations';
import ToggleSwitch from '../common/lightswitch/ToggleSwitch';
import MappingTable from './components/MappingTable';
import Resources from './components/Resources';

const IntegrationBlock = styled.div`
  margin-bottom: 20px;
`;

const Title = styled.div`
  display: grid;
  grid-template-columns: auto 100px 32px;
  grid-column-gap: 10px;

  font-size: 16px;
  font-weight: 700;
  color: #576575;

  margin-bottom: 20px;
`;

const TableWrapper = styled.div`
  margin-bottom: 20px;

  &:last-child {
    margin-bottom: 0;
  }
`;

const RefreshButton = styled.div`
  justify-self: end;

  > button {
    position: relative;
    top: -1px;
  }
`;

const ToggleWrapper = styled.div`
  align-self: end;
`;

const mapStateToProps = (state) => ({
  isUpdatingIntegrations: state.extra.isUpdatingIntegrations,
});

const mapDispatchToProps = (dispatch) => ({
  toggleMapping: (handle) => dispatch(toggleMapping(handle)),
  changeResource: (handle, resourceId, fieldMap) => dispatch(changeResource(handle, resourceId, fieldMap)),
  updateMapping: (handle, resourceFieldId, expressFieldUid) =>
    dispatch(updateFieldMap(handle, resourceFieldId, expressFieldUid)),
  refreshResources: (handle) => dispatch(refreshResourcesAction(handle)),
});

class Integration extends React.Component {
  static propTypes = {
    name: PropTypes.string.isRequired,
    handle: PropTypes.string.isRequired,
    integrationType: PropTypes.string,
    resources: PropTypes.array.isRequired,
    mapping: PropTypes.object,
    formFields: PropTypes.array.isRequired,
    isUpdatingIntegrations: PropTypes.bool,
    toggleMapping: PropTypes.func,
    changeResource: PropTypes.func,
    updateMapping: PropTypes.func,
    refreshResources: PropTypes.func,
  };

  getMappingTable = () => {
    const { name: typeName, handle, formFields, mapping, resources, updateMapping } = this.props;
    const { resourceId, fieldMap = {} } = mapping;
    const resource = resources.find((resource) => resource.handle === resourceId);

    if (!resource) {
      return null;
    }

    const mappingsByCategory = { Default: [] };
    for (const field of resource.fields) {
      if (!field.category) {
        mappingsByCategory['Default'].push(field);
        continue;
      }

      if (typeof mappingsByCategory[field.category] === 'undefined') {
        mappingsByCategory[field.category] = [];
      }
      mappingsByCategory[field.category].push(field);
    }

    const mappingTables = [];
    for (const [name, fields] of Object.entries(mappingsByCategory)) {
      if (!fields.length) {
        continue;
      }

      mappingTables.push(
        <TableWrapper key={name}>
          <MappingTable
            title={name === 'Default' ? typeName : name}
            formFields={formFields}
            resourceFields={fields}
            map={fieldMap}
            updateTarget={(resourceFieldId, expressFieldUid) => updateMapping(handle, resourceFieldId, expressFieldUid)}
          />
        </TableWrapper>
      );
    }

    return mappingTables;
  };

  getResourceOptionsList() {
    const { resources } = this.props;

    const opts = [];
    for (const resource of resources) {
      opts.push(createOption(resource.name, resource.handle));
    }

    return opts;
  }

  handleChangeResource = (event) => {
    const { value: selectedResourceId } = event.target;
    const { resources, handle, changeResource } = this.props;

    const resource = resources.find((resource) => resource.handle === selectedResourceId);
    const fieldMap = {};
    if (resource) {
      for (const field of resource.fields) {
        fieldMap[field.handle] = null;
      }
    }

    changeResource(handle, selectedResourceId, fieldMap);
  };

  render() {
    const { name, handle, integrationType, mapping = null } = this.props;
    const { resourceId = null } = mapping;
    const { toggleMapping, refreshResources, isUpdatingIntegrations } = this.props;

    const enabled = resourceId !== null;

    return (
      <IntegrationBlock>
        <Title>
          {name}

          <RefreshButton>
            {enabled && (
              <button className="btn small" disabled={isUpdatingIntegrations} onClick={() => refreshResources(handle)}>
                {translate(isUpdatingIntegrations ? 'Refreshing...' : 'Refresh')}
              </button>
            )}
          </RefreshButton>

          <ToggleWrapper>
            <ToggleSwitch value={enabled} toggleHandler={() => toggleMapping(handle)} />
          </ToggleWrapper>
        </Title>

        {enabled && (
          <>
            <Resources
              integrationType={integrationType}
              resources={this.getResourceOptionsList()}
              selectedResourceId={resourceId}
              changeHandler={this.handleChangeResource}
            />

            {this.getMappingTable()}
          </>
        )}
      </IntegrationBlock>
    );
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Integration);
