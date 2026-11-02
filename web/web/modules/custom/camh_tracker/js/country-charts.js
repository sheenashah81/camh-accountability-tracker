/**
 * @file
 * Draws one line chart per indicator on the country profile.
 *
 * Reads the values from the read-only Drupal JSON:API. One series per chart, a
 * single brand colour, no second axis, a label on the latest value and a hover
 * tooltip. The data table below each chart is the text alternative.
 */
(function (Drupal, once) {
  'use strict';

  const PRIMARY = '#0B6E99';
  const TEXT = '#1A1A1A';
  const MUTED = '#595959';
  const GRID = '#D0D5DD';

  async function load(figure) {
    const { country, indicator } = figure.dataset;
    const url = '/jsonapi/node/indicator_value'
      + '?filter[field_country.id]=' + encodeURIComponent(country)
      + '&filter[field_indicator.id]=' + encodeURIComponent(indicator)
      + '&sort=field_year&page[limit]=50'
      + '&fields[node--indicator_value]=field_year,field_value,field_sex';
    const response = await fetch(url, { headers: { Accept: 'application/vnd.api+json' } });
    if (!response.ok) {
      throw new Error('Could not load data (' + response.status + ')');
    }
    const body = await response.json();
    let rows = body.data.map((d) => ({
      year: Number(d.attributes.field_year),
      value: Number(d.attributes.field_value),
      sex: d.attributes.field_sex,
    }));
    if (rows.some((r) => r.sex === 'Total')) {
      rows = rows.filter((r) => r.sex === 'Total');
    }
    return rows.sort((a, b) => a.year - b.year);
  }

  function draw(canvas, rows, unit, locale) {
    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const full = new Intl.NumberFormat(locale, { maximumFractionDigits: 2 });
    const compact = new Intl.NumberFormat(locale, { notation: 'compact', maximumFractionDigits: 1 });
    const chart = echarts.init(canvas, null, { renderer: 'svg' });
    chart.setOption({
      animation: !reduced,
      textStyle: { fontFamily: '"Noto Sans", "Helvetica Neue", Arial, sans-serif', color: MUTED },
      grid: { left: 8, right: 72, top: 16, bottom: 8, containLabel: true },
      xAxis: {
        type: 'category', data: rows.map((r) => String(r.year)), boundaryGap: false,
        axisLine: { lineStyle: { color: GRID } }, axisTick: { show: false },
        axisLabel: { color: MUTED, fontSize: 14, hideOverlap: true },
      },
      yAxis: {
        type: 'value', min: 0,
        splitLine: { lineStyle: { color: GRID, opacity: 0.7 } },
        axisLabel: { color: MUTED, fontSize: 14, formatter: (v) => compact.format(v) },
      },
      tooltip: {
        trigger: 'axis', confine: true, borderColor: GRID, textStyle: { color: TEXT, fontSize: 14 },
        axisPointer: { type: 'line', lineStyle: { color: MUTED, width: 1 } },
        formatter: (p) => '<strong>' + p[0].axisValue + '</strong><br>' + full.format(p[0].value) + ' ' + unit,
      },
      series: [{
        type: 'line', data: rows.map((r) => r.value), symbol: 'circle', symbolSize: 8,
        showSymbol: false, lineStyle: { width: 2, color: PRIMARY },
        itemStyle: { color: PRIMARY, borderColor: '#fff', borderWidth: 2 },
        emphasis: { focus: 'none' },
        markPoint: {
          symbol: 'circle', symbolSize: 10, silent: true, itemStyle: { color: PRIMARY, borderColor: '#fff', borderWidth: 2 },
          label: { show: false },
          data: [{ coord: [String(rows[rows.length - 1].year), rows[rows.length - 1].value] }],
        },
        endLabel: {
          show: true, color: TEXT, fontSize: 14, fontWeight: 600,
          formatter: (p) => (p.value >= 1000 ? compact.format(p.value) : full.format(p.value)),
        },
      }],
    });
    const resize = () => chart.resize();
    if (window.ResizeObserver) {
      new ResizeObserver(resize).observe(canvas);
    }
    else {
      window.addEventListener('resize', resize);
    }
  }

  Drupal.behaviors.camhCountryCharts = {
    attach(context) {
      once('camh-chart', '.indicator-chart', context).forEach((figure) => {
        const canvas = figure.querySelector('.indicator-chart__canvas');
        const status = figure.querySelector('.indicator-chart__status');
        const locale = document.documentElement.lang || 'en';
        load(figure).then((rows) => {
          if (!rows.length) {
            status.textContent = Drupal.t('No data available for this chart.');
            return;
          }
          status.hidden = true;
          draw(canvas, rows, figure.dataset.unit, locale);
        }).catch(() => {
          status.textContent = Drupal.t('The chart could not be loaded. The data table below has the same values.');
        });
      });
    },
  };
}(Drupal, once));
