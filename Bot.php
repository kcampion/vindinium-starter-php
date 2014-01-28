<?php

class Bot
{

}

class RandomBot extends Bot
{
    public function move($state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');
        return $dirs[array_rand($dirs)];
    }
}


class FighterBot extends Bot
{
    public function move($state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');
        return $dirs[array_rand($dirs)];
    }
}


class SlowBot extends Bot
{
    public function move($state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');
        sleep(2);
        return $dirs[array_rand($dirs)];
    }
}