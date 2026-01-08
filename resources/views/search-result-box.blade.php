<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Test</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .search-form input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 4px;
        }
        .search-form input[type="text"]:focus {
            outline: none;
            border-color: #003DA5;
        }
        .search-form button {
            padding: 12px 24px;
            font-size: 16px;
            background: #003DA5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background: #002d7a;
        }
        .results-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .results-grid .search-block {
            width: 100%;
            margin: 0;
        }
        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="antialiased">
<h1>Search Test Page</h1>

<form class="search-form" x-data="{ query: new URLSearchParams(location.search).get('q') || '' }" @submit.prevent="if(query.trim()) window.location.href = '?q=' + encodeURIComponent(query.trim())">
    <input type="text" x-model="query" placeholder="Enter your search query..." autofocus>
    <button type="submit">Search</button>
</form>

<p x-data="{ q: new URLSearchParams(location.search).get('q') }" x-show="q">Showing results for: <strong x-text="q"></strong></p>
<hr x-data="{ q: new URLSearchParams(location.search).get('q') }" x-show="q">

<div class="results-grid">

<!-- LibGuides-->
<div class="search-block" x-data="{
  source: 'LibGuides',
  query: new URLSearchParams(location.search).get('q'),
  results: [],
  count: 0,
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title" x-text="source">&nbsp</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
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

<!-- Journals -->
<div class="search-block" x-data="{
  source: 'Journals',
  query: new URLSearchParams(location.search).get('q'),
  results: [],
  count: 0,
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title">Journals</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
    results</a>
</div>
<div class="search-results">
  <p class="source-heading">Search for journals available through the UC Library system.</p>
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

<!-- UC Library Search-->
<div class="search-block" x-data="{
  source: 'UCLibrary',
  query: new URLSearchParams(location.search).get('q'),
  results: [],
  count: 0,
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title">UC Library Search</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
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
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title">Course Reserves</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
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
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title">Databases</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
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
  allResultsLink: '',
  loading: true,
  error: ''
}" x-init="
fetch('/api/search?q=' + query + '&s=' + source)
.then(res => res.json())
.then(res => {
  results = res.results;
  count = res.total;
  allResultsLink = res.allResultsLink;
  loading = false;
  error = res.error;
})">
<div class="search-source-header">
  <div class="source-title">UCR Library Website</div>
  <a class="all-results-link" :href="allResultsLink" x-show="count > 0">See all <span x-text="count">&nbsp;</span>
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

</div><!-- end results-grid -->

</body>
</html>