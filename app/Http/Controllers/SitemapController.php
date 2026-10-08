<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /cek-pesanan',
            'Disallow: /pesan',
            'Allow: /',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('produk.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('galeri'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('tentang'), 'priority' => '0.5'],
            ['loc' => route('layanan'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('lokasi'), 'priority' => '0.6'],
            ['loc' => route('faq'), 'priority' => '0.6'],
            ['loc' => route('size-chart'), 'priority' => '0.5'],
            ['loc' => route('track.create'), 'priority' => '0.4'],
            ['loc' => route('kebijakan-privasi'), 'priority' => '0.2'],
            ['loc' => route('syarat-ketentuan'), 'priority' => '0.2'],
        ];

        foreach (Category::select('slug', 'updated_at')->orderBy('name')->get() as $category) {
            $urls[] = [
                'loc' => route('kategori', $category->slug),
                'priority' => '0.8',
                'changefreq' => 'daily',
                'lastmod' => $category->updated_at?->toDateString(),
            ];
        }

        foreach (Product::select('slug', 'updated_at', 'image')->latest()->orderByDesc('id')->get() as $product) {
            $urls[] = [
                'loc' => route('produk.show', $product->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $product->updated_at?->toDateString(),
                'image' => $product->image ? asset('storage/'.$product->image) : null,
            ];
        }

        $items = collect($urls)->map(function (array $url) {
            $priority = isset($url['priority']) ? "\n    <priority>{$url['priority']}</priority>" : '';
            $changefreq = isset($url['changefreq']) ? "\n    <changefreq>{$url['changefreq']}</changefreq>" : '';
            $lastmod = ! empty($url['lastmod']) ? "\n    <lastmod>{$url['lastmod']}</lastmod>" : '';
            $image = ! empty($url['image'])
                ? "\n    <image:image>\n      <image:loc>".e($url['image'])."</image:loc>\n    </image:image>"
                : '';

            return "  <url>\n    <loc>".e($url['loc'])."</loc>{$lastmod}{$changefreq}{$priority}{$image}\n  </url>";
        })->implode("\n");

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n"
            .$items."\n"
            .'</urlset>'."\n";

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
