<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SERVER['PATH_INFO'])){
    http_response_code(405);
    exit();
}

$error_response = array(
    "success" => false,
    "description" => "Required inputs were not received. A Search could not be conducted.",
    "results" => [],
);

$request = explode('/', $_SERVER['PATH_INFO']);
$RequestType = (isset($request["1"]) && $request["1"] <> '') ? trim(strtoupper($request["1"])) : exit(json_encode($error_response));
$Relationship = (isset($request["2"]) && $request["2"] <> '') ? trim($request["2"]) : exit(json_encode($error_response));
$Keyword = (isset($request["3"]) && $request["3"] <> '') ? trim(strtoupper($request["3"])) : exit(json_encode($error_response));

$dSearch = new Search($RequestType, $Relationship, $Keyword);


class Search{

    /* =============================================================================================== */

    private $RequestType;
    private $Relationship;
    private $Keyword;
    private $pdo;
    private $output_array = array(
        "success" => false,
        "description" => "Required inputs were not received. A Search could not be conducted.",
        "results" => [],
    );

    public function __construct(string $RequestType, string $Relationship, string $Keyword) {
        $this->RequestType = $RequestType;
        $this->Relationship = $Relationship;
        $this->Keyword = $Keyword;

        $this->pdo = new PDO("sqlite:contacts.db");
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        switch($this->RequestType){
            case "FETCH":
                $this->fetch();
                exit(json_encode($this->output_array));
            break;
        }
    }

    private function fetch() : bool {
        if(strtoupper($this->Relationship) != 'ALL'){
            $sql = 'SELECT * FROM contacts WHERE Relationship = ? AND (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? )';
            $exec_arr = array($this->Relationship, "%".$this->Keyword."%", "%".$this->Keyword."%", "%".$this->Keyword."%");
        } else {
            $sql = 'SELECT * FROM contacts WHERE Relationship LIKE ? OR FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ?';
            $exec_arr = array("%".$this->Keyword."%", "%".$this->Keyword."%", "%".$this->Keyword."%", "%".$this->Keyword."%");
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($exec_arr);

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($results) || count($results) < 1) {
            $this->output_array["success"] = false;
            $this->output_array["description"] = "No search results found.";
            $this->output_array["results"] = [];

            $results = false;
        } else {
            foreach($results as $key => $value){
                $results[$key]["Phone"] = '('.substr($value["Phone"], 0, 3).') '.substr($value["Phone"], 3, 3).'-'.substr($value["Phone"], 6, 4);
            }

            $this->output_array["success"] = true;
            $this->output_array["description"] = "No search results found.";
            $this->output_array["results"] = $results;
        }

        return true;
    }
}