import { shallow } from 'enzyme';
import React from 'react';
import Select from '../Select';

describe('<Select />', () => {
  let wrapper;
  let onChange;
  beforeEach(() => {
    onChange = jest.fn();
    wrapper = shallow(
      <Select
        label="A Test Select Box"
        name="test_select"
        value="two"
        saveHandler={onChange}
        options={[
          { value: 'one', label: 'Value One' },
          { value: 'two', label: 'Value Two' },
          { value: 'three', label: 'Value Three' },
        ]}
      />
    );
  });

  it('renders & displays correctly', () => {
    expect(wrapper).toMatchSnapshot();
  });

  it('contains a textarea field', () => {
    expect(wrapper.find("select[name='test_select']")).toHaveLength(1);
  });

  it('changes value', () => {
    const value = 'three';
    const select = wrapper.find("select[name='test_select']");
    expect(select.prop('value')).toEqual('two');

    select.simulate('change', { target: { value } });
    expect(onChange).toBeCalledWith({ target: { value: 'three' } });
  });

  it('has an empty option', () => {
    const localWrapper = shallow(
      <Select
        label="A Test Select Box"
        name="test_select"
        value="two"
        saveHandler={onChange}
        emptyOption={'This has an empty option'}
        options={[
          { value: 'one', label: 'Value One' },
          { value: 'two', label: 'Value Two' },
          { value: 'three', label: 'Value Three' },
        ]}
      />
    );

    const select = localWrapper.find("select[name='test_select']");

    expect(select.find("option[value='']")).toHaveLength(1);
  });

  it('does not have an empty option', () => {
    const localWrapper = shallow(
      <Select
        label="A Test Select Box"
        name="test_select"
        value="two"
        saveHandler={onChange}
        options={[
          { value: 'one', label: 'Value One' },
          { value: 'two', label: 'Value Two' },
          { value: 'three', label: 'Value Three' },
        ]}
      />
    );

    const select = localWrapper.find("select[name='test_select']");

    expect(select.find("option[value='']")).toHaveLength(0);
  });
});
