<?php

// Helper functions for vanilla build/runtime URL rewriting.
// These work without Apache/Nginx rewrite rules by mapping
// router-style paths (e.g. /product/123) to built PHP entry files
// (e.g. product.php?id=123).

/**
 * Map a page relative path to its vanilla build destination.
 * 
 * Examples:
 * - pages/index.php           -> index.php
 * - pages/about.php           -> about.php
 * - pages/product/[id].php    -> product.php (use ?id=xxx)
 * - pages/type/[id].php       -> type.php (use ?id=xxx)
 * - pages/games/[name]/[id].php -> games/index.php (use ?name=xxx&id=xxx)
 */
function vanilla_mapPageToEntry($relativePath)
{
    $relativePath = str_replace('\\', '/', $relativePath);
    if (substr($relativePath, -4) === '.php') {
        $withoutExt = substr($relativePath, 0, -4);
    } else {
        $withoutExt = $relativePath;
    }

    $segments = $withoutExt === '' ? [] : explode('/', $withoutExt);
    $paramNames = [];
    $staticSegments = [];

    foreach ($segments as $seg) {
        if (preg_match('/^\[(.+)\]$/', $seg, $m)) {
            $paramNames[] = $m[1];
        } else {
            $staticSegments[] = $seg;
        }
    }

    $hasDynamic = !empty($paramNames);

    if (!$hasDynamic) {
        // No dynamic segment: keep structure as-is
        $dest = $relativePath;
        return [$dest, []];
    }

    // Dynamic route mapping rules:
    // - pages/product/[id].php        -> build/product.php
    // - pages/type/[id].php           -> build/type.php
    // - pages/games/[name]/[id].php   -> build/games/index.php

    if (empty($staticSegments)) {
        $dest = 'index.php';
    } elseif (count($staticSegments) === 1 && count($paramNames) === 1) {
        // Single static + single param -> flatten to product.php
        $dest = $staticSegments[0] . '.php';
    } else {
        // Multiple params or deeper nesting -> games/index.php style
        $dest = implode('/', $staticSegments) . '/index.php';
    }

    return [$dest, $paramNames];
}

function vanilla_matchRoutePathToPage($requestPath, $pagesDir)
{
    $requestPath = trim($requestPath, '/');
    $segments = $requestPath === '' ? ['index'] : explode('/', $requestPath);
    $currentPath = $pagesDir;
    $paramValues = [];

    if (count($segments) === 1 && $segments[0] === 'index') {
        $indexFile = $currentPath . '/index.php';
        if (file_exists($indexFile)) {
            $relative = substr($indexFile, strlen($pagesDir) + 1);
            return [$relative, $paramValues];
        }
    }

    $paramPattern = '/^\[(.*?)\]$/';
    $segmentCount = count($segments);

    for ($i = 0; $i < $segmentCount; $i++) {
        $segment = $segments[$i];
        $isLast = ($i === $segmentCount - 1);
        $found = false;

        if ($isLast) {
            $filePath = $currentPath . '/' . $segment . '.php';
            if (file_exists($filePath)) {
                $relative = substr($filePath, strlen($pagesDir) + 1);
                return [$relative, $paramValues];
            }

            $indexPath = $currentPath . '/' . $segment . '/index.php';
            if (file_exists($indexPath)) {
                $relative = substr($indexPath, strlen($pagesDir) + 1);
                return [$relative, $paramValues];
            }
        }

        $dirPath = $currentPath . '/' . $segment;
        if (is_dir($dirPath)) {
            $currentPath = $dirPath;
            continue;
        }

        $items = scandir($currentPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemName = pathinfo($item, PATHINFO_FILENAME);
            if (preg_match($paramPattern, $itemName, $matches)) {
                $paramName = $matches[1];
                $paramValues[$paramName] = $segment;

                if ($isLast && substr($item, -4) === '.php') {
                    $filePath = $currentPath . '/' . $item;
                    $relative = substr($filePath, strlen($pagesDir) + 1);
                    return [$relative, $paramValues];
                }

                $paramPath = $currentPath . '/' . $item;
                if (!$isLast && is_dir($paramPath)) {
                    $currentPath = $paramPath;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            return null;
        }
    }

    $indexFile = $currentPath . '/index.php';
    if (file_exists($indexFile)) {
        $relative = substr($indexFile, strlen($pagesDir) + 1);
        return [$relative, $paramValues];
    }

    return null;
}

function vanilla_makeRelativePath($from, $to)
{
    $from = str_replace('\\', '/', trim($from, '/'));
    $to = str_replace('\\', '/', trim($to, '/'));

    $fromParts = $from === '' ? [] : explode('/', $from);
    $toParts = $to === '' ? [] : explode('/', $to);

    // Remove filename from source path
    if (!empty($fromParts)) {
        array_pop($fromParts);
    }

    $length = min(count($fromParts), count($toParts));
    $common = 0;
    for ($i = 0; $i < $length; $i++) {
        if ($fromParts[$i] !== $toParts[$i]) {
            break;
        }
        $common++;
    }

    $up = array_fill(0, count($fromParts) - $common, '..');
    $down = array_slice($toParts, $common);

    $relativeParts = array_merge($up, $down);
    $rel = implode('/', $relativeParts);

    // If same directory/file, just return basename
    if ($rel === '') {
        return basename($to);
    }

    return $rel;
}

/**
 * Rewrite an internal URL to work with vanilla build (no .htaccess needed).
 * 
 * Handles:
 * - "/" -> "index.php"
 * - "/about" -> "about.php"
 * - "/product/123" -> "product.php?id=123"
 * - "/type/20?games=50" -> "type.php?id=20&games=50"
 * - "/api/..." -> relative path to api folder
 * - "/public/..." -> relative path to public folder
 */
function vanilla_rewriteInternalUrl($url, $pagesDir, $currentDestRelative)
{
    $trimmed = trim($url);
    
    // Skip empty, anchors, external URLs, and special protocols
    if ($trimmed === '') {
        return $url;
    }
    if ($trimmed[0] === '#') {
        return $url;
    }
    if (preg_match('~^(https?:)?//~i', $trimmed)) {
        return $url;
    }
    if (preg_match('~^(mailto:|tel:|javascript:|data:)~i', $trimmed)) {
        return $url;
    }

    $parsed = parse_url($trimmed);
    if ($parsed === false) {
        return $url;
    }

    $path = $parsed['path'] ?? '';
    $query = $parsed['query'] ?? '';
    $fragment = $parsed['fragment'] ?? '';
    
    // Handle root path "/" -> index.php
    if ($path === '' || $path === '/') {
        $newPath = vanilla_makeRelativePath($currentDestRelative, 'index.php');
        if ($query !== '') {
            $newPath .= '?' . $query;
        }
        if ($fragment !== '') {
            $newPath .= '#' . $fragment;
        }
        return $newPath;
    }

    $relativePath = ltrim($path, '/');
    
    // Handle /api/... and /public/... paths - convert to relative
    if (preg_match('~^(api|public)/~', $relativePath)) {
        $newPath = vanilla_makeRelativePath($currentDestRelative, $relativePath);
        if ($query !== '') {
            $newPath .= '?' . $query;
        }
        if ($fragment !== '') {
            $newPath .= '#' . $fragment;
        }
        return $newPath;
    }

    // Try to match route to a page file
    $route = vanilla_matchRoutePathToPage($relativePath, $pagesDir);
    if ($route === null) {
        // Not a known page route - might be a static file or external
        // If it starts with /, make it relative for portability
        if ($trimmed[0] === '/') {
            $newPath = vanilla_makeRelativePath($currentDestRelative, $relativePath);
            if ($query !== '') {
                $newPath .= '?' . $query;
            }
            if ($fragment !== '') {
                $newPath .= '#' . $fragment;
            }
            return $newPath;
        }
        return $url;
    }

    list($pageRelative, $routeParams) = $route;
    list($destRelative, $paramNames) = vanilla_mapPageToEntry($pageRelative);

    // Merge route params with query params
    $queryParams = [];
    if ($query !== '') {
        parse_str($query, $queryParams);
    }

    $merged = array_merge($routeParams, $queryParams);
    $qs = http_build_query($merged);

    // Make URL relative to the current built file so project can live under any base path
    $newPath = vanilla_makeRelativePath($currentDestRelative, $destRelative);
    if ($qs !== '') {
        $newPath .= '?' . $qs;
    }
    if ($fragment !== '') {
        $newPath .= '#' . $fragment;
    }

    return $newPath;
}

/**
 * Rewrite all internal URLs in HTML to work with vanilla build.
 * 
 * Handles: href, action, src attributes
 */
function vanilla_rewriteHtmlUrls($html, $pagesDir, $currentDestRelative)
{
    // Match href, action, and src attributes
    $pattern = '/\b(href|action|src)\s*=\s*([\'\"])([^\'\"]*)([\'\"])/i';
    $callback = function ($matches) use ($pagesDir, $currentDestRelative) {
        $attr = $matches[1];
        $quote = $matches[2];
        $url = $matches[3];
        $newUrl = vanilla_rewriteInternalUrl($url, $pagesDir, $currentDestRelative);
        return $attr . '=' . $quote . $newUrl . $quote;
    };

    return preg_replace_callback($pattern, $callback, $html);
}
