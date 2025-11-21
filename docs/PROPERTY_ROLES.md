# Property-Based Roles & Permissions

Tento projekt používá **Spatie Laravel Permission** s vlastním rozšířením pro property-specific roles.

## Vztah User ↔ Properties

Users mají **many-to-many** vztah s Properties přes pivot tabulku `property_user`.

```php
// Přidat uživatele k property
$user->properties()->attach($property);

// Odebrat uživatele z property
$user->properties()->detach($property);

// Získat všechny properties uživatele
$user->properties;

// Zkontrolovat přístup k property
$user->hasProperty($property);
```

## Wildcard Property Roles

Role jsou ve formátu: `{role}:property:{property_id}`

Příklady:
- `manager:property:1` - Manager pro property ID 1
- `viewer:property:2` - Viewer pro property ID 2
- `admin:property:5` - Admin pro property ID 5

## Použití

### Přiřazení role pro konkrétní property

```php
$user->assignPropertyRole('manager', $property);
$user->assignPropertyRole('viewer', $property);
```

### Kontrola role pro property

```php
if ($user->hasPropertyRole('manager', $property)) {
    // User je manager této property
}

if ($user->hasAnyPropertyRole($property)) {
    // User má jakoukoliv roli pro tuto property
}
```

### Získání všech rolí pro property

```php
$roles = $user->getPropertyRoles($property);
// Vrátí: ['manager', 'viewer']
```

### Synchronizace rolí

```php
// Odstraní všechny staré role a přiřadí nové
$user->syncPropertyRoles(['manager', 'editor'], $property);
```

### Odebrání role

```php
$user->removePropertyRole('manager', $property);
```

## Middleware

Můžeš vytvořit middleware pro kontrolu property-specific permissions:

```php
// app/Http/Middleware/CheckPropertyRole.php
public function handle($request, Closure $next, $role)
{
    $property = Property::findOrFail($request->route('property'));
    
    if (!auth()->user()->hasPropertyRole($role, $property)) {
        abort(403, 'Unauthorized');
    }
    
    return $next($request);
}
```

Použití v routes:

```php
Route::middleware(['auth', 'property.role:manager'])->group(function () {
    Route::get('/properties/{property}/settings', ...);
});
```

## Doporučené role

- `owner` - Vlastník property (plná kontrola)
- `manager` - Manažer (správa bookings, nastavení)
- `staff` - Personál (pouze čtení a základní operace)
- `viewer` - Pouze prohlížení

## Migrace existujících dat

Pokud máš existující uživatele s properties (hasMany vztah), potřebuješ je migrovat:

```php
// Migrace z hasMany na belongsToMany
User::with('properties')->each(function ($user) {
    foreach ($user->properties as $property) {
        // Přidat do pivot tabulky
        $user->properties()->syncWithoutDetaching($property->id);
        
        // Přiřadit owner roli
        $user->assignPropertyRole('owner', $property);
    }
});
```
