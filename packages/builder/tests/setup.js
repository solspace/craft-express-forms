const enzyme = require('enzyme');
const Adapter = require('enzyme-adapter-react-16');
require('raf/polyfill');

enzyme.configure({ adapter: new Adapter() });
