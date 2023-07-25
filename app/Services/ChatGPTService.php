<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
class ChatGPTService
{
    //categories for context of category type to use in further search
    public $mainCategories = array(
        "Children's / Teenage Fiction & True Stories" => array(
            "General Fiction (Children's/Teenage)",
            "Adventure Stories (Children's/Teenage)",
            "Animal Stories (Children's/Teenage)",
            "Fantasy & Magical Realism (Children's/Teenage)",
            "Historical Fiction (Children's/Teenage)",
            "Horror & Ghost Stories, Chillers (Children's/Teenage)",
            "Mystery & Crime Fiction (Children's/Teenage)",
            "Romance & Relationship Stories (Children's/Teenage)",
            "Science Fiction (Children's/Teenage)",
            "Sports & Outdoor Recreation (Children's/Teenage)",
            "Thrillers (Children's/Teenage)",
            "War & Armed Forces (Children's/Teenage)",
            "Witches & Ghosts (Children's/Teenage)",
            "Fantasy Romance (Teenage)"
        ),
        "Picture Books & Storybooks" => array(
            "Picture Storybooks",
            "Picture Books, Activity Books & Early Learning Material"
        ),
        "Educational & Learning" => array(
            "Educational: English Language & Literacy",
            "Educational: Mathematics & Numeracy",
            "Educational: Sciences, General Science",
            "Educational: History",
            "Educational: Geography",
            "Educational: Languages Other Than English",
            "Educational: Biology",
            "Educational: Physics",
            "Educational: Music",
            "Educational: Art & Design",
            "Educational: IT & Computing, ICT",
            "Educational: Business Studies & Economics",
            "Educational: Psychology",
            "Educational: Religious Studies (Christianity, Islam, Hinduism, Buddhism)",
            "Educational: Citizenship & Social Education",
            "Educational: Social Sciences",
            "Educational: Drama Studies",
            "Educational: Physical Education (Including Dance)",
            "Educational: Design & Technology",
            "Educational: Food Technology",
            "Educational: Vocational Subjects"
        ),
        "Early Learning & Activity Books" => array(
            "Interactive & Activity Books & Packs",
            "Colouring & Painting Activity Books",
            "Sticker & Stamp Books",
            "Puzzle Books (Children's/Teenage)",
            "Handicrafts (Children's/Teenage)",
            "Early Learning: ABC Books / Alphabet Books",
            "Early Learning: Numbers & Counting",
            "Early Learning: Verse & Rhymes",
            "Early Learning: Colors",
            "Early Learning: First Word Books",
            "Early Learning: Opposites",
            "Early Learning: Size, Shapes & Patterns",
            "Early Learning: Time & Seasons",
            "Early Learning: Telling the Time",
            "Early Learning: The Senses",
            "Early Learning: Things That Go",
            "Early Learning: People Who Help Us",
            "Early Learning: First Experiences"
        ),
        "Animals & Wildlife" => array(
            "Wildlife (Children's/Teenage)",
            "Pets (Children's/Teenage)",
            "Farm Animals (Children's/Teenage)",
            "Dinosaurs & Prehistoric World (Children's/Teenage)",
            "Cats as Pets"
        ),
        "General Non-Fiction" => array(
            "Children's / Teenage: General Non-Fiction",
            "Educational Material",
            "Reference Material (Children's/Teenage)",
            "Anthologies (Children's/Teenage)",
            "Annuals (Children's/Teenage)",
            "True Stories (Children's/Teenage)",
            "Bibles & Bible Stories (Children's/Teenage)",
            "Religion & Beliefs: General Interest (Children's/Teenage)",
            "History & the Past: General Interest (Children's/Teenage)",
            "Social Issues: Environment & Green Issues (Children's/Teenage)",
            "Social Issues (Children's/Teenage)",
            "People & Places (Children's/Teenage)"
        ),
        "Health & Personal Development" => array(
            "Personal & Social Issues: Body & Health (Children's/Teenage)",
            "Personal & Social Issues: Self-awareness & Self-esteem (Children's/Teenage)",
            "Personal & Social Issues: Disability & Special Needs (Children's/Teenage)",
            "Personal & Social Issues: Family Issues (Children's/Teenage)",
            "Personal & Social Issues: Sexuality & Relationships (Children's/Teenage)",
            "Personal & Social Issues: Racism & Multiculturalism (Children's/Teenage)",
            "Personal & Social Issues: Bullying, Violence & Abuse (Children's/Teenage)",
            "Personal & Social Issues: Divorce, Separation, Family Break-up (Children's/Teenage)",
            "Coping with Death & Bereavement"
        ),
        "Poetry & Literature" => array(
            "Poetry (Children's/Teenage)",
            "Fiction & Related Items",
            "Myth & Legend Told as Fiction",
            "Playscripts (Children's/Teenage)",
            "Shakespeare Plays",
            "Fiction & Related Items (Modern & Contemporary Fiction)"
        ),
        "Educational: English Language & Reading" => array(
            "Educational: English Language: Readers & Reading Schemes",
            "Educational: English Language & Literacy: Reading & Writing Skills",
            "Educational: English Language: Reading Skills: Synthetic Phonics",
            "Educational: English Language: Reading & Writing Skills: Handwriting",
            "ELT Graded Readers",
            "ELT Literature & Fiction Readers",
            "ELT Non-fiction & Background Readers",
            "Language Readers"
        ),
        "Graphic Novels & Comics" => array(
            "Graphic Novels",
            "Comic Strip Fiction / Graphic Novels (Children's/Teenage)",
            "Graphic Novels: Superheroes & Super-villains",
            "Graphic Novels: Manga",
            "Graphic Novels: Literary & Memoirs",
            "Graphic Novels: True Stories & Non-fiction",
            "Cartoon & Comic Strips"
        ),
        "Mathematics & Science" => array(
            "Mathematics",
            "Educational: Mathematics & Numeracy: Times Tables",
            "Educational: Mathematics & Numeracy",
            "Educational: Physics",
            "Educational: Chemistry",
            "Educational: Biology"
        ),
        "Cooking & Food" => array(
            "Cooking & Food (Children's/Teenage)",
            "Cooking with Chicken & Other Poultry",
            "Cooking for One",
            "Quick & Easy Cooking",
            "Cooking with Meat & Game",
            "Vegetarian Cookery",
            "Cooking for Parties",
            "Cooking for/with Children"
        ),
        "Reference & Encyclopedias" => array(
            "Encyclopedias (Children's/Teenage)",
            "Atlases & Maps (Children's/Teenage)",
            "Reference Works (Children's/Teenage)",
            "Language Phrasebooks",
            "Bilingual & Multilingual Dictionaries"
        ),
        "Music & Art" => array(
            "Music: General Interest (Children's/Teenage)",
            "Music: Pop Music (Children's/Teenage)",
            "Music: Songbooks",
            "Techniques of Music / Music Tutorials",
            "Musical Instruments & Instrumental Ensembles",
            "Keyboard Instruments",
            "Wind Instruments",
            "String Instruments",
            "Drawing & Drawings",
            "Painting & Paintings",
            "Art: General Interest (Children's/Teenage)"
        )
    );

    function searchBooks ($input){
            $prompt = "
            Return the answer as a JSON object.
                You are Magnocarta, an AI that generates book results for a website and returning that information in an object oriented json.
                The ISBN should be from an United Kingdom publisher if there is not any give an United States one.
                Return up to 4 results that can be found on amazon. Do not give books by the same author, same series, or that are contained in the prompt.
                If a book is newer and you do not have info about it return the JSON Format {error: \"unknown\"}.
                Only return one book, not an entire series.
                Prioritise british authors.
                Prioritise popular books.
                Books must be published after 2011.
                All results must have all the data filled in the json values.
                The prompt must be compatible to return the book data and must be appropriate, if it is not, return just JSON Format {error: \"false\"}.
                If it is an inappropriate prompt, return just in JSON Format {error: \"false\"}.
                Return the data in the format of { recommendations [{bookName, bookDescription, bookISBN}, {bookName, bookDescription, bookISBN}, ...] }.
                The question is \"$input\" ";
        return $this->callGpt($prompt);
    }

    function searchToppstaBooks ($input){
        $contextData = $this->retrieveContextData($input);
        $contextDataParsed = array_key_exists("error", $contextData) ? json_decode("{\"error\": \"" . $contextData["error"] . "\"}", true) : json_decode($contextData["choices"][0]["message"]["content"], true)["data"];
        if(array_key_exists("context",$contextDataParsed) && $contextDataParsed["context"] == "category"){
            $sentCategories = "";
            $name = array_key_exists("name",$contextDataParsed) && $contextDataParsed["name"] != "null" ? $contextDataParsed["name"] : "";

            //logic for categories
            if($name != "" && count(explode(',',$name)) == 1){
                $sentCategories = implode(',', $this->mainCategories[$name]);
                $categorySearchPrompt = "I need you to return the most viable category from a list of categories for the sentence in question that is provided.
                The list of categories is [".$sentCategories."].
                The sentence is \"".$input."\".
                Return the most viable categories in the format of JSON {data : [category1, category2,...]}.
                Do not explain anything, just return JSON.";
                $returnedCategoryData = $this->callGpt($categorySearchPrompt);
                $returnedCategoryParsed = array_key_exists("error", $returnedCategoryData) ? json_decode("{\"error\": \"" . $returnedCategoryData["error"] . "\"}", true) : json_decode($returnedCategoryData["choices"][0]["message"]["content"], true)["data"];
                $contextDataParsed["name"] = is_array($returnedCategoryParsed) ? implode(",", $returnedCategoryParsed) : $returnedCategoryParsed;
            }
            else if($name != "" && count(explode(',',$name)) > 1){
                $splitted = explode(',',$name);
                for ($i = 0; $i < count($splitted); $i++){
                    $sentCategories .= implode(',', $this->mainCategories[$splitted[$i]]);
                }
                $categorySearchPrompt = "I need you to return the most viable category from a list of categories for the sentence in question that is provided.
                The list of categories is [".$sentCategories."].
                The sentence is \"".$input."\".
                Return the most viable categories in the format of JSON {data : [category1, category2,...]}.
                Do not explain anything, just return JSON.";
                $returnedCategoryData = $this->callGpt($categorySearchPrompt);
                $returnedCategoryParsed = array_key_exists("error", $returnedCategoryData) ? json_decode("{\"error\": \"" . $returnedCategoryData["error"] . "\"}", true) : json_decode($returnedCategoryData["choices"][0]["message"]["content"], true)["data"];
                $contextDataParsed["name"] = is_array($returnedCategoryParsed) ? implode(",", $returnedCategoryParsed) : $returnedCategoryParsed;
            }
        }
        //end of logic for categories

        dd($contextDataParsed);

    }

    function retrieveContextData($input){
        $prompt = "I want you to act like a sentence context extractor. I will give you an sentence and you need to return an \'context\' variable from the given available contexts (category, book, series, author, illustrator). If the context is categorical then return most relevant value for \'name\' variable from this list of categories (\"Children\'s / Teenage Fiction & True Stories\", \"Picture Books & Storybooks\", \"Educational & Learning\", \"Early Learning & Activity Books\", \"Animals & Wildlife\", \"General Non-Fiction\", \"Health & Personal Development\", \"Poetry & Literature\", \"Educational: English Language & Reading\", \"Graphic Novels & Comics\", \"Mathematics & Science\", \"Cooking & Food\", \"Reference & Encyclopedias\", \"Music & Art\");\nIf in the sentence there is by, from and then a name, that is an author context. As well as return if there is a date or time mentioned in the \'date\' variable in numeric value or \'this year\' or \'next year\' , name of the book, author, series or similar in the \'name\' variable, \'orderBy\' returned from the context, values can be from this list (newest, oldest, best, popular). If a variable has no data in the sentence return the value as \'null\'. \'similar\' variable has the value yes if the sentence is asking for something similar to what is wanted, and no if it is asking directly for one type of series, book by author, or just books by that one author. The format must be all variables returned in a list.\nExample is sentence:  \"I want to read a new book in the harry potter series\" , your response: \"{data: {\"context\" : \"series\", \"date\": \"null\", \"name\":\"harry potter\", \"orderBy\" : \"newest\", \"similar\" : \"no\"}}\".\nAnother example:  sentence:  \"I want to read a book published in 2019 by Johny Mans\" , your response : \"{data: {\"context\" : \"author\", \"date\": \"2019\", \"name\":\"Johny Mans\", \"orderBy\" : \"null\", \"similar\" : \"no\"}}\".\nAnother example :  sentence :  \"Books like Harry Potter \", your response :{data: {\"context\" : \"book\", \"date\": \"null\", \"name\":\"Harry Potter\", \"orderBy\" : \"null\", \"similar\" : \"yes\"}}\".\nAnother example :  sentence :  \"A Middle grade murder mystery series\", your response : {data: {\"context\" : \"category\", \"date\": \"null\", \"name\":\"Children\'s / Teenage Fiction & True Stories\", \"orderBy\" : \"null\", similar : \"null\"}}\".\nThe list must look like  this \"data : {variable : value}\". Return in JSON format. Variables are \'context\', \'date\', \'name\', \'orderBy\', \'similar\'. Do not explain. The sentence is \"".$input."\".\n";
        return $this->callGpt($prompt);
    }

    function callGpt($prompt){
        try {
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
