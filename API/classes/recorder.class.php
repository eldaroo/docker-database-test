<?php 

class recorder{

    public static function recordData($newAction){
        $file = file_get_contents('journal.json');
        $data = json_decode($file, true);
        $data["actions"] = array_values($data["actions"]);
        
        array_push($data["actions"], $newAction);
        file_put_contents("journal.json", json_encode($data));
    }
}