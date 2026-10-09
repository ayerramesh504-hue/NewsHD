<?= view('layout/header') ?>

<section class="market-hero">
  <div class="container market-hero-inner">
    <div>
      <span class="market-kicker"><i class="fa-solid fa-chart-line"></i> Nepal Stock Exchange</span>
      <h1>NEPSE Market</h1>
      <p>Track selected Nepal share prices, daily movement, and trading volume in one place.</p>
    </div>
  </div>
</section>

<main class="section market-page">
  <div class="container">
    <div class="market-toolbar"><div><h2>Market tools</h2><p>Live charts and trade levels powered by NEPSE Alpha.</p></div></div>
    <section class="market-widgets">
      <div class="market-widget-card market-widget-wide">
        <div class="market-widget-head"><div><span class="market-kicker">Technical analysis</span><h2>NEPSE live chart</h2></div><a href="https://nepsealpha.com/nepse-chart" target="_blank" rel="noopener">Open full chart <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
        <iframe src="https://nepsealpha.com/nepse-chart" title="NEPSE live chart" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
      <div class="market-widget-card">
        <div class="market-widget-head"><div><span class="market-kicker">Trade levels</span><h2>Standard Pivot</h2></div><a href="https://nepsealpha.com/standard-pivot" target="_blank" rel="noopener">Open <i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
        <iframe src="https://nepsealpha.com/standard-pivot" title="NEPSE standard pivot" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div>
    </section>
    <p class="market-disclaimer"><i class="fa-solid fa-circle-info"></i> Market data is provided for information only and may be delayed. It is not investment advice.</p>
  </div>
</main>

<?= view('layout/footer') ?>
