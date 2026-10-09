(function () {
  const canvas = document.getElementById('activityChart');
  if (!canvas || typeof window.Chart === 'undefined') return;

  let labels = [];
  let values = [];
  try {
    labels = JSON.parse(canvas.dataset.labels || '[]');
    values = JSON.parse(canvas.dataset.values || '[]');
  } catch (e) { /* ignore */ }

  const css = getComputedStyle(document.documentElement);
  const accent = css.getPropertyValue('--admin-accent').trim() || '#dc2626';
  const grid = css.getPropertyValue('--admin-border').trim() || '#e5e7eb';

  new Chart(canvas, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Activity',
        data: values,
        borderColor: accent,
        backgroundColor: accent + '22',
        fill: true,
        tension: 0.35,
        pointRadius: 4,
        pointBackgroundColor: accent,
        borderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: grid } },
        y: { beginAtZero: true, grid: { color: grid } },
      },
    },
  });
})();
