<div id="search-container">
  <input id="main-search" type="text" />
  <button id="search-button" onclick="search()" type="button">Search</button>
</div>
<script>
  function search() {
    let query = document.getElementById("main_search").value;
    window.location.href = "/onesearch?q=" + query;
  }
</script>