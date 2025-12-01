<?php

use App\States\Booking\Pending;
use App\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Relations\Relation;

// Ensure morph map is loaded (AppServiceProvider boots automatically in tinker)

$state = new Pending(new Booking());
echo "State class: " . get_class($state) . "\n";

// Check what getMorphClass returns
// Note: Spatie State might not be a Model, so getMorphClass might not exist on it directly unless it uses a trait or the package handles it.
// But the package handles serialization.

// Let's simulate what happens when we cast to string
echo "String cast: " . (string) $state . "\n";

// Let's see if we can find the alias from the morph map
$alias = array_search(get_class($state), Relation::morphMap());
echo "Morph alias: " . ($alias ?: 'Not found') . "\n";
