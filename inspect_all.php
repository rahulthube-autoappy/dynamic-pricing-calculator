<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$all = DB::table('components')->get();
echo "Total components in DB: " . $all->count() . "\n\n";

foreach ($all as $c) {
    $parent = is_string($c->parent_id) ? json_decode($c->parent_id, true) : $c->parent_id;
    $pStr = is_array($parent) ? implode(',', $parent) : ($parent ?? 'NULL');
    echo "ID: {$c->id} | is_bundle: {$c->is_bundle} | is_leaf: {$c->is_leaf} | parent: {$pStr} | name: {$c->name}\n";
}