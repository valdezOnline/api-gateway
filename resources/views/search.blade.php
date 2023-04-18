<div class="search-block" x-data="{
    source: 'Calisphere',
    query: new URLSearchParams(location.search).get('q'),
    results: [],
    count: 0,
  }" x-init=" fetch('https://library-public-api-f7ju7.ondigitalocean.app/api/search?q=' + query + '&s=' + source)
  .then(res => res.json())
  .then(res => {
    results = res.results;
    count = res.total;
  })">
  <div class="search-source-title">
    <div x-text="source">&nbsp</div>
    <a class="all-results-link" :href="result.all-results-url">See all <span x-text="result.count">&nbsp;</span> results</a>
  </div>
  <template x-for="result in results">
    <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
  </template>
</div>