import './charts-explorer.styl';

Craft.ExpressForms.SubmissionsIndex = Craft.BaseElementIndex.extend({
  getViewClass: function (mode) {
    switch (mode) {
      case 'table':
        return Craft.ExpressForms.SubmissionsTableView;
      default:
        return this.base(mode);
    }
  },
  getDefaultSort: function () {
    return ['dateCreated', 'desc'];
  },
  getDefaultSourceKey: function () {
    // Did they request a specific category group in the URL?
    const defaultFormHandle = window.selectedExpressFormsFormHandle;

    if (this.settings.context === 'index' && typeof defaultFormHandle !== 'undefined') {
      for (let i = 0; i < this.$sources.length; i++) {
        const $source = $(this.$sources[i]);

        if ($source.data('handle') === defaultFormHandle) {
          return $source.data('key');
        }
      }
    }

    return this.base();
  },
});

// Register the ExpressForms SubmissionsIndex class
Craft.registerElementIndexClass('Solspace\\ExpressForms\\elements\\Submission', Craft.ExpressForms.SubmissionsIndex);
