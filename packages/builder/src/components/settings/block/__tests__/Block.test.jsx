import React from 'react';
import { shallow } from 'enzyme';
import Block from '../Block';
import BaseInput from '../inputs/BaseInput';

class TestInput extends BaseInput {
  renderInput() {
    return '';
  }
}

describe('<Block />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<Block title="Block Title" />);
    expect(wrapper).toMatchSnapshot();
  });

  it('displays a title', () => {
    const wrapper = shallow(<Block title="Block Title" />);
    expect(wrapper.find('h3').text()).toStrictEqual('Block Title <Info />');
  });

  it('renders children', () => {
    const wrapper = shallow(
      <Block title="Block Title">
        <TestInput label="Testing one" name="test1" value="test1" description="A description" />
        <TestInput label="Testing two" name="test2" value="test2" required={true} />
      </Block>
    );
    expect(wrapper).toMatchSnapshot();
    expect(wrapper.find('TestInput')).toHaveLength(2);
  });
});
