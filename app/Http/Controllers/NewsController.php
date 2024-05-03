<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleXMLElement;
use App\Models\Article;

class NewsController extends Controller
{
    public function __invoke()
    {
        $source = "https://trends.google.com/trends/trendingsearches/daily/rss?geo=ES"; 


        $contents = simplexml_load_file($source);
        $contents->registerXPathNamespace('ht', 'https://trends.google.com/trends/trendingsearches/daily');
        $namespace = $contents->getNamespaces(true)['ht']; // Asumiendo que 'ht' es un prefijo de namespace definido
         foreach ($contents->channel->item as $item) {
            $ht = $item->children($namespace);
            //return var_dump($ht);
            Article::updateOrCreate([
                'title' => $item->title,
                'pubDate' => $item->pubDate],
                ['link' => $item->link,
                'description' => $item->description,
                'picture' => $ht->picture,
                'item1_url' => $ht->news_item[0]->news_item_url ?? null,  
                'item1_title' => $ht->news_item[0]->news_item_title ?? null, 
                'item1_source' => $ht->news_item[0]->news_item_source ?? null,  
                'item2_url' => $ht->news_item[1]->news_item_url ?? null, 
                'item2_title' => $ht->news_item[1]->news_item_title ?? null,
                'item2_source' => $ht->news_item[1]->news_item_source ?? null,
                ]);

         }


    }
}
