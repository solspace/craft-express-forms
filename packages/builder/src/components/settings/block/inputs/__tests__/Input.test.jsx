import { shallow } from 'enzyme';
import React from 'react';
import BaseInput from '../BaseInput';

describe('<BaseInput />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<TestInput label="Test Input" name="test_input" value="test value" />);
    expect(wrapper).toMatchSnapshot();
  });

  it('shows asterisk if required', () => {
    const wrapper = shallow(<TestInput label="Test Input" required={true} name="test_input" value="test value" />);
    expect(wrapper).toMatchSnapshot();
    expect(wrapper.find('label.required')).toHaveLength(1);
  });
});

class TestInput extends BaseInput {
  static propTypes = {
    ...BaseInput.propTypes,
  };

  renderInput() {
    return <input type="text" value="test value" />;
  }
}
