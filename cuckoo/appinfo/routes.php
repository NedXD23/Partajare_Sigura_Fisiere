<?php
/**
 * adding route for ajax callback
 */
return ['routes' => [
    ['name' => 'cuckoo#check', 'url' => '/check', 'verb' => 'GET'],
    ['name' => 'cuckoo#send', 'url' => '/send', 'verb' => 'GET']
]];
