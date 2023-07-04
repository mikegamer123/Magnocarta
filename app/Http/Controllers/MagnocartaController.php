<?php

namespace App\Http\Controllers;

use App\Services\ChatGPTService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;

class MagnocartaController extends BaseController
{
    private function InitGPT($input, $type = "bookSearch")
    {
        $chatGpt = new ChatGPTService();
        switch ($type){
            case "bookSearch" : return $chatGpt->searchBooks($input);
            case "bookPrep" : return $chatGpt->prepareInputSearchBooks($input);
            default : return "";
        }
    }

    public function bookSearch(Request $request)
    {
        // Create a Guzzle HTTP client
        $client = new Client(['http_errors' => false]); // we want 404 to not throw exception

        $searchInput = $request->input("input");
        //get formatted input from user input
        $outputFormat = $this->InitGPT($searchInput, "bookPrep");
        while (!$outputFormat) {
            $outputFormat = $this->InitGPT($searchInput, "bookPrep");
        } //keep trying to get response if null
        $outputFormat = array_key_exists("error", $outputFormat) ? json_decode("{\"error\": \"" . $outputFormat["error"] . "\"}", true) : json_decode($outputFormat["choices"][0]["message"]["content"], true);
        if(array_key_exists("error", $outputFormat)) return json_encode($outputFormat);
        //get book recommendations from formatted input
        $output = $this->InitGPT($outputFormat);
        while (!$output) {
            $output = $this->InitGPT($outputFormat);
        } //keep trying to get response if null

        $formatted = array_key_exists("error", $output) ? json_decode("{\"error\": \"" . $output["error"] . "\"}", true) : json_decode($output["choices"][0]["message"]["content"], true);
//dd($formatted);
        if (array_key_exists("recommendations", $formatted)) {
            for ($i = 0; $i < count($formatted["recommendations"]); $i++) {
                if ($formatted["recommendations"][$i]["bookISBN"] && $formatted["recommendations"][$i]["bookISBN"] !== "") {
                    $urlISBN = "https://pictures.abebooks.com/isbn/{$formatted["recommendations"][$i]['bookISBN']}-uk-300.jpg";
                    $response = $client->get($urlISBN);
                    // Check the status code of the response
                    if ($response->getStatusCode() === 404) {
                        //if not correct get from Google books api new isbn value from title
                        $url = 'https://www.googleapis.com/books/v1/volumes?q=intitle:"' . $formatted["recommendations"][$i]["bookName"] . '"&key=' . env('GOOGLE_API_KEY') . '&maxResults=10&printType=books&langRestrict=en';
                        $response = $client->get($url);
                        $jsonResponse = $response->getBody();
                        $data = json_decode($jsonResponse, true);
                        if($data["totalItems"] == 0) {
                            $formatted["recommendations"][$i]["bookName"] = ""; //remove the book later
                            continue;
                        }
                        $items = $data["items"];
                        $filled = false;
                        for ($j = 0; $j < count($items); $j++) {
                            $volInfo = $items[$j]["volumeInfo"] ?? [];
                            if ($filled) break;
                            if(!array_key_exists("industryIdentifiers",$volInfo)) continue;
                            foreach ($volInfo["industryIdentifiers"] as $identifier) {
                                if ($identifier["type"] == "ISBN_13") {
                                    $formatted["recommendations"][$i]['bookISBN'] = $identifier["identifier"]; // fill with new ISBN
                                    $urlISBN = "https://pictures.abebooks.com/isbn/{$formatted["recommendations"][$i]['bookISBN']}-uk-300.jpg";
                                    $response = $client->get($urlISBN); // check the new ISBN
                                    if ($response->getStatusCode() !== 404) {
                                        $filled = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else { //if we don't even have an ISBN
                    //pull isbn from Google books API
                    $url = 'https://www.googleapis.com/books/v1/volumes?q=intitle:"' . $formatted["recommendations"][$i]["bookName"] . '"&key=' . env('GOOGLE_API_KEY') . '&maxResults=10&printType=books&langRestrict=en';
                    $response = $client->get($url);
                    $jsonResponse = $response->getBody();
                    $data = json_decode($jsonResponse, true);
                    if($data["totalItems"] == 0) {
                        $formatted["recommendations"][$i]["bookName"] = ""; //remove the book later
                        continue;
                    }
                    $items = $data["items"];
                    $filled = false;
                    for ($j = 0; $j < count($items); $j++) {
                        $volInfo = $items[$j]["volumeInfo"];
                        foreach ($volInfo["industryIdentifiers"] as $identifier) {
                            if ($identifier["type"] == "ISBN_13") {
                                $formatted["recommendations"][$i]['bookISBN'] = $identifier["identifier"]; // fill with new ISBN
                                $filled = true;
                                break;
                            }
                        }
                        if ($filled) break;
                    }
                }
            }
        }
        $jsonReturned = json_encode($formatted); // encode data again because chatGPT can scramble the encoding of json for JS
        return $jsonReturned;
    }
}
