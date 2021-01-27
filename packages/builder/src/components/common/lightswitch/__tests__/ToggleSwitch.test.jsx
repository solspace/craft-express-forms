import { shallow } from 'enzyme';
import React from 'react';
import ToggleSwitch from '../ToggleSwitch';

describe('<ToggleSwitch />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<ToggleSwitch value={true} />);
    expect(wrapper).toMatchSnapshot();
  });

  it("Has the 'on' class when value is true", () => {
    const wrapper = shallow(<ToggleSwitch value={true} />);
    expect(wrapper.find('div.toggle-switch.on')).toHaveLength(1);
  });

  it("Does not have the 'on' class when value is false", () => {
    const wrapper = shallow(<ToggleSwitch value={false} />);
    expect(wrapper.find('div.toggle-switch.off')).toHaveLength(1);
  });

  it('changes value', () => {
    const onChange = jest.fn().mockReturnValue('clicked');
    const wrapper = shallow(<ToggleSwitch value={true} toggleHandler={onChange} />);

    const toggleSwitch = wrapper.find('div.toggle-switch.on');

    toggleSwitch.simulate('click');
    expect(onChange).toHaveBeenCalled();
  });
});
