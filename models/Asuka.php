<?php

namespace Asuka\Models;

use medoo;

class Asuka
{
    function __construct(medoo $database)
    {
        $this->database = $database;
    }

    function getDatabase() {
       return $this->database;
    }
}