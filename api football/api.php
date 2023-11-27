<?php
// Pobierz klucz API z https://www.api-football.com/
$apiKey = 'YOUR_API_KEY';

// Ustaw dane dostępowe do bazy danych MySQL
$dbHost = 'localhost';
$dbUser = 'your_username';
$dbPassword = 'your_password';
$dbName = 'your_database';

// Połącz się z bazą danych
$mysqli = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Sprawdź połączenie z bazą danych
if ($mysqli->connect_error) {
    die("Błąd połączenia z bazą danych: " . $mysqli->connect_error);
}

// Funkcja do pobierania danych z API Football
function fetchDataFromApi($url, $headers) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

// Funkcja do pobierania listy dostępnych lig
function getLeaguesList() {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/leagues";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Funkcja do pobierania listy meczów dla konkretnej ligi
function getMatchesForLeague($leagueId) {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/fixtures?league=$leagueId";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Funkcja do pobierania listy drużyn dla danej ligi
function getTeamsForLeague($leagueId) {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/teams?league=$leagueId";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Funkcja do pobierania szczegółów konkretnego meczu
function getMatchDetails($matchId) {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/fixtures?id=$matchId";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Funkcja do pobierania szczegółów konkretnej drużyny
function getTeamDetails($teamId) {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/teams?id=$teamId";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Funkcja do pobierania listy zawodników dla danej drużyny
function getPlayersForTeam($teamId) {
    global $apiKey;
    $apiUrl = "https://api-football-v1.p.rapidapi.com/v3/players/squads?team=$teamId";
    $headers = [
        'X-RapidAPI-Host: api-football-v1.p.rapidapi.com',
        'X-RapidAPI-Key: ' . $apiKey,
    ];
    return fetchDataFromApi($apiUrl, $headers);
}

// Pobierz listę lig
$leaguesList = getLeaguesList();

// Wyświetl listę lig
if (isset($leaguesList['response'])) {
    echo "<h2>Lista dostępnych lig:</h2>";
    echo "<ul>";
    foreach ($leaguesList['response'] as $league) {
        echo "<li>";
        echo "{$league['league']['name']} (ID: {$league['league']['id']}) - ";
        echo "<a href='?leagueId={$league['league']['id']}'>Pokaż mecze</a> | ";
        echo "<a href='?leagueId={$league['league']['id']}&view=teams'>Pokaż drużyny</a>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "Błąd pobierania listy lig z API.";
}

// Sprawdź, czy zostało wybrane ID ligi
if (isset($_GET['leagueId'])) {
    $selectedLeagueId = $_GET['leagueId'];

    // Sprawdź, czy użytkownik wybrał przeglądanie drużyn
    if (isset($_GET['view']) && $_GET['view'] === 'teams') {
        $teamsForLeague = getTeamsForLeague($selectedLeagueId);

        // Wyświetl listę drużyn
        if (isset($teamsForLeague['response'])) {
            echo "<h2>Lista drużyn dla wybranej ligi:</h2>";
            echo "<ul>";
            foreach ($teamsForLeague['response'] as $team) {
                echo "<li>";
                echo "{$team['team']['name']} - ";
                echo "<a href='?teamId={$team['team']['id']}'>Pokaż szczegóły</a> | ";
                echo "<a href='?teamId={$team['team']['id']}&view=players'>Pokaż zawodników</a>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "Błąd pobierania listy drużyn z API.";
        }
    } elseif (isset($_GET['teamId'])) {
        // Sprawdź, czy zostało wybrane ID drużyny
        $selectedTeamId = $_GET['teamId'];

        // Sprawdź, czy użytkownik wybrał przeglądanie zawodników
        if (isset($_GET['view']) && $_GET['view'] === 'players') {
            $playersForTeam = getPlayersForTeam($selectedTeamId);

            // Wyświetl listę zawodników
            if (isset($playersForTeam['response'])) {
                echo "<h2>Lista zawodników dla wybranej drużyny:</h2>";
                echo "<ul>";
                foreach ($playersForTeam['response'] as $player) {
                    echo "<li>{$player['player']['name']}</li>";
                }
                echo "</ul>";
            } else {
                echo "Błąd pobierania listy zawodników z API.";
            }
        } else {
            // Wyświetl szczegóły drużyny
            $teamDetails = getTeamDetails($selectedTeamId);

            if (isset($teamDetails['response'][0])) {
                $team = $teamDetails['response'][0]['team'];
                echo "<h2>Szczegóły drużyny:</h2>";
                echo "<p>Nazwa: {$team['name']}</p>";
                echo "<p>Kraj: {$team['country']}</p>";
                // Dodaj więcej szczegółów, jeśli są dostępne
            } else {
                echo "Błąd pobierania szczegółów drużyny z API.";
            }
        }
    } else {
        // Wyświetl listę meczów
        $matchesForLeague = getMatchesForLeague($selectedLeagueId);

        if (isset($matchesForLeague['response'])) {
            echo "<h2>Lista meczów dla wybranej ligi:</h2>";
            echo "<ul>";
            foreach ($matchesForLeague['response'] as $match) {
                echo "<li>";
                echo "{$match['fixture']['date']} - ";
                echo "{$match['teams']['home']['name']} vs {$match['teams']['away']['name']} - ";
                echo "<a href='?matchId={$match['fixture']['id']}'>Pokaż szczegóły</a>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "Błąd pobierania listy meczów z API.";
        }
    }
}

// Zakończ połączenie z bazą danych
$mysqli->close();
?>