// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html

module.exports = {
  // Automatically clear mock calls and instances between every test
  clearMocks: true,

  // The directory where Jest should output its coverage files
  coverageDirectory: 'coverage',

  // An array of file extensions your modules use
  moduleFileExtensions: [
    // "ts",
    // "tsx",
    'js',
    'jsx',
  ],

  // A map from regular expressions to module names that allow to stub out resources with a single module
  moduleNameMapper: {
    '\\.(css|less|scss|sass|styl)$': 'identity-obj-proxy',
    '\\.(svg)$': '<rootDir>/tests/svg-transformer.js',
    '^@xf/builder/(.*)$': '<rootDir>/src/$1',
    '^@xf/styles/(.*)$': '<rootDir>/../styles/src/$1',
  },

  // The test environment that will be used for testing
  testEnvironment: 'node',

  // The glob patterns Jest uses to detect test files
  testMatch: ['**/__tests__/*.+(js|jsx)'],

  snapshotSerializers: ['enzyme-to-json/serializer'],
  setupFilesAfterEnv: ['./tests/setup.js'],
};
