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
  <h4 class="search-source-title">Result count: <span x-text="count">&nbsp;</span></h4>
  <template x-for="result in results">
    <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
  </template>
</div>