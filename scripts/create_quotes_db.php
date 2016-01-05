<?php
$dataPath      = realpath(__DIR__) . '/../data/';
$quoteDatabase = $dataPath . 'quotes.db';

$db = new PDO('sqlite:' . $quoteDatabase);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS quotes (id INTEGER PRIMARY KEY AUTOINCREMENT, citation TEXT NOT NULL, source TEXT DEFAULT 'Telegram', quote TEXT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)");
