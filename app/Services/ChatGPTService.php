<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class ChatGPTService
{
    function searchBooks ($input){
        $data = [];
        try {
            $prompt = "
Return the answer as a JSON object.
 You are Magnocarta, an AI that generates book recommendations for a website and returning that information in an object oriented json.

The properties of the book recommendations:
Category : ".$input["categoryOfBook"].",
Book Audience is : ".$input["audience"].",
Age group of the book : ".$input["ageGroup"].",
The first edition of the book must be published : ".$input["datePublished"].",
Publication of the book should be : ".($input["publication"] == "" ? "British" : $input["publication"])."
Author of the book must not be :  ".($input["author"] == "" ? "No restriction" : $input["author"])."
The series must not be : ".($input["series"] == "" ? "No restriction" : $input["series"])."
Books must be popular and new and they have to exist.
Only return one book not an entire series.
Return 1 - 4 recommendations.
Retrieve recommendations with the above mentioned properties.
If a book is newer and you do not have info about it return the JSON Format {error: \"unknown\"}.
                All results must have all the data filled in the json values.
                The prompt must be compatible to return the book data and must be appropriate, if it is not, return just JSON Format {error: \"false\"}.
                If it is an inappropriate prompt, return just in JSON Format {error: \"false\"}.
                Return the data in the format of { recommendations [{bookName, bookDescription, bookISBN, datePublished}, {bookName, bookDescription, bookISBN, datePublished}, ...] }.
";
            $retry = true;
            while ($retry) {
                $data = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
                ])
                    ->post("https://api.openai.com/v1/chat/completions", [
                        "model" => "gpt-3.5-turbo",
                        'messages' => [
                            [
                                "role" => "user",
                                "content" => $prompt
                            ]
                        ],
                        'temperature' => 0.7,
                        "max_tokens" => 1000,
                        "top_p" => 1.0,
                        "frequency_penalty" => 0.5,
                        "presence_penalty" => 0.5
                    ])
                    ->json();

                // Check if an error occurred
                if (isset($data['error'])) {
                    if ($data['error'] === "false") {
                        // Handle inappropriate prompt or error
                        $retry = false;
                    } else {
                        throw new Exception("An error occurred: " . $data['error']);
                    }
                } else {
                    $retry = false;
                }
            }
        } catch (Exception $e) {
            $data['error'] = "error: " . $e->getMessage();
        }
        return $data;
    }

    function prepareInputSearchBooks ($input){
        $data = [];
        try {
            $prompt = "
Return answer as a json response.
If the question is inappropriate or not valid return json format {error:\"false\"}.
For a question like this \"Give me books like harry potter (published )after the year 2015\"
Take the keywords that you can guess from the question for example :
categoryOfBook = \"Fantasy\", audience = \"Children\", ageGroup = \"10 - 15 years\", datePublished: \"the year 2015 and above\", author = \"J.K.Rowling\", series = \"Harry Potter\", publication : \"british\"
Get the keywords from this question  (fill in with a guess what is missing from question) below in the format  provided:
{categoryOfBook:\"\", audience:\"\", ageGroup:\"\", datePublished : \"\" (default \"the year 2011 and above\"),  author: \"\",  series:\"\" ,publication: \"\" (default \"British\")}

The question is \"$input\" ";

            $retry = true;
            while ($retry) {
                $data = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
                ])
                    ->post("https://api.openai.com/v1/chat/completions", [
                        "model" => "gpt-3.5-turbo",
                        'messages' => [
                            [
                                "role" => "user",
                                "content" => $prompt
                            ]
                        ],
                        'temperature' => 0.7,
                        "max_tokens" => 1000,
                        "top_p" => 1.0,
                        "frequency_penalty" => 0.5,
                        "presence_penalty" => 0.5
                    ])
                    ->json();

                // Check if an error occurred
                if (isset($data['error'])) {
                    if ($data['error'] === "false") {
                        // Handle inappropriate prompt or error
                        $retry = false;
                    } else {
                        throw new Exception("An error occurred: " . $data['error']);
                    }
                } else {
                    $retry = false;
                }
            }
        } catch (Exception $e) {
            $data['error'] = "error: " . $e->getMessage();
        }
        return $data;
    }
}
