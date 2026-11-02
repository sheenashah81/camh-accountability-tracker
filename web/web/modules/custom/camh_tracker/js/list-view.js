/**
 * @file
 * The Table or Map toggle on the Commitments list. The map shows the countries
 * in the current results; the table stays the default and is always available.
 */
(function (Drupal, once, drupalSettings) {
  'use strict';

  Drupal.behaviors.camhListView = {
    attach(context) {
      once('camh-list-toggle', '.view-toggle', context).forEach((group) => {
        const buttons = group.querySelectorAll('.view-toggle__button');
        const mapBox = document.querySelector('.list-map-wrapper');
        const content = document.querySelector('.view-commitments .view-content');
        let drawn = false;
        buttons.forEach((b) => b.addEventListener('click', () => {
          const showMap = b.dataset.view === 'map';
          buttons.forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
          if (mapBox) {
            mapBox.hidden = !showMap;
          }
          if (content) {
            content.hidden = showMap;
          }
          if (showMap && !drawn && drupalSettings.camhListMap && typeof echarts !== 'undefined') {
            drawn = true;
            Drupal.camhDrawMap(document.getElementById('list-map'), drupalSettings.camhListMap);
          }
        }));
      });
    },
  };
}(Drupal, once, drupalSettings));
