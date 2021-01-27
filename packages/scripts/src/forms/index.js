import { Sortable } from '@shopify/draggable';
import './index.styl';

$('.card-actions a.delete-form').on({
  click: function (event) {
    const { id, message } = event.target.dataset;

    if (confirm(message)) {
      $.ajax({
        type: 'post',
        url: Craft.getCpUrl('express-forms/forms/delete'),
        data: {
          [Craft.csrfTokenName]: Craft.csrfTokenValue,
          id,
        },
        success: ({ error = null }) => {
          if (error) {
            Craft.cp.displayError(error);
            return;
          }
          $(event.target).parents('li[data-id]').remove();
        },
        error: (response) => {
          Craft.cp.displayError(response);
        },
      });
    }
  },
});

$('.card-actions a.duplicate-form').on({
  click: function (event) {
    const { uuid, message } = event.target.dataset;

    if (confirm(message)) {
      $.ajax({
        type: 'post',
        url: Craft.getCpUrl('express-forms/forms/duplicate'),
        data: {
          [Craft.csrfTokenName]: Craft.csrfTokenValue,
          uuid,
        },
        success: ({ error = null }) => {
          if (error) {
            Craft.cp.displayError(error);
            return;
          }

          window.location.reload();
        },
        error: (response) => {
          Craft.cp.displayError(response);
        },
      });
    }
  },
});

const cardsList = document.getElementById('form-cards');
if (cardsList) {
  const sortable = new Sortable(cardsList, {
    draggable: 'li[data-id]',
    handle: '.drag-handle',
  });

  sortable.on('sortable:stop', () => {
    setTimeout(() => {
      const order = [...document.querySelectorAll('#form-cards > li')].map((item) => item.dataset.id);

      $.ajax({
        type: 'post',
        url: Craft.getCpUrl('express-forms/forms/sort'),
        data: {
          [Craft.csrfTokenName]: Craft.csrfTokenValue,
          order,
        },
      });
    }, 100);
  });
}

$('a.reset-spam').on({
  click: function () {
    const { uuid, message } = $(this).data();
    const self = $(this);

    if (!confirm(message)) {
      return false;
    }

    $.ajax({
      url: Craft.getCpUrl('express-forms/forms/reset-spam'),
      type: 'post',
      dataType: 'json',
      data: {
        [Craft.csrfTokenName]: Craft.csrfTokenValue,
        uuid: uuid,
      },
      success: (response) => {
        if (response.success) {
          self.siblings('.counter').html('0');
        } else {
          console.error(response);
        }
      },
    });

    return false;
  },
});

$('.exporter').on({
  change: function () {
    const val = $(this).val();

    if (val) {
      $(this).parents('form').submit();
    }

    $(this).val('');
  },
});
