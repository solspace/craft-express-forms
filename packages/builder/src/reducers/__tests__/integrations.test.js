import * as reducers from '../integrations';

describe('Integrations actions', () => {
  it('creates a toggle action correctly', () => {
    const expected = { type: 'TOGGLE_MAPPING', handle: 'test-handle' };
    expect(reducers.toggleMapping('test-handle')).toStrictEqual(expected);
  });

  it('creates a changeResource action correctly', () => {
    const expected = {
      type: 'CHANGE_RESOURCE',
      handle: 'test-handle',
      resourceId: 'abc999',
      fieldMap: { one: null, two: null },
    };
    expect(reducers.changeResource('test-handle', 'abc999', { one: null, two: null })).toEqual(expected);
  });

  it('creates an updateFieldMap action correctly', () => {
    const expected = {
      type: 'UPDATE_FIELD_MAP',
      handle: 'test-handle',
      resourceFieldId: '000abc',
      expressFieldUid: 'some-uid',
    };

    expect(reducers.updateFieldMap('test-handle', '000abc', 'some-uid')).toStrictEqual(expected);
  });
});

describe('Integrations reducers', () => {
  it('sets initial state', () => {
    expect(reducers.integrations(undefined, {})).toStrictEqual({});
  });

  it('toggles non-existing mapping ', () => {
    expect(reducers.integrations({}, reducers.toggleMapping('test-handle'))).toEqual({
      'test-handle': { resourceId: '', fieldMap: {} },
    });
  });

  it('clears existing data when toggling off', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
        },
        reducers.toggleMapping('test-handle')
      )
    ).toEqual({});
  });

  it('toggles non-existing mapping without affecting others', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
          'test-handle-2': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
        },
        reducers.toggleMapping('test-handle-3')
      )
    ).toEqual({
      'test-handle': {
        resourceId: 'some-resource',
        fieldMap: { some: 'value' },
      },
      'test-handle-2': {
        resourceId: 'some-resource',
        fieldMap: { some: 'value' },
      },
      'test-handle-3': { resourceId: '', fieldMap: {} },
    });
  });

  it('toggles existing mapping without affecting others', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
          'test-handle-2': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
          'test-handle-3': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
        },
        reducers.toggleMapping('test-handle-3')
      )
    ).toEqual({
      'test-handle': {
        resourceId: 'some-resource',
        fieldMap: { some: 'value' },
      },
      'test-handle-2': {
        resourceId: 'some-resource',
        fieldMap: { some: 'value' },
      },
    });
  });

  it('changes resource', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource-id',
            fieldMap: {
              abc000: 'one',
              abc001: 'two',
              abc002: 'three',
            },
          },
          other: {
            resourceId: 'some-other-resource-id',
            fieldMap: {
              one: 'one',
              two: 'two',
              three: 'three',
            },
          },
        },
        reducers.changeResource('test-handle', 'completely-different-id', {
          cba100: null,
          cba200: null,
        })
      )
    ).toEqual({
      'test-handle': {
        resourceId: 'completely-different-id',
        fieldMap: {
          cba100: null,
          cba200: null,
        },
      },
      other: {
        resourceId: 'some-other-resource-id',
        fieldMap: {
          one: 'one',
          two: 'two',
          three: 'three',
        },
      },
    });
  });

  it('updates field map fields', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource-id',
            fieldMap: {
              abc000: null,
              abc001: null,
            },
          },
        },
        reducers.updateFieldMap('test-handle', 'abc001', 'some-hash')
      )
    ).toEqual({
      'test-handle': {
        resourceId: 'some-resource-id',
        fieldMap: {
          abc000: null,
          abc001: 'some-hash',
        },
      },
    });
  });

  it('updates a mapping field map without touching other mappings', () => {
    expect(
      reducers.integrations(
        {
          'test-handle': {
            resourceId: 'some-resource',
            fieldMap: { some: 'value' },
          },
          other: {
            resourceId: 'some-resource-id',
            fieldMap: { one: 'one', two: 'two', three: 'three' },
          },
        },
        reducers.updateFieldMap('test-handle', 'some', 'other value')
      )
    ).toEqual({
      'test-handle': {
        resourceId: 'some-resource',
        fieldMap: { some: 'other value' },
      },
      other: {
        resourceId: 'some-resource-id',
        fieldMap: { one: 'one', two: 'two', three: 'three' },
      },
    });
  });
});
