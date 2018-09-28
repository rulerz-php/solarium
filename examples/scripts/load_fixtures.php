#!/usr/bin/env php
<?php

/** @var \Solarium\Client $client */
list($client, $rulerz) = require_once __DIR__.'/../bootstrap.php';

$fixtures = json_decode(file_get_contents(__DIR__.'/../../vendor/kphoen/rulerz/examples/fixtures.json'), true);

echo sprintf("\e[32mLoading fixtures for %d players\e[0m".PHP_EOL, count($fixtures['players']));

foreach ($fixtures['players'] as $i => $player) {
    // get an update query instance
    $update = $client->createUpdate();

    // create a new document for the data
    $doc = $update->createDocument();

    $doc->id = $i + 1;
    $doc->pseudo = $player['pseudo'];
    $doc->fullname = $player['fullname'];
    $doc->birthday = $player['birthday'];
    $doc->gender = $player['gender'];
    $doc->points = (int) $player['points'];

    // add the document and a commit command to the update query
    $update->addDocument($doc);
    $update->addCommit();

    $client->update($update);
}