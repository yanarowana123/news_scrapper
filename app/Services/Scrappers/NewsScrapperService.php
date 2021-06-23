<?php


namespace App\Services\Scrappers;


use App\Models\News;
use Illuminate\Support\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class NewsScrapperService extends ScrapperService
{

    public function scrape(): void
    {
        $crawler = new Crawler($this->getContent());
        $crawler
            ->filter('channel item')
            ->each(function (Crawler $node) {
                $fields = $this->getFields($node);
                if ($newsItem = News::find($fields['guid'])) {
                    $newsItem->update([$fields]);
                } else {
                    News::create($fields);
                }
            });
    }

    private function hasContent(Crawler $node): bool
    {
        return $node->count() > 0;
    }

    private function getFields(Crawler $node): array
    {
        return [
            'guid' => $this->hasContent($node->filter('guid')) ? $node->filter('guid')->text() : null,
            'title' => $this->hasContent($node->filter('title')) ? $node->filter('title')->text() : null,
            'link' => $this->hasContent($node->filter('link')) ? $node->filter('link')->text() : null,
            'description' => $this->hasContent($node->filter('description')) ? $node->filter('description')->text() : null,
            'pub_date' => $this->hasContent($node->filter('pubDate')) ? Carbon::parse($node->filter('pubDate')->text())->format('Y-m-d H:i:s') : null,
            'author' => $this->hasContent($node->filter('author')) ? $node->filter('author')->text() : null,
            'image' => $this->hasContent($node->filter('enclosure[type="image/jpeg"]')) ? $node->filter('enclosure[type="image/jpeg"]')->attr('url') : null
        ];
    }
}
