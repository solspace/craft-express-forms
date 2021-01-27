$(() => {
  const $assetDownloadForm = $('form#asset_download');

  $('#content').on(
    {
      click: function () {
        const { assetId } = $(this).data();

        $('input[name=assetId]', $assetDownloadForm).val(assetId);
        $assetDownloadForm.submit();
      },
    },
    'a[data-asset-id]'
  );
});
