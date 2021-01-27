import { shallow } from 'enzyme';
import React from 'react';
import Textarea from '../Textarea';

describe('<Textarea />', () => {
  let wrapper;
  let onChange;
  beforeEach(() => {
    onChange = jest.fn();
    wrapper = shallow(<Textarea label="A Test Textarea" name="test_textarea" value="a value" saveHandler={onChange} />);
  });

  it('renders & displays correctly', () => {
    expect(wrapper).toMatchSnapshot();
  });

  it('contains a textarea field', () => {
    expect(wrapper.find("textarea[name='test_textarea']")).toHaveLength(1);
  });

  it('changes value', () => {
    const value = 'A different value';
    wrapper.find("textarea[name='test_textarea']").simulate('change', { target: { value } });
    expect(onChange).toBeCalledWith({ target: { value: 'A different value' } });
  });
});
