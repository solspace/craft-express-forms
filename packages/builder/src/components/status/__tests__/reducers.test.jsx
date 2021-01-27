import React from 'react';
import { setStatusIsNotSaving, setStatusIsSaving, status } from '../reducers';

describe('Status actions', () => {
  it("creates an 'is saving' action correctly", () => {
    const expectedAction = {
      type: 'UPDATE_STATUS',
      key: 'saving',
      value: true,
    };

    expect(setStatusIsSaving()).toEqual(expectedAction);
  });

  it("creates an 'is not saving' action correctly", () => {
    const expectedAction = {
      type: 'UPDATE_STATUS',
      key: 'saving',
      value: false,
    };

    expect(setStatusIsNotSaving()).toEqual(expectedAction);
  });
});

describe('Status reducers', () => {
  it('sets initial state', () => {
    expect(status(undefined, {})).toEqual({
      saving: false,
    });
  });

  it('sets restored state correctly', () => {
    const previousState = {
      test: 'value',
    };
    expect(status(previousState, {})).toEqual(previousState);
  });

  it("updates 'saving' state correctly", () => {
    const previousState = {
      status: {
        fake: 'status',
        other: false,
      },
    };

    expect(status(previousState, setStatusIsSaving())).toEqual({
      ...previousState,
      saving: true,
    });
  });
});
