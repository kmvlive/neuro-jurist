<?php

namespace App\Console\Commands;

use App\Models\QuickPrompt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Генерирует sitemap.xml для SEO-страниц';

    public function handle()
    {
        $baseUrl = config('app.url');
        $urls = [];

        // Главная
        $urls[] = [
            'loc' => $baseUrl,
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        // Статические страницы
        $pages = [
            '/pricing' => ['changefreq' => 'monthly', 'priority' => '0.8'],
            '/consult' => ['changefreq' => 'weekly', 'priority' => '0.9'],
            '/prompts' => ['changefreq' => 'weekly', 'priority' => '0.8'],
        ];

        foreach ($pages as $path => $meta) {
            $urls[] = array_merge(['loc' => $baseUrl . $path], $meta);
        }

        // SEO-лендинги /consult/{key}
        $prompts = QuickPrompt::where('active', true)->whereNotNull('seo_title')->get();
        foreach ($prompts as $prompt) {
            $urls[] = [
                'loc' => $baseUrl . '/consult/' . $prompt->key,
                'lastmod' => $prompt->updated_at->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // Генерируем XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;

        // Сохраняем в public/sitemap.xml
        File::put(public_path('sitemap.xml'), $xml);

        $this->info('✅ Sitemap сгенерирован: ' . count($urls) . ' URL');
        $this->info('   Файл: public/sitemap.xml');

        return 0;
    }
}
