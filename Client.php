<?php

class Client
{

    CONST TIMEOUT = 15;
    private $key;
    private $mode;
    private $numberOfGames;
    private $numberOfTurns;
    private $serverUrl = 'http://vindinium.org';

    public function __construct()
    {
        if ($_SERVER['argc'] < 4) {
            echo "Usage: " . $_SERVER['SCRIPT_FILENAME'] . " <key> <[training|arena]> <number-of-games|number-of-turns> [server-url]\n";
            echo "Example: " . $_SERVER['SCRIPT_FILENAME'] . " mySecretKey training 20\n";
        } else {
            $this->key = $_SERVER['argv'][1];
            $this->mode = $_SERVER['argv'][2];

            if ($this->mode == "training") {
                $this->numberOfGames = 1;
                $this->numberOfTurns = (int)$_SERVER['argv'][3];
            } else {
                $this->numberOfGames = (int)$_SERVER['argv'][3];
                $this->numberOfTurns = 300; # Ignored in arena mode
            }

            if ($_SERVER['argc'] == 5) {
                $this->serverUrl = $_SERVER['argv'][4];
            }
        }
    }

    public function load()
    {
        require('./Bot.php');
        require('./HttpPost.php');

        for ($i = 0; $i <= ($this->numberOfGames - 1); $i++) {
            $this->start(new RandomBot());
            echo "\nGame finished: " . ($i + 1) . "/" . $this->numberOfGames . "\n";
        }
    }

    private function start($botObject)
    {
        // Starts a game with all the required parameters
        if ($this->mode == 'arena') {
            echo "Connected and waiting for other players to join...\n";
        }

        // Get the initial state
        $state = $this->getNewGameState();
        echo "Playing at: " . $state['viewUrl'] . "\n";

        ob_start();
        while ($this->isFinished($state) === false) {
            // Some nice output ;)
            echo '.';
            ob_flush();
            flush();

            // Move to some direction
            $url = $state['playUrl'];
            $direction = $botObject->move($state);
            $state = $this->move($url, $direction);
        }
    }

    private function getNewGameState()
    {
        // Get a JSON from the server containing the current state of the game
        if ($this->mode == 'training') {
            // Don't pass the 'map' parameter if you want a random map
            $params = array('key' => $this->key, 'turns' => $this->numberOfTurns, 'map' => 'm1');
            $api_endpoint = '/api/training';
        } elseif ($this->mode == 'arena') {
            $params = array('key' => $this->key);
            $api_endpoint = '/api/arena';
        }

        // Wait for 10 minutes
        $r = HttpPost::post($this->serverUrl . $api_endpoint, $params, 10 * 60);

        if (isset($r['headers']['status_code']) && $r['headers']['status_code'] == 200) {
            return json_decode($r['content'], true);
        } else {
            echo "Error when creating the game\n";
            echo $r['content'];
        }
    }

    private function move($url, $direction)
    {
        /*
         * Send a move to the server
         * Moves can be one of: 'Stay', 'North', 'South', 'East', 'West'
         */

        try {
            $r = HttpPost::post($url, array('dir' => $direction), self::TIMEOUT);
            if (isset($r['headers']['status_code']) && $r['headers']['status_code'] == 200) {
                return json_decode($r['content'], true);
            } else {
                echo "Error HTTP " . $r['headers']['status_code'] . "\n" . $r['content'] . "\n";
                return array('game' => array('finished' => true));
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            return array('game' => array('finished' => true));
        }
    }

    private function isFinished($state)
    {
        return $state['game']['finished'];
    }
}