<?php

$string_data = file_get_contents("klasa.txt");
$array = [];
$array = unserialize($string_data);

switch ($argv[1]) {
    case "add":
        if (array_key_exists($argv[2], $array)) {
            echo "Podany uczeń już istenieje.";
        } else {
            $imie = readline("Podaj swoje imie: ");
            $nazwisko = readline("Podaj swoje nazwisko: "); 
            $nr = readline("Podaj swoj nr z dziennika: ");
            $array[$argv[2]] = [
                'nr' => $nr,
                'imie' => $imie,
                'nazwisko' => $nazwisko
                ];
                echo "{$array[$argv[2]]['imie']} {$array[$argv[2]]['nazwisko']} majacy numer {$array[$argv[2]]['nr']} zostal dodany.";
    }
    break;
    case "delete":
        echo "{$array[$argv[2]]['imie']} {$array[$argv[2]]['nazwisko']} majacy numer {$array[$argv[2]]['nr']} zostal usuniety.";
        unset($array[$argv[2]]);
    break;
    case "view":
        if (array_key_exists($argv[2], $array)) {
            echo "{$array[$argv[2]]['imie']} {$array[$argv[2]]['nazwisko']} ma numer {$array[$argv[2]]['nr']}.";
        } else {
            echo "Nie ma takiego ucznia.";
        }
    break;
    case "export":
        $fp = fopen('klasa.csv', 'w'); 
        if (sizeof($argv) === 2) {
            foreach ($array as $fields) {
                fputcsv($fp, $fields);
            }
        } else if (sizeof($argv) === 3) {
            fputcsv($fp, $array[$argv[2]]);
        }
        fclose($fp);
    break;
}

$string_data = serialize($array);
file_put_contents("klasa.txt", $string_data);