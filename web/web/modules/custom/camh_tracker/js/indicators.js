/**
 * @file
 * Bar chart per indicator: the latest value for each country, sorted, one colour.
 * The countries are labelled on the axis, so no colour legend is needed.
 */
(function (Drupal, once, drupalSettings) {
  'use strict';

  Drupal.behaviors.camhIndicatorBars = {
    attach(context) {
      once('camh-indicator-bars', '.indicator-bars', context).forEach((el) => {
        const cfg = (drupalSettings.camhIndicators || {})[el.dataset.key];
        if (!cfg || typeof echarts === 'undefined') {
          return;
        }
        const locale = document.documentElement.lang || 'en';
        const full = new Intl.NumberFormat(locale, { maximumFractionDigits: 2 });
        const compact = new Intl.NumberFormat(locale, { notation: 'compact', maximumFractionDigits: 1 });
        const rows = cfg.rows.slice().reverse();
        el.style.height = Math.max(240, rows.length * 44 + 60) + 'px';
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const chart = echarts.init(el, null, { renderer: 'svg' });
        chart.setOption({
          animation: !reduced,
          textStyle: { fontFamily: '"Noto Sans", "Helvetica Neue", Arial, sans-serif', color: '#595959' },
          grid: { left: 8, right: 64, top: 8, bottom: 24, containLabel: true },
          xAxis: { type: 'value', min: 0, splitLine: { lineStyle: { color: '#D0D5DD', opacity: 0.7 } }, axisLabel: { color: '#595959', fontSize: 14, formatter: (v) => compact.format(v) } },
          yAxis: { type: 'category', data: rows.map((r) => r.name), axisTick: { show: false }, axisLine: { lineStyle: { color: '#D0D5DD' } }, axisLabel: { color: '#1A1A1A', fontSize: 14 } },
          tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, confine: true, textStyle: { color: '#1A1A1A', fontSize: 14 },
            formatter: (p) => '<strong>' + p[0].name + '</strong> (' + rows[p[0].dataIndex].year + ')<br>' + full.format(p[0].value) + ' ' + cfg.unit },
          series: [{
            type: 'bar', barWidth: 20, data: rows.map((r) => r.value),
            itemStyle: { color: '#0B6E99', borderRadius: [0, 4, 4, 0] },
            label: { show: true, position: 'right', color: '#1A1A1A', fontSize: 14, fontWeight: 600, formatter: (p) => (p.value >= 1000 ? compact.format(p.value) : full.format(p.value)) },
          }],
        });
        el.addEventListener('click', () => {});
        chart.on('click', (p) => {
          const row = rows[p.dataIndex];
          if (row) {
            window.location.href = row.url;
          }
        });
        if (window.ResizeObserver) {
          new ResizeObserver(() => chart.resize()).observe(el);
        }
      });
    },
  };
}(Drupal, once, drupalSettings));
