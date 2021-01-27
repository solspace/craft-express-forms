import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { toCamelCase } from '../../functions/string';
import { translate } from '../../functions/translator';
import { url } from '../../functions/url';
import { updateForm } from '../../reducers/form';
import { EMAIL } from '../fields/types';
import Integration from '../integrations/Integration';
import Block from './block/Block';
import { Color, Lightswitch, Select, Text, Textarea } from './block/inputs';
import './settings.styl';

const mapStateToProps = (state) => ({
  isPro: state.extra.isPro,
  form: state.form,
  fields: state.form.fields,
  notifications: state.extra.notifications,
  integrations: state.extra.integrations,
  integrationMapping: state.form.integrations,
});

const mapDispatchToProps = (dispatch) => ({
  update: (key, value) => dispatch(updateForm(key, value)),
});

export class SettingsContainer extends React.Component {
  static propTypes = {
    isPro: PropTypes.bool,
    form: PropTypes.object,
    notifications: PropTypes.array,
    integrationsMapping: PropTypes.object,
    integrations: PropTypes.array,
  };

  handleSettingUpdate = (event) => {
    const { name, value, dataset } = event.target;

    let valueToCamelCase = false;
    if (dataset && dataset.valueToCamelCase) {
      valueToCamelCase = !!dataset.valueToCamelCase;
    }

    const {
      update,
      form: { id = null },
    } = this.props;

    update(name, valueToCamelCase ? toCamelCase(value) : value);

    if (name === 'name' && id === null) {
      update('handle', toCamelCase(value));
    }
  };

  handleColorUpdate = (event) => {
    const { update } = this.props;

    update('color', event.hex);
  };

  getNotificationOptions = () => {
    const notifications = [];

    for (const notification of this.props.notifications) {
      notifications.push({
        value: notification.fileName,
        label: notification.name,
      });
    }

    return notifications;
  };

  getEmailFields = () => {
    const { fields } = this.props;
    const list = [];

    for (const field of fields) {
      if (field.type === EMAIL) {
        list.push({
          value: field.uid,
          label: field.name,
        });
      }
    }

    return list;
  };

  getExpressFieldOptions = () => {
    const { fields } = this.props;

    const expressFieldOptions = [];
    for (const field of fields) {
      expressFieldOptions.push({
        value: field.uid,
        label: field.name,
      });
    }

    return expressFieldOptions;
  };

  getIntegrationMappings = () => {
    const { integrations, integrationMapping = {}, isPro } = this.props;

    if (!isPro) {
      const linkHtml = translate(`<a href="{link}">Upgrade to Pro</a> to get access to popular API integrations.`, {
        link: url('express-forms/resources/explore'),
      });

      return <div dangerouslySetInnerHTML={{ __html: linkHtml }} />;
    }

    if (!integrations.length) {
      return null;
    }

    const list = [];
    for (let type of integrations) {
      const { name, handle, integrationType, resources } = type;

      const mapping = integrationMapping[handle] ?? {};
      list.push(
        <Integration
          key={handle}
          name={name}
          handle={handle}
          integrationType={integrationType}
          resources={resources}
          formFields={this.getExpressFieldOptions()}
          mapping={mapping}
        />
      );
    }

    return list;
  };

  render() {
    const { form } = this.props;
    const integrationMappings = this.getIntegrationMappings();

    return (
      <div className="settings">
        <Block title={'Form Settings'} description={'Control and set the basic settings for your form here.'}>
          <Text
            label="Name"
            description="The name you see for form in the control panel, and also available for use in templates and email notification templates."
            name="name"
            value={form.name ?? ''}
            required={true}
            saveHandler={this.handleSettingUpdate}
          />
          <Text
            label="Handle"
            description="Used for calling the form inside a template."
            name="handle"
            value={form.handle ?? ''}
            required={true}
            saveHandler={this.handleSettingUpdate}
            valueToCamelCase={true}
          />
          <Textarea
            label="Description"
            description="An internal note explaining the purpose of the form, and also available for use in templates."
            name="description"
            value={form.description ?? ''}
            required={false}
            saveHandler={this.handleSettingUpdate}
          />
          <Color
            label="Color"
            description="Used for styling form card in CP as well as differentiating the form's submissions in graphs like widgets, etc. Also available for use in templates."
            name="color"
            value={form.color ?? ''}
            required={false}
            saveHandler={this.handleColorUpdate}
          />
          <Text
            label="Submission Title"
            description="The generated title for the submission, similar to Craft Entries, etc. Can use Freeform fields, e.g. '{firstName} {lastName}'."
            name="submissionTitle"
            value={form.submissionTitle ?? ''}
            required={true}
            saveHandler={this.handleSettingUpdate}
          />
          <Lightswitch
            label="Save Submissions"
            description="Do you want save submissions for this form to the database?"
            name="saveSubmissions"
            value={form.saveSubmissions ?? true}
            required={false}
            saveHandler={this.handleSettingUpdate}
          />
        </Block>

        <Block
          title={'Notifications'}
          description={'This area allows you to manage email notifications for your form.'}
        >
          <Select
            label="Admin Notification"
            description="Select the email notification template that should be used for Admin email notifications."
            name="adminNotification"
            value={form.adminNotification ?? ''}
            required={false}
            saveHandler={this.handleSettingUpdate}
            emptyOption="Select template..."
            options={this.getNotificationOptions()}
          />
          {form.adminNotification && (
            <Textarea
              label="Admin Email(s)"
              description="Specify admin email address(es) to be notified. Separate multiples by line breaks only."
              name="adminEmails"
              value={form.adminEmails ?? ''}
              required={false}
              saveHandler={this.handleSettingUpdate}
            />
          )}
          <Select
            label="Submitter Notification"
            description="Select the email notification template that should be used for sending an email notification to the submitter of the form."
            name="submitterNotification"
            value={form.submitterNotification ?? ''}
            required={false}
            saveHandler={this.handleSettingUpdate}
            emptyOption="Select template..."
            options={this.getNotificationOptions()}
          />
          {form.submitterNotification && (
            <Select
              label="Submitter Email"
              description="Select the Email field in your form that will contain the email address of the submitter."
              name="submitterEmailField"
              value={form.submitterEmailField ?? ''}
              required={false}
              saveHandler={this.handleSettingUpdate}
              emptyOption="Select..."
              options={this.getEmailFields()}
            />
          )}
        </Block>
        {integrationMappings && (
          <Block
            title="Integrations"
            description="With Express Forms Pro edition, you'll see options to connect your form to first party API integrations when you have at least 1 setup."
          >
            {integrationMappings}
          </Block>
        )}
      </div>
    );
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(SettingsContainer);
