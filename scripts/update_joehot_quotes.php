<?php

const QUOTE_DB_SOURCE_URL = 'https://git.yawk.at/?p=jhq-server.git;a=blob_plain;f=joehot.qt.txt;hb=HEAD';

$dataPath = realpath(__DIR__) . '/../data/';
$quoteDatabase = $dataPath . 'joehot.db';

$db = new PDO('sqlite:' . $quoteDatabase);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS quotes (id INTEGER PRIMARY KEY AUTOINCREMENT, citation TEXT NOT NULL DEFAULT 'joehot200', source TEXT DEFAULT NULL, quote TEXT NOT NULL)");
$sth = $db->prepare('INSERT INTO quotes (citation, source, quote) VALUES (:citation, :source, :quote)');

$lines = explode("\n", file_get_contents(QUOTE_DB_SOURCE_URL));
$lines = array_filter($lines, function ($line) {
    return strpos($line, '#') != 0;
});

foreach ($lines as $quote) {
    // Parse the quote DB according to the rules defined at https://git.yawk.at/?p=jhq-server.git;a=blob;f=README.md;h=a0894ebf6cd5bd94488bb61c0bf3d5ec54821e61;hb=HEAD
    if (preg_match('/^(.*)::/', $quote, $matches)) {
        $quoteParts['citation'] = trim(rtrim($matches[0], '::'));
        $quote = preg_replace('/^.*::/', '', $quote);
    }

    if (preg_match('/#(.*)$/', $quote, $matches)) {
        if (!empty(trim(str_replace('^', '', $matches[0])))) {
            $quoteParts['source'] = trim(ltrim($matches[0], '#'));
            if (empty(trim(str_replace('^', '', $quoteParts['source'])))) {
                unset($quoteParts['source']);
            }
            $quote = preg_replace('/#.*$/', '', $quote);
        }
    }

    $quoteParts['text'] = trim($quote);

    $existing = $db->prepare('SELECT id FROM quotes WHERE quote = :quote LIMIT 1');
    $existing->bindValue(':quote', $quoteParts['text'], PDO::PARAM_STR);
    $existing->execute();
    $result = $existing->fetch(PDO::FETCH_OBJ);

    if (!isset($result->id)) {
        $sth->bindValue(':citation', array_key_exists('citation', $quoteParts) ? $quoteParts['citation'] : 'joehot200');
        $sth->bindValue(':source', array_key_exists('source', $quoteParts) ? $quoteParts['source'] : null);
        $sth->bindValue(':quote', $quoteParts['text'], PDO::PARAM_STR);
        $sth->execute();

        echo 'Added ' . implode('|', $quoteParts) . PHP_EOL;
    } else {
        echo 'Ignoring duplicate...' . PHP_EOL;
    }
}