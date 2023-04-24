<!-- Calisphere -->
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
  <p class="source-heading">Calisphere is your gateway to digital collections from California's great libraries, archives, and museums. Discover over 2,100,000 images, texts, and recordings.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span class="pill" x-text="result.type">&nbsp;</span><br/>
        Collection: <a x-text="result.collection_name" :href="result.collection_url">&nbsp;</a>
      </div>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>

<!-- LibGuides-->
<div class="search-block" x-data="{
  source: 'LibGuides',
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
  <p class="source-heading">The library offers many subject guides prepared by library staff. The guides are updated periodically with resources for specific subject areas. Use these guides to get started with finding library resources in your discipline.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span class="pill" x-text="result.type">&nbsp;</span><br/>
        <span x-text="result.description">&nbsp;</span>
      </div>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>

<!-- UC Library Search-->
<div class="search-block" x-data="{
  source: 'UCLibrary',
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
  <div class="source-title">UC Library Search</div>
  <a class="all-results-link" :href="all_results_url" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
    results</a>
</div>
<div class="search-results">
  <p class="source-heading">UC Library Search connects the libraries on all 10 University of California campuses through a unified discovery and borrowing system.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span class="pill" x-text="result.type">&nbsp;</span>&nbsp;<span class="pill blue">Source: <span x-text="result.source">&nbsp;</span></span><br/>
        <span x-text="result.contents">&nbsp;</span>
      </div>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>

<!-- Course Reserves -->
<div class="search-block" x-data="{
  source: 'CourseReserves',
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
  <div class="source-title">Course Reserves</div>
  <a class="all-results-link" :href="all_results_url" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
    results</a>
</div>
<div class="search-results">
  <p class="source-heading">Library course reserves provide access to course readings and materials selected by instructors.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span class="pill" x-text="result.type">&nbsp;</span>&nbsp;<span class="pill blue">Source: <span x-text="result.source">&nbsp;</span></span>&nbsp;<span class="pill red">Course: <span x-text="result.crs">&nbsp;</span></span><br/>
        <span x-text="result.description">&nbsp;</span>
      </div>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>

<!-- Databases -->
<div class="search-block" x-data="{
  source: 'Databases',
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
  <div class="source-title">Databases</div>
  <a class="all-results-link" :href="all_results_url" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
    results</a>
</div>
<div class="search-results">
  <p class="source-heading">Find the best library databases for your research.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span x-text="result.description">&nbsp;</span>
      </div>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>

<!-- LibraryWebsite -->
<div class="search-block" x-data="{
  source: 'LibraryWebsite',
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
  <div class="source-title">UCR Library Website</div>
  <a class="all-results-link" :href="all_results_url" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
    results</a>
</div>
<div class="search-results">
  <p class="source-heading">Search for results on the UCR Library Website.</p>
  <template x-for="result in results">
    <div class="search-result">
      <a class="search-link" x-text="result.title" :href="result.url">&nbsp;</a>
      <div class="result-details">
        <span x-text="result.url">&nbsp;</span>
    </div>
  </template>

  <div class="loading" x-show="loading">
    Loading results...
  </div>
  <div class="search-error" x-show="error">
    Error loading results: <span x-text="error"></span>
  </div>
</div>
</div>