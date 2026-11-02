/**
 * @file
 * The Overview map: country outlines plus a marker and a name label for every
 * tracked country, so small countries (UK, Jordan, Bangladesh, Philippines) are
 * easy to see. Dark large marker = has a published commitment; light small marker
 * = tracked, none published yet. Drawn as flat vector graphics with ECharts (no
 * WebGL, no tiles, no outside services). The table under the map has the same
 * information, so the map is never the only way in.
 */
(function (Drupal, once, drupalSettings) {
  'use strict';

  const C = { other: '#D9DDE3', border: '#FFFFFF', published: '#0B6E99', tracked: '#8CCBEA', ink: '#1A1A1A' };

  // The middle of a country's largest piece of land (so islands and overseas
  // territories do not pull the marker out to sea).
  function centre(feature) {
    const polys = feature.geometry.type === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;
    let best = null;
    let bestArea = -1;
    polys.forEach((p) => {
      const ring = p[0];
      const xs = ring.map((c) => c[0]);
      const ys = ring.map((c) => c[1]);
      const w = Math.max(...xs) - Math.min(...xs);
      const h = Math.max(...ys) - Math.min(...ys);
      if (w * h > bestArea) {
        bestArea = w * h;
        best = [(Math.max(...xs) + Math.min(...xs)) / 2, (Math.max(...ys) + Math.min(...ys)) / 2];
      }
    });
    return best;
  }

  Drupal.camhDrawMap = function (el, cfg) {
    return fetch(cfg.geojson).then((r) => r.json()).then((geo) => {
      echarts.registerMap('world', geo, {});
      const centres = {};
      geo.features.forEach((f) => { centres[f.properties.iso3] = centre(f); });
      const byIso = {};
      cfg.countries.forEach((c) => { byIso[c.iso3] = c; });
      const shown = cfg.countries.filter((c) => centres[c.iso3]);
      const chart = echarts.init(el, null, { renderer: 'svg' });
      const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      chart.setOption({
        animation: !reduced,
        tooltip: {
          trigger: 'item', confine: true, textStyle: { color: C.ink, fontSize: 14 },
          formatter: (p) => {
            const c = byIso[p.name];
            return c ? '<strong>' + c.name + '</strong><br>' + (c.shown_count !== undefined ? c.shown_count : c.published) + ' ' + cfg.text.published : '';
          },
        },
        geo: {
          map: 'world', nameProperty: 'iso3', roam: false, boundingCoords: [[-135, 66], [158, -44]],
          itemStyle: { areaColor: C.other, borderColor: C.border, borderWidth: 0.6 },
          emphasis: { disabled: true }, select: { disabled: true },
          regions: shown.map((c) => ({ name: c.iso3, itemStyle: { areaColor: c.published > 0 ? '#5AAAD0' : '#BFE3F5', borderColor: C.ink, borderWidth: 1 } })),
        },
        series: [{
          type: 'scatter', coordinateSystem: 'geo', z: 5,
          data: shown.map((c) => ({
            name: c.iso3, value: centres[c.iso3],
            symbolSize: c.published > 0 ? 26 : 14,
            itemStyle: c.published > 0
              ? { color: C.published, borderColor: '#FFFFFF', borderWidth: 3, shadowBlur: 6, shadowColor: 'rgba(0,0,0,0.45)' }
              : { color: C.tracked, borderColor: C.ink, borderWidth: 2 },
            label: {
              show: true, formatter: c.name, position: 'top', distance: c.published > 0 ? 8 : 5,
              fontSize: 14, fontWeight: c.published > 0 ? 700 : 400, color: C.ink,
              textBorderColor: '#FFFFFF', textBorderWidth: 3,
            },
          })),
        }],
      });
      chart.on('click', (p) => {
        const c = byIso[p.name];
        if (c) {
          window.location.href = c.url;
        }
      });
      if (window.ResizeObserver) {
        new ResizeObserver(() => chart.resize()).observe(el);
      }
      return chart;
    });
  };

  Drupal.behaviors.camhOverviewMap = {
    attach(context) {
      once('camh-overview-map', '#overview-map', context).forEach((el) => {
        if (drupalSettings.camhOverview && typeof echarts !== 'undefined') {
          Drupal.camhDrawMap(el, drupalSettings.camhOverview);
        }
      });
    },
  };
}(Drupal, once, drupalSettings));
