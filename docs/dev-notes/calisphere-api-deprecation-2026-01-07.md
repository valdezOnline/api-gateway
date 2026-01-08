# Calisphere API Deprecation

**Status:** :x: Broken - Needs Fix
**Date Discovered:** 2026-01-07
**Priority:** Medium

## Problem Description

The Calisphere search source is failing with the error:
```
cURL error 6: Could not resolve host: solr.calisphere.org
```

The current implementation attempts to query:
```
https://solr.calisphere.org/solr/query/?q={query}&rows=5&wt=json&indent=true&mm=100%25&pf3=title&pf=text,title&qs=12&ps=12
```

## Root Cause

The Calisphere Solr API was **deprecated in February 2024**. Calisphere migrated their infrastructure to OpenSearch and the old `solr.calisphere.org` endpoint no longer exists.

From the official documentation:
> "As of February 2024, any API keys that have been assigned will no longer be able to access data through the legacy Solr API."

## Potential Solutions

### Option 1: Use DPLA API (Recommended)
The Digital Public Library of America (DPLA) aggregates Calisphere content and maintains a public API.

- **API Documentation:** https://pro.dp.la/developers
- **Note:** DPLA data may be slightly behind (last Calisphere snapshot was November 2023)

### Option 2: Wait for New Calisphere API
Calisphere is developing a new API strategy for their OpenSearch platform. Monitor for updates:
- https://help.oac.cdlib.org/support/solutions/articles/9000101639-calisphere-apis

### Option 3: Disable Calisphere Search
Temporarily remove or disable the Calisphere search source until a replacement is available.

## Files to Monitor/Update

- `app/Search/Sources/Calisphere.php` - Main search source implementation
- `resources/views/search-result-box.blade.php` - Test page includes Calisphere block

## Verification Steps

1. Check if new Calisphere API is available
2. Update `Calisphere.php` with new endpoint
3. Test search query on `/search-test?q=nature`
4. Verify results display correctly

## References

- [Calisphere API Help Center](https://help.oac.cdlib.org/support/solutions/articles/9000101639-calisphere-apis)
- [DPLA Developer Portal](https://pro.dp.la/developers)
- [Calisphere GitHub (ucldc)](https://github.com/ucldc)
