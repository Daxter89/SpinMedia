<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

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
        $this->publica($article);
        return $this->getJWT();
    }
    public function publica($article){
        $client = new Client();

        $base_uri = env("WP_BASE_URL").'/jwt-auth/v1/token';

        $response = $client->request('POST', $base_uri, [
            'headers' => [
            ],
            'json' => [
                'username' => env('WP_USER'),
                'password' => env('WP_PASS'),
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            $response = json_decode($response->getBody());
            $token = $response->token;
            $client = new Client();
            $base_uri = env("WP_BASE_URL").'/wp/v2/posts';
            $auth = 'Bearer ' . $token;
    
            try {
            $response = $client->request('POST', $base_uri , [
                'headers' => [
                    'Authorization' => $auth,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'title' => $article->article_title,
                    'content' => $article->article_description,
                    'status' => 'publish'
                ]
            ]);
            if ($response->getStatusCode() == 201) {
                $article->is_published = true;
                $article->save();
                // Podrías retornar algo aquí, por ejemplo, un mensaje de éxito o la respuesta.
            } else {
                // Manejar situaciones donde la API no devuelve un estado 201 (creado)
                return "Error al publicar el artículo, código de estado: " . $response->getStatusCode();
            }
        } catch (RequestException $e) {
            // Manejar excepciones de la solicitud, como errores de conexión o HTTP
            return "Error al publicar el artículo: " . $e->getMessage();
        }
            // Podrías retornar algo aquí, por ejemplo, un mensaje de éxito o la respuesta.
        } else {
            // Manejar situaciones donde la API no devuelve un estado 201 (creado)
            return "Error al publicar el artículo, código de estado: " . $response->getStatusCode();
        }

       
    
    }

    function getJWT() {
        $key = env("JWT_KEY");  // La clave secreta, debe ser la misma en wp-config.php
        $issuedAt = time();  // Tiempo actual en segundos desde la época Unix
        $expirationTime = $issuedAt + 3600;  // Token expira en 1 hora
        $serverName = 'https://oriolmiro.com';  // Asegúrate de que corresponda al dominio de tu sitio de WordPress
    
        $payload = [
            'iss' => $serverName,  // Emisor
            'iat' => $issuedAt,  // Tiempo de emisión
            'exp' => $expirationTime,  // Tiempo de expiración
            'user_id' => 2,  // ID del usuario de WordPress
        ];
        $token = JWT::encode($payload, $key, 'HS256');  // Codifica y retorna el token
        print_r($token);
        return $token;  // Codifica y retorna el token
    }
}
