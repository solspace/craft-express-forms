$('.action-buttons a.icon.delete').on({
  click: (event) => {
    const { target } = event;
    const { notification, msg } = target.dataset;

    if (confirm(msg)) {
      $.ajax({
        url: Craft.getCpUrl('express-forms/settings/email-notifications/delete'),
        type: 'post',
        dataType: 'json',
        data: {
          [Craft.csrfTokenName]: Craft.csrfTokenValue,
          notification,
        },
        success: (response) => {
          if (response.success) {
            window.location.href = Craft.getCpUrl('express-forms/settings/email-notifications');
          } else if (response.error) {
            Craft.cp.displayError(response.error);
          }
        },
      });
    }
  },
});
