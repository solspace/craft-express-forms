import React from 'react';
import { shallow } from 'enzyme';
import RadialActionButton from '../fieldlist/RadialActionButton';

describe('<RadialActionButton />', () => {
  it('renders & displays correctly', () => {
    const wrapper = shallow(<RadialActionButton type="text" />);
    expect(wrapper).toMatchSnapshot();
  });
});
