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

        //$this->getImageFromUrl($article->item1_url);
        //$this->getImageFromUrl($article->item2_url);
         // Verifica si el artículo fue encontrado
    if ($article) {
        // Construye el prompt
        $prompt=  "Act with the best editor of THE NEW YORK TIMES, read these two news stories carefully and write a new article in Spanish for a Spanish audience.\n
         $article->item1_url\n 
         $article->item2_url 
            \n Take a deep breath and work on this step by step
            \nReply in JSON mode, {\"title\" : \"****\", \"content\": \"*****\"}.
            \nEscape double quotes \" within the content of the title or content.";
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
        $mediaId = $this->uploadImage($article->item1_url);
        $this->publica($article,$mediaId);
        return $this->getJWT();
    }
    public function publica($article, $mediaId = 335){
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
                    'status' => 'publish',
                    'featured_media' => $mediaId  // Asegúrate de que $mediaId es el ID del medio que quieres como imagen destacada
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
        //print_r($token);
        return $token;  // Codifica y retorna el token
    }

    public function getImageFromUrl($url){
        $client = new Client();
        $response = $client->request('GET', $url);
        $html = $response->getBody();
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true); // Desactiva errores/libxml errors (por ejemplo, HTML mal formado)
        $doc->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new \DOMXPath($doc);
        $metaTags = $xpath->query("//meta[@property='og:image']");
        $imageUrl = '';
        if ($metaTags->length > 0) {
            $imageUrl = $metaTags->item(0)->getAttribute('content');
            //echo "La URL de la imagen es: " . $imageUrl;
        } else {
            echo "No se encontró la etiqueta meta og:image.";
        }
        return $imageUrl;
    }

    public function uploadImage($url = 'https://www.sensacine.com/noticias/cine/noticia-1000073876/'){
        
        $base_uri = env("WP_BASE_URL").'/jwt-auth/v1/token';
        $client = new Client();
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
        }

        $imageUrl = $this->getImageFromUrl($url);

        $contents = file_get_contents($imageUrl);

    // Extraemos el nombre del archivo desde la URL, eliminando cualquier parámetro de consulta
    $pathInfo = pathinfo(parse_url($imageUrl, PHP_URL_PATH));
    $filename = $pathInfo['basename'];  // Obtiene solo el nombre del archivo, sin parámetros de consulta

    // Sanitiza el nombre del archivo para eliminar caracteres no deseados
    $sanitizedFilename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);

    // Guarda el contenido en un archivo local
    if (file_put_contents($sanitizedFilename, $contents) === false) {
        return response()->json(['error' => 'Failed to save the file.'], 500);
    }
    try {
        $client = new Client();
        $base_uri = env("WP_BASE_URL").'/wp/v2/media';
        $response = $client->request('POST', $base_uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Asume que tienes un token JWT válido

            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($sanitizedFilename, 'r'),
                    'filename' => $sanitizedFilename,
                ],
            ],
            'http_errors' => false
        ]);
        $body = json_decode($response->getBody(), true);
        $imageId = $body['id'];
        return $imageId;
        if ($response->getStatusCode() == 201 || $response->getStatusCode() == 200){
            return "Archivo subido con éxito. ID del medio: " . var_dump($response);
        } else {
            echo "Error al subir archivo. Estado HTTP: " . $response->getStatusCode() . "\n" . $response->getBody();
        }
    } catch (RequestException $e) {
        echo "Error al realizar la solicitud: " . $e->getMessage();
    }
    // Devuelve el nombre del archivo para confirmar la operación
    return response()->json(['filename' => $sanitizedFilename]);

       
    }
}
