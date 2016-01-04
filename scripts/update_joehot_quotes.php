<?php

const QUOTE_DB_SOURCE_URL = 'https://git.yawk.at/?p=jhq-server.git;a=blob_plain;f=joehot.qt.txt;h=92a89c73f1aa7cf2120524111ef1e10262b70026;hb=HEAD';

$dataPath = realpath(__DIR__) . '/../data/';
$quoteDatabase = $dataPath . 'joehot.db';
$db = new PDO('sqlite:' . $quoteDatabase);
$db->exec("CREATE TABLE IF NOT EXISTS quotes (id INT PRIMARY KEY, citation TEXT DEFAULT 'joehot200', source TEXT DEFAULT NULL, quote TEXT NOT NULL)");

$lines = explode("\n", file_get_contents(QUOTE_DB_SOURCE_URL));

$sth = $db->prepare('INSERT INTO quotes (citation, source, quote) VALUES (?, ?, ?)');
foreach ($lines as $quote) {
    $quoteParts = [];

    // Parse the quote DB according to the rules defined at https://git.yawk.at/?p=jhq-server.git;a=blob;f=README.md;h=a0894ebf6cd5bd94488bb61c0bf3d5ec54821e61;hb=HEAD
    if (preg_match('/^(.*)::/', $quote, $matches)) {
        $quoteParts['citation'] = trim(rtrim($matches[0], '::'));
        $quote = preg_replace('/^.*::/', '', $quote);
    }

    if (preg_match('/#(.*)$/', $quote, $matches)) {
        if (!empty(trim(str_replace('^', '', $matches[0])))) {
            $quoteParts['source'] = trim(ltrim($matches[0], '#'));
            $quote = preg_replace('/#.*$/', '', $quote);
        }
    }

    $quoteParts['text'] = trim($quote);

    $sth->execute([
        array_key_exists('citation', $quoteParts) ? $quoteParts['citation'] : null,
        array_key_exists('source', $quoteParts) ? $quoteParts['source'] : null,
        $quoteParts['text']
    ]);
}