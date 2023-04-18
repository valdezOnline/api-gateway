<div class="search-block" x-data="{
    source: 'Calisphere',
    query: new URLSearchParams(location.search).get('q'),
    results: [],
    count: 0,
    all_results_url: ''
  }" x-init=" fetch('https://library-public-api-f7ju7.ondigitalocean.app/api/search?q=' + query + '&s=' + source)
  .then(res => res.json())
  .then(res => {
    results = res.results;
    count = res.total;
    all_results_url = res.all_results_url;
  })">
  <div class="search-source-title">
    <div x-text="source">&nbsp</div>
    <a class="all-results-link" :href="all_results_url">See all <span x-text="count">&nbsp;</span> results</a>
  </div>
  <div class="search-results">
    <template x-for="result in results">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
    </template>
  </div>
</div>