<div class="search-block" x-data="{
    source: 'Calisphere',
    query: new URLSearchParams(location.search).get('q'),
    results: [],
    count: 0,
    all_results_url: '',
    loading: true,
    error: ''
  }" x-init="
  fetch('https://library-public-api-f7ju7.ondigitalocean.app/api/search?q=' + query + '&s=' + source)
  .then(res => res.json())
  .then(res => {
    results = res.results;
    count = res.total;
    all_results_url = res.all_results_url;
    loading = false;
    error = res.error;
  })">
  <div class="search-source-header">
    <div class="source-title" x-text="source">&nbsp</div>
    <a class="all-results-link" :href="all_results_url" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
      results</a>
  </div>
  <div class="search-results">
    <template x-for="result in results">
      <div class="search-result">
        <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
        <div class="result-details">
          <p x-text="result.type">&nbsp;</p>
          Included in <a x-text="result.collection_name" :href="result.collection_url">&nbsp;</a>
        </div>
      </div>
    </template>

    <div x-show="loading">
      Loading results...
    </div>
    <div class="search-error" x-show="error">
      Error loading results: <span x-text="error"></span>
    </div>
  </div>
</div>