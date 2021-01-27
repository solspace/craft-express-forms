import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { saveFormUrl, newFormUrl, formsIndexUrl, editFormUrl } from '../../config';
import { setStatusIsNotSaving, setStatusIsSaving } from './reducers';
import { updateForm } from '../../reducers/form';
import { translate } from '../../functions/translator';
import { notice, error } from '../../functions/notifications';

const mapStateToProps = (state) => ({
  saving: state.status.saving,
  form: state.form,
});

const mapDispatchToProps = (dispatch) => ({
  setIsSaving: () => dispatch(setStatusIsSaving()),
  setNotSaving: () => dispatch(setStatusIsNotSaving()),
  setFormId: (id) => dispatch(updateForm('id', id)),
});

const originalTitle = 'Quick Save';
const progressTitle = 'Saving...';

class SaveContainer extends React.Component {
  static propTypes = {
    form: PropTypes.object,
    saving: PropTypes.bool,
    setIsSaving: PropTypes.func,
    setNotSaving: PropTypes.func,
    setFormId: PropTypes.func,
  };

  componentDidMount() {
    document.addEventListener('keydown', this.checkForSaveShortcut);

    document.getElementById('xf-save').addEventListener('click', this.save);
    document.getElementById('xf-save-finish').addEventListener('click', this.saveAndFinish);
    document.getElementById('xf-save-new').addEventListener('click', this.saveAndClear);
    document.getElementById('xf-save-duplicate').addEventListener('click', this.duplicate);
  }

  componentWillUnmount() {
    document.removeEventListener('keydown', this.checkForSaveShortcut);

    document.getElementById('xf-save').removeEventListener('click', this.save);
    document.getElementById('xf-save-finish').removeEventListener('click', this.saveAndFinish);
    document.getElementById('xf-save-new').removeEventListener('click', this.saveAndClear);
    document.getElementById('xf-save-duplicate').removeEventListener('click', this.duplicate);
  }

  checkForSaveShortcut = (event) => {
    const sKey = 83;
    const keyCode = event.which;

    if (keyCode === sKey && this.isModifierKeyPressed(event)) {
      event.preventDefault();
      this.save();
    }

    return false;
  };

  isModifierKeyPressed = (event) => {
    // metaKey maps to ⌘ on Macs
    if (window.navigator.platform.match(/Mac/)) {
      return event.metaKey;
    }

    // Both altKey and ctrlKey == true on some Windows keyboards when the right-hand ALT key is pressed
    // so just be safe and make sure altKey == false
    return event.ctrlKey && !event.altKey;
  };

  save = async () => {
    const { handle } = await this.saveActiveState();

    history.pushState(handle, '', editFormUrl(handle));
  };

  saveAndFinish = async () => {
    await this.saveActiveState();
    window.location = formsIndexUrl;
  };

  saveAndClear = async () => {
    await this.saveActiveState();
    window.location = newFormUrl;
  };

  duplicate = () => {
    const formId = this.saveActiveState();
  };

  saveActiveState = async () => {
    const { setIsSaving, setNotSaving, setFormId } = this.props;
    const { form } = this.props;

    const saveButton = document.getElementById('xf-save');

    setIsSaving();
    saveButton.value = translate(progressTitle);

    try {
      const response = await fetch(saveFormUrl, {
        method: 'post',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        },
        body: JSON.stringify(form),
      });

      const { success, errors, data } = await response.json();

      if (!success) {
        Object.keys(errors).forEach((key) => {
          error(errors[key]);
        });
        console.error(errors);
      }

      setNotSaving();
      saveButton.value = translate(originalTitle);

      if (data.id) {
        setFormId(data.id);
      }

      notice(translate('Form saved successfully'));

      return data;
    } catch (err) {
      error(err);
      console.error(err);

      setNotSaving();
      saveButton.value = translate(originalTitle);
    }

    return null;
  };

  render() {
    return <></>;
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(SaveContainer);
