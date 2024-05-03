<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ApiNewsController extends Controller
{
    /**
     * Act with the best editor of THE NEW YORK TIMES, read these two news stories carefully and write a new article in Spanish for a Spanish audience. https://www.sensacine.com/noticias/cine/noticia-1000073876/ https://www.mundodeportivo.com/elotromundo/television/20240501/1002237942/pelicula-hoy-tv-abierto-gratis-clint-eastwood-dirige-protagoniza-clasico-magistral-thriller-accion-smd-tvh.html
* In JSON mode, {"title" : "", "content": ""}.
*Take a deep breath and work on this step by step
    */

    public function getPrompt(){
        $article = Article::whereNull('article_title')->whereNotNull('item2_url')->orderBy('created_at','desc')->take(1)->first();


         // Verifica si el artículo fue encontrado
    if ($article) {
        // Construye el prompt
        $prompt=  "Act with the best editor of THE NEW YORK TIMES, read these two news stories carefully and write a new article in Spanish for a Spanish audience.\n
         $article->item1_url\n 
         $article->item2_url 
            \n Take a deep breath and work on this step by step
            \nReply in JSON mode, {\"title\" : \"****\", \"content\": \"*****\"}.";
        // Prepara el arreglo para la respuesta JSON
        $response = [
            'id' => $article->id,  // Asume que $article tiene un 'id'
            'prompt' => $prompt
        ];

        // Devuelve el JSON
        return response()->json($response);
    } else {
        // En caso de que no se encuentre un artículo, devuelve un mensaje de error
        return response()->json(['error' => 'No articles found'], 404);
    }
    }

    public function saveArticle(Request $request){

        $article = Article::find($request->input('id'));
        $article->article_title = $request->input('title');
        $article->article_description = $request->input('content');
        $article->save();
        return var_dump($article->title);
    }
}
