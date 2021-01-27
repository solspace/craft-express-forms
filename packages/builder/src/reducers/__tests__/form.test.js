import { form, updateForm } from '../form';

describe('Form actions', () => {
  it('creates an action correctly', () => {
    const expectedAction = {
      type: 'UPDATE_FORM',
      key: 'name',
      value: 'Express Forms',
    };

    expect(updateForm('name', 'Express Forms')).toEqual(expectedAction);
  });
});

describe('Form reducers', () => {
  it('sets initial state', () => {
    expect(form(undefined, {})).toEqual({
      submissionTitle: '{{ dateCreated|date("Y-m-d H:i:s") }}',
      fields: [],
      integrations: {},
    });
  });

  it('sets restored state correctly', () => {
    const previousState = {
      name: 'Test name',
      description: 'Description of said form',
      fields: [],
      integrations: { crm: {}, mailingLists: {} },
      other: {
        category: 'value',
      },
    };
    expect(form(previousState, {})).toEqual(previousState);
  });

  it('updates value correctly', () => {
    const previousState = {
      name: 'Test name',
      description: 'Description of said form',
      fields: [],
      integrations: { crm: {}, mailingLists: {} },
      other: {
        category: 'value',
      },
    };

    expect(form(previousState, updateForm('description', 'a completely different value'))).toEqual({
      ...previousState,
      description: 'a completely different value',
    });
  });

  it('inserts new value correctly', () => {
    const previousState = {
      name: 'Express Form',
      fields: [],
      integrations: { crm: {}, mailingLists: {} },
    };

    expect(form(previousState, updateForm('description', 'Express form test'))).toEqual({
      ...previousState,
      description: 'Express form test',
    });
  });
});
