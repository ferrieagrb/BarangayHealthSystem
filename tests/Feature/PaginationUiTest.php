<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Tests\TestCase;

class PaginationUiTest extends TestCase
{
    public function test_numbered_pagination_preserves_filters_tab_and_named_page_parameter(): void
    {
        $paginator = new LengthAwarePaginator(range(201, 220), 411, 20, 11, [
            'path' => '/logs', 'pageName' => 'citizen_page',
        ]);
        $paginator->appends(['tab' => 'citizens', 'search' => 'test resident', 'supply_page' => 3]);
        $html = $paginator->links()->toHtml();
        $this->assertStringContainsString('Showing 201 to 220 of 411 results', $html);
        $dom = new \DOMDocument(); @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//*[@aria-current="page"]')->length);
        $this->assertSame('11', $xpath->query('//*[@aria-current="page"]')->item(0)->textContent);
        foreach ($xpath->query('//a') as $link) {
            parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
            $this->assertSame('citizens', $query['tab']);
            $this->assertSame('test resident', $query['search']);
            $this->assertSame('3', $query['supply_page']);
            $this->assertArrayHasKey('citizen_page', $query);
            $this->assertArrayNotHasKey('page', $query);
        }
    }

    public function test_first_last_and_single_page_states(): void
    {
        $first = new LengthAwarePaginator(range(1, 20), 411, 20, 1);
        $last = new LengthAwarePaginator(range(401, 411), 411, 20, 21);
        $single = new LengthAwarePaginator([1], 1, 20, 1);
        $this->assertStringContainsString('aria-disabled="true" aria-label="Previous page"', $first->links()->toHtml());
        $this->assertStringContainsString('aria-disabled="true" aria-label="Next page"', $last->links()->toHtml());
        $this->assertSame('', trim($single->links()->toHtml()));
    }

    public function test_simple_paginator_and_registry_compatibility_view(): void
    {
        $simple = new Paginator(range(1, 21), 20, 1, ['path' => '/records']);
        $html = $simple->links()->toHtml();
        $this->assertStringContainsString('Page 1', $html);
        $this->assertStringContainsString('href="/records?page=2"', $html);
        $registry = new LengthAwarePaginator(range(1, 20), 411, 20, 1);
        $this->assertStringContainsString('bheams-pagination', $registry->links('partials.citizen-pagination')->toHtml());
    }

    public function test_out_of_range_page_does_not_generate_invalid_numbered_links(): void
    {
        $paginator = new LengthAwarePaginator([], 411, 20, 999999999);
        $html = $paginator->links()->toHtml();
        $this->assertStringContainsString('Showing 0 to 0 of 411 results', $html);
        $this->assertLessThan(10000, strlen($html));
    }
}
