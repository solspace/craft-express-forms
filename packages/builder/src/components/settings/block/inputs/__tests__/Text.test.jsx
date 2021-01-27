import { shallow } from 'enzyme';
import React from 'react';
import Text from '../Text';

describe('<Text />', () => {
  let wrapper;
  let onChange;
  beforeEach(() => {
    onChange = jest.fn();
    wrapper = shallow(<Text label="A Test Input" name="test_input" value="a value" saveHandler={onChange} />);
  });

  it('renders & displays correctly', () => {
    expect(wrapper).toMatchSnapshot();
  });

  it('contains a text input', () => {
    expect(wrapper.find("input[name='test_input']")).toHaveLength(1);
  });

  it('changes value', () => {
    const value = 'A different value';
    wrapper.find("input[name='test_input']").simulate('change', { target: { value } });
    expect(onChange).toBeCalledWith({ target: { value: 'A different value' } });
  });
});
