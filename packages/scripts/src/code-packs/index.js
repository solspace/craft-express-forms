import '@xf/styles/code-packs.styl';

('use strict');

const $prefix = $('#prefix');
const $components = $('#components-wrapper');
const firstFileLists = $('> div > ul.directory-structure', $components);

let prefixTimeout = null;

$(() => {
  $prefix.on({
    keyup: () => {
      clearTimeout(prefixTimeout);
      prefixTimeout = setTimeout(function () {
        updateFilePrefixes();
      }, 50);
    },
  });

  updateFilePrefixes();
});

const updateFilePrefixes = () => {
  firstFileLists.each(function () {
    const $fileList = $(this);
    $('> li > span[data-name]', $fileList).each(function () {
      $(this).html($prefix.val() + $(this).data('name'));
    });
  });
};
