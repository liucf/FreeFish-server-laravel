<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Rootcategory;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerController extends Controller
{
    public function category()
    {
        $url = 'https://www.trademe.co.nz/browse';
        // $browsershot = new \Spatie\Browsershot\Browsershot();
        // $browsershot->setNodeBinary('/Users/michael/.nvm/versions/node/v18.16.1/bin/node');
        // $browsershot->setNpmBinary('/Users/michael/.nvm/versions/node/v18.16.1/bin/npm');
        // Crawler::create()
        //     ->setBrowsershot($browsershot)
        //     ->ignoreRobots()
        //     ->executeJavaScript()
        //     ->setCrawlObserver(new CategoryCrawlObserver)
        //     ->startCrawling($url);
        
        // $browsershot = new \Spatie\Browsershot\Browsershot();
        // $browsershot->setNodeBinary('/Users/michael/.nvm/versions/node/v18.16.1/bin/node');
        // $browsershot->setNpmBinary('/Users/michael/.nvm/versions/node/v18.16.1/bin/npm');
        // $html = $browsershot->url($url)->bodyHtml();

        $html = Browsershot::url("https://www.trademe.co.nz/browse")->timeout(1200000)->newHeadless()->bodyHtml();
        $crawler = new Crawler($html);

        $table = $crawler->filter('#fullCat');
        $table->filter('div.category')->each(function (Crawler $node, $i) {
            $categoryname = $node->filter('h3')->text();
           
            if($categoryname != "Computers"){
                return false;
            }

            logger($categoryname);

            $root = Rootcategory::updateOrCreate(
                ['name' => $categoryname]
            );
            $node->filter('li')->each(function (Crawler $node2, $i) use ($root) {
                $subcategoryname = $node2->filter('a')->text();
                $subcategoryurl = "https://www.trademe.co.nz/" . $node2->filter('a')->attr('href');
                logger($subcategoryname);
                logger($subcategoryurl);

                $subcategory = Subcategory::updateOrCreate(
                    ['name' => $subcategoryname, 'rootcategory_id' => $root->id],
                );

                $thirdCategoryExist = false;
                $html2 = Browsershot::url($subcategoryurl)->timeout(1200000)->newHeadless()->bodyHtml();
                $crawler2 = new Crawler($html2);
                $crawler2->filter('.tm-category-suggestions__list-item')->each(function (Crawler $node3, $i) use ($root, $subcategory) {
                    $categoryname3 = $node3->filter('a')->text();
                    $categoryurl3 = "https://www.trademe.co.nz" . $node3->filter('a')->attr('href');
                    logger($categoryname3);
                    preg_match('/(.*)(\((\d*,?\d+)\))/', $categoryname3, $matches);
                    // logger($matches[0]);
                    // logger($matches[1]);
                    // logger($matches[2]);
                    // logger($matches[3]);
                    $category = Category::updateOrCreate(
                        ['name' => $matches[1], 'subcategory_id' => $subcategory->id],
                    );
                    $thirdCategoryExist = true;
                    // download one product
                    logger($categoryurl3);
                    $html3 = Browsershot::url($categoryurl3)->timeout(1200000)->newHeadless()->bodyHtml();
                    $crawler3 = new Crawler($html3);
                    $node = $crawler3->filter('.tm-marketplace-search-card__wrapper');

                    logger("download one product from category");
                    try {
                        $productname = $node->filter('#-title')->text();
                    } catch (\Throwable $th) {
                        try {
                        $productname = $node->filter('.tm-marketplace-search-card__title')->text();
                        }
                        catch (\Throwable $th) {
                            $productname = "none";
                        }
                    }

                    if( Product::where('name', $productname)->exists()){
                        return false;
                    }

                    // logger($productname->count());
                    // if($productname->count() == 0){
                    //     $productname = $node->filter('.tm-marketplace-search-card__title')->text();
                    // }else{
                    //     $productname = $productname->text();
                    // }
                    try {
                        $productPrice = $node->filter('.tm-marketplace-search-card__price')->text();
                    } catch (\Throwable $th) {
                        return false;
                    }
                    $producturl =  "https://www.trademe.co.nz/a/" . $node->filter('.tm-marketplace-search-card__detail-section--link')->attr('href');
                    logger($productname);
                    logger($producturl);
                    logger($productPrice);

                    $html4 = Browsershot::url($producturl)->timeout(1200000)->newHeadless()->bodyHtml();
                    $crawler4 = new Crawler($html4);
                    $productDesc = $crawler4->filter('.tm-markdown')->text();
                    logger($productDesc);

                    $productPrice = str_replace(',', '', $productPrice);
                    $productPrice = str_replace('$', '', $productPrice);
                    $productPrice = str_replace(' ', '', $productPrice);
                    $productPrice = (float)$productPrice;
                    logger($productDesc);

                    $product = Product::updateOrCreate(
                        [
                            'user_id' => 1,
                            'name' => $productname, 
                            'rootcategory_id' => $root->id,
                            'subcategory_id' => $subcategory->id,
                            'category_id' => $category->id, 
                            'price' => (float)$productPrice, 
                            'description' => $productDesc,
                            'originalPrice' => $productPrice * 0.5
                        ],
                    );

                    // download the pictures
                    $productImg = $crawler4->filter('.tm-marketplace-listing-photos__aspect-ratio');
                    if($productImg->count() > 0){
                        $imgWrap = $productImg->attr('style');
                        preg_match('/.*url\("((.*\/)(.*))"\);/', $imgWrap, $matches);
                        $imgUrl = $matches[1]; 
                        $imgName = $matches[3];
                        logger($imgUrl);
                        logger($imgName);
                        $img = file_get_contents($imgUrl);
                        Storage::disk('public')->put('product/' . $imgName, $img);
                        $product->thumbs()->create([
                            'name' => $imgName
                        ]);
                    }
                
                });
                  
                if(!$thirdCategoryExist) {
                    // downlaod one product from subcategory
                    logger("download one product from subcategory");

                    try {
                        $productname = $crawler2->filter('#-title')->text();
                    } catch (\Throwable $th) {
                        try {
                        $productname = $crawler2->filter('.tm-marketplace-search-card__title')->text();

                        }
                        catch (\Throwable $th) {
                            $productname = "none";
                        }
                    }

                    if( Product::where('name', $productname)->exists()){
                        return false;
                    }

                    // logger($productname->count());
                    // if($productname->count() == 0){
                    //     $productname = $crawler2->filter('.tm-marketplace-search-card__title')->text();
                    // }else{
                    //     $productname = $productname->text();
                    // }
                    $productPrice = $crawler2->filter('.tm-marketplace-search-card__price')->text();
                    $producturl =  "https://www.trademe.co.nz/a/" . $crawler2->filter('.tm-marketplace-search-card__detail-section--link')->attr('href');
                    logger($productname);
                    logger($producturl);
                    logger($productPrice);

                    $html4 = Browsershot::url($producturl)->timeout(1200000)->newHeadless()->bodyHtml();
                    $crawler4 = new Crawler($html4);
                    $productDesc = $crawler4->filter('.tm-markdown')->text();
                    logger($productDesc);

                    $productPrice = str_replace(',', '', $productPrice);
                    $productPrice = str_replace('$', '', $productPrice);
                    $productPrice = str_replace(' ', '', $productPrice);
                    $productPrice = (float)$productPrice;
                    logger($productDesc);

                    $product = Product::updateOrCreate(
                        [
                            'user_id' => 1,
                            'name' => $productname, 
                            'rootcategory_id' => $root->id,
                            'subcategory_id' => $subcategory->id,
                            'price' => (float)$productPrice, 
                            'description' => $productDesc,
                            'originalPrice' => $productPrice * 0.5
                        ],
                    );

                    // download the pictures
                    $productImg = $crawler4->filter('.tm-marketplace-listing-photos__aspect-ratio');
                    if($productImg->count() > 0){
                    $imgWrap = $productImg->attr('style');
                        preg_match('/.*url\("((.*\/)(.*))"\);/', $imgWrap, $matches);
                        $imgUrl = $matches[1]; 
                        $imgName = $matches[3];
                        logger($imgUrl);
                        logger($imgName);
                        $img = file_get_contents($imgUrl);
                        Storage::disk('public')->put('product/' . $imgName, $img);
                        $product->thumbs()->create([
                            'name' => $imgName
                        ]);
                    }
                }
            });
        });
    }

    public function computer()
    {
        $url = 'https://www.trademe.co.nz/browse';
        
        $html = Browsershot::url("https://www.trademe.co.nz/browse")->timeout(1200000)->newHeadless()->bodyHtml();
        $crawler = new Crawler($html);

        $table = $crawler->filter('#fullCat');
        $table->filter('div.category')->each(function (Crawler $node, $i) {
            $categoryname = $node->filter('h3')->text();
           
            if($categoryname != "Computers"){
                return false;
            }

            logger($categoryname);
            
            $root = Rootcategory::updateOrCreate(
                ['name' => $categoryname]
            );
            $node->filter('li')->each(function (Crawler $node2, $i) use ($root) {
                $subcategoryname = $node2->filter('a')->text();
                $subcategoryurl = "https://www.trademe.co.nz/" . $node2->filter('a')->attr('href');
                logger($subcategoryname);
                logger($subcategoryurl);

                if($subcategoryname!="Monitors"){
                    return false;
                }

                $subcategory = Subcategory::updateOrCreate(
                    ['name' => $subcategoryname, 'rootcategory_id' => $root->id],
                );

                $thirdCategoryExist = false;
                $html2 = Browsershot::url($subcategoryurl)->timeout(1200000)->newHeadless()->bodyHtml();
                $crawler2 = new Crawler($html2);
                $crawler2->filter('.tm-category-suggestions__list-item')->each(function (Crawler $node3, $i) use ($root, $subcategory) {
                    $categoryname3 = $node3->filter('a')->text();
                    $categoryurl3 = "https://www.trademe.co.nz" . $node3->filter('a')->attr('href');
                    logger($categoryname3);
                    preg_match('/(.*)(\((\d*,?\d+)\))/', $categoryname3, $matches);
                    // logger($matches[0]);
                    // logger($matches[1]);
                    // logger($matches[2]);
                    // logger($matches[3]);
                    $category = Category::updateOrCreate(
                        ['name' => $matches[1], 'subcategory_id' => $subcategory->id],
                    );
                    $thirdCategoryExist = true;
                    // download one product
                    logger($categoryurl3);
                    $html3 = Browsershot::url($categoryurl3)->timeout(1200000)->newHeadless()->bodyHtml();
                    $crawler3 = new Crawler($html3);
                    $crawler3->filter('.tm-marketplace-search-card__wrapper')->each(function (Crawler $node4, $i) use ($root, $subcategory, $category) {
                        logger("download one product from category");
                        try {
                            $productname = $node4->filter('#-title')->text();
                        } catch (\Throwable $th) {
                            try {
                            $productname = $node4->filter('.tm-marketplace-search-card__title')->text();
                            }
                            catch (\Throwable $th) {
                                $productname = "none";
                            }
                        }
    
                        if( Product::where('name', $productname)->exists()){
                            return false;
                        }
    
                       
                        try {
                            $productPrice = $node4->filter('.tm-marketplace-search-card__price')->text();
                        } catch (\Throwable $th) {
                            return false;
                        }
                        $producturl =  "https://www.trademe.co.nz/a/" . $node4->filter('.tm-marketplace-search-card__detail-section--link')->attr('href');
                        logger($productname);
                        logger($producturl);
                        logger($productPrice);
    
                        $html5 = Browsershot::url($producturl)->timeout(1200000)->newHeadless()->bodyHtml();
                        $crawler5 = new Crawler($html5);
                        $productDesc = $crawler5->filter('.tm-markdown')->text();
                        logger($productDesc);
    
                        $productPrice = str_replace(',', '', $productPrice);
                        $productPrice = str_replace('$', '', $productPrice);
                        $productPrice = str_replace(' ', '', $productPrice);
                        $productPrice = (float)$productPrice;
                        logger($productDesc);
    
                        $product = Product::updateOrCreate(
                            [
                                'user_id' => 1,
                                'name' => $productname, 
                                'rootcategory_id' => $root->id,
                                'subcategory_id' => $subcategory->id,
                                'category_id' => $category->id, 
                                'price' => (float)$productPrice, 
                                'description' => $productDesc,
                                'originalPrice' => $productPrice * 0.5
                            ],
                        );
    
                        // download the pictures
                        $productImg = $crawler5->filter('.tm-marketplace-listing-photos__aspect-ratio');
                        if($productImg->count() > 0){
                            $imgWrap = $productImg->attr('style');
                            preg_match('/.*url\("((.*\/)(.*))"\);/', $imgWrap, $matches);
                            $imgUrl = $matches[1]; 
                            $imgName = $matches[3];
                            logger($imgUrl);
                            logger($imgName);
                            $img = file_get_contents($imgUrl);
                            Storage::disk('public')->put('product/' . $imgName, $img);
                            $product->thumbs()->create([
                                'name' => $imgName
                            ]);
                        }
                    
                    });
                   
                });
                  
                if(!$thirdCategoryExist) {
                    // downlaod one product from subcategory
                    logger("download one product from subcategory");
                    return false;
                    try {
                        $productname = $crawler2->filter('#-title')->text();
                    } catch (\Throwable $th) {
                        try {
                        $productname = $crawler2->filter('.tm-marketplace-search-card__title')->text();

                        }
                        catch (\Throwable $th) {
                            $productname = "none";
                        }
                    }

                    if( Product::where('name', $productname)->exists()){
                        return false;
                    }

                    // logger($productname->count());
                    // if($productname->count() == 0){
                    //     $productname = $crawler2->filter('.tm-marketplace-search-card__title')->text();
                    // }else{
                    //     $productname = $productname->text();
                    // }
                    $productPrice = $crawler2->filter('.tm-marketplace-search-card__price')->text();
                    $producturl =  "https://www.trademe.co.nz/a/" . $crawler2->filter('.tm-marketplace-search-card__detail-section--link')->attr('href');
                    logger($productname);
                    logger($producturl);
                    logger($productPrice);

                    $html4 = Browsershot::url($producturl)->timeout(1200000)->newHeadless()->bodyHtml();
                    $crawler4 = new Crawler($html4);
                    $productDesc = $crawler4->filter('.tm-markdown')->text();
                    logger($productDesc);

                    $productPrice = str_replace(',', '', $productPrice);
                    $productPrice = str_replace('$', '', $productPrice);
                    $productPrice = str_replace(' ', '', $productPrice);
                    $productPrice = (float)$productPrice;
                    logger($productDesc);

                    $product = Product::updateOrCreate(
                        [
                            'user_id' => 1,
                            'name' => $productname, 
                            'rootcategory_id' => $root->id,
                            'subcategory_id' => $subcategory->id,
                            'price' => (float)$productPrice, 
                            'description' => $productDesc,
                            'originalPrice' => $productPrice * 0.5
                        ],
                    );

                    // download the pictures
                    $productImg = $crawler4->filter('.tm-marketplace-listing-photos__aspect-ratio');
                    if($productImg->count() > 0){
                    $imgWrap = $productImg->attr('style');
                        preg_match('/.*url\("((.*\/)(.*))"\);/', $imgWrap, $matches);
                        $imgUrl = $matches[1]; 
                        $imgName = $matches[3];
                        logger($imgUrl);
                        logger($imgName);
                        $img = file_get_contents($imgUrl);
                        Storage::disk('public')->put('product/' . $imgName, $img);
                        $product->thumbs()->create([
                            'name' => $imgName
                        ]);
                    }
                }
            });
        });
    }
}
