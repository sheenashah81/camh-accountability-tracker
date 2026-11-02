/**
 * @file
 * Keeps anchor jumps and keyboard focus clear of the fixed header.
 *
 * On wide screens the header stays in view. This script measures its real height
 * (it changes with window width, zoom and the length of the site title) and sets
 * a CSS variable that the page uses as scroll padding, so the 'Skip to main
 * content' jump and Tab focus never end up underneath the header.
 */
(function () {
  'use strict';

  function update() {
    const header = document.querySelector('header');
    if (!header) {
      return;
    }
    const sticky = getComputedStyle(header).position === 'sticky';
    document.documentElement.style.setProperty('--camh-header-h', (sticky ? header.getBoundingClientRect().height : 0) + 'px');
  }

  update();
  window.addEventListener('resize', update);
  window.addEventListener('load', update);
  if (window.ResizeObserver) {
    const h = document.querySelector('header');
    if (h) {
      new ResizeObserver(update).observe(h);
    }
  }
}());
