name: Report an Issue
about: Create a report about a bug or something that isn't working correctly in Express Forms.
title: ''
labels: issue
assignees: ''
body:
  - type: markdown
    attributes:
      value: |
        Thanks for taking the time to submit a bug report! Please fill out the fields to the best of your knowledge, so we can get to the bottom of the issue as quickly as possible.
  - type: textarea
    id: body
    attributes:
      label: What happened?
      value: |
        ### Describe the bug or issue you're experiencing



        ### Steps to reproduce

        1.

        ### Expected behavior



        ### Actual behavior

    validations:
      required: true
  - type: input
    id: cmsVersion
    attributes:
      label: Craft CMS Version
    validations:
      required: true
  - type: input
    id: expressformsVersion
    attributes:
      label: Express Forms Version
    validations:
      required: true
  - type: dropdown
    id: expressformsEdition
    attributes:
      label: Express Forms Edition
      options:
        - Lite
        - Pro
    validations:
      required: true
  - type: input
    id: phpVersion
    attributes:
      label: PHP version
  - type: checkboxes
    id: freshOrUpgrade
    attributes:
      label: Issue Started After Upgrade or Fresh Install?
      options:
        - Upgrade
        - Fresh Install
    validations:
      required: true