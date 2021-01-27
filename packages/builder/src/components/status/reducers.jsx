// Update
// ==================
const UPDATE_STATUS = 'UPDATE_STATUS';

export const setStatusIsSaving = () => ({
  type: UPDATE_STATUS,
  key: 'saving',
  value: true,
});

export const setStatusIsNotSaving = () => ({
  type: UPDATE_STATUS,
  key: 'saving',
  value: false,
});

const updateStatusReducer = (state, { key, value }) => ({
  ...state,
  [key]: value,
});

// Reducer
// ==================

const defaultState = {
  saving: false,
};

export const status = (state = defaultState, action) => {
  switch (action.type) {
    case UPDATE_STATUS:
      return updateStatusReducer(state, action);
  }

  return state;
};
