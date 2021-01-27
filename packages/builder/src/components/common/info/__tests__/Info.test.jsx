import React from 'react';
import { shallow } from 'enzyme';
import Info from '../Info';

describe('<Info />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<Info description="test" />);
    expect(wrapper).toMatchSnapshot();
  });

  it('contains the right classname', () => {
    const wrapper = shallow(<Info description="test" />);
    expect(wrapper.find('.information-dot')).toHaveLength(1);
  });

  it('contains the description', () => {
    const wrapper = shallow(<Info description="Test description provided" />);
    expect(wrapper.prop('title')).toStrictEqual('Test description provided');
  });

  it('does not render if no description provided', () => {
    const wrapper = shallow(<Info />);
    expect(wrapper.isEmptyRender()).toBeTruthy();
  });
});
