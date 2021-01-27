import { fields } from './fields';
import { integrations } from './integrations';

// Update
// ==================
const UPDATE_FORM = 'UPDATE_FORM';

export const updateForm = (key, value) => ({
  type: UPDATE_FORM,
  key,
  value,
});

const updateFormValue = (state, { key, value }) => ({
  ...state,
  [key]: value,
});

// Reducer
// ==================

const defaultState = {
  submissionTitle: '{{ dateCreated|date("Y-m-d H:i:s") }}',
};

const updateFormReducer = (state = defaultState, action) => {
  if (action.type === UPDATE_FORM) {
    return updateFormValue(state, action);
  }

  return state;
};

export const form = (state = defaultState, action) => {
  return {
    ...updateFormReducer(state, action),
    fields: fields(state.fields, action),
    integrations: integrations(state.integrations, action),
  };
};
