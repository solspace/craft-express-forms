import '@xf/styles/cards.styl';
import iconDiscord from './assets/discord-icon.svg';
import iconSo from './assets/so-icon.svg';
import iconSupport from './assets/support-icon.svg';
import iconGithub from './assets/github-icon.svg';
import iconFeedback from './assets/feedback-icon.svg';
import iconNewsletter from './assets/newsletter-icon.svg';
import './community.styl';

(() => {
  $('#so-icon').html(iconSo);
  $('#discord-icon').html(iconDiscord);

  $('#github-icon').html(iconGithub);
  $('#support-icon').html(iconSupport);
  $('#feedback-icon').html(iconFeedback);
  $('#newsletter-icon').html(iconNewsletter);
})();
