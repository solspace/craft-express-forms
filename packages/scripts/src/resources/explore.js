import '@xf/styles/cards.styl';
import './explore.styl';
import iconExpressForms from './assets/express-forms.svg';
import iconFreeform from './assets/freeform.svg';
import iconCalendar from './assets/calendar.svg';
import iconDeveloper from './assets/develop.svg';
import iconUpgrade from '../integrations/assets/star-solid.svg';
import iconActive from '../integrations/assets/check.svg';

(() => {
  $('#proedition-icon').html(iconExpressForms);
  $('#freeform-icon').html(iconFreeform);
  $('#calendar-icon').html(iconCalendar);
  $('#developer-friendly-icon').html(iconDeveloper);
  $('.upgrade-icon').html(iconUpgrade);
  $('.active-icon').html(iconActive);
})();
