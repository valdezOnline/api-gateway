<div x-data="{
    source: 'https://library-public-api-f7ju7.ondigitalocean.app/api/search?q='
    query: new URLSearchParams(location.search).get('q'),
    results: null,

    search() {
      fetch(this.source + this.query)
        .then(response => response.json())
        .then(data => this.results = data)
    }
  }" x-init="search()">
  <template x-for="result in results">
    <p x-text="results.title"></p>
  </template>
</div>