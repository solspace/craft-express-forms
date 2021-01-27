import './index.styl';
import iconActive from './assets/check.svg';
import iconUpgrade from './assets/star-solid.svg';

$('ul#cards li.checking').each(function () {
  const elem = $(this);
  const { handle } = elem.data();

  const url = Craft.getActionUrl('express-forms/integrations/check-connection');

  $.ajax({
    url,
    type: 'post',
    dataType: 'json',
    data: {
      handle,
      [Craft.csrfTokenName]: Craft.csrfTokenValue,
    },
    success: (response) => {
      if (response.success) {
        elem.attr('class', 'active');
      } else {
        elem.attr('class', 'errors');
        elem.attr('title', response.errors.join(', '));
      }
    },
    error: (response) => {
      console.error(response);
    },
  });
});

$('.upgrade-icon').html(iconUpgrade);
$('.active-icon').html(iconActive);
