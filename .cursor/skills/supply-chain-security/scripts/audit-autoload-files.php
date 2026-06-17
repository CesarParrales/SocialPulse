<?php
// scripts/audit-autoload-files.php
// Audita autoload.files en paquetes de terceros (vector del ataque laravel-lang).
// Ejecutar desde la raíz del proyecto (donde está vendor/):
//   php scripts/audit-autoload-files.php
// Exit code: 0 = limpio, 1 = hallazgos sospechosos (útil como gate en CI).

$WHITELIST = [
    // Paquetes que legítimamente usan autoload.files
    'laravel/framework',           // helpers.php de Laravel
    'laravel/helpers',             // helpers opcionales de Laravel
    'illuminate/support',          // support helpers
    'nesbot/carbon',               // no usa, pero aquí como ejemplo de whitelist
    'ramsey/uuid',                 // no usa, ejemplo
    // Agregar paquetes de tu proyecto que justificadamente usen autoload.files
];

$suspicious = [];
$packages = glob('vendor/*/*/composer.json');

foreach ($packages as $composerJson) {
    $data = json_decode(file_get_contents($composerJson), true);

    if (empty($data['autoload']['files'])) {
        continue;
    }

    // Obtener el nombre del paquete
    $packageName = $data['name'] ?? basename(dirname(dirname($composerJson)));

    if (in_array($packageName, $WHITELIST)) {
        continue;
    }

    $suspicious[] = [
        'package' => $packageName,
        'files'   => $data['autoload']['files'],
        'path'    => $composerJson,
    ];
}

if (empty($suspicious)) {
    echo "✅ No suspicious autoload.files found\n";
    exit(0);
}

echo "⚠️  SUSPICIOUS autoload.files found:\n\n";
foreach ($suspicious as $item) {
    echo "Package: {$item['package']}\n";
    echo "Files:   " . implode(', ', $item['files']) . "\n";
    echo "Path:    {$item['path']}\n";
    echo "\n";
}

echo "Review each package's autoload.files entry.\n";
echo "A translation/icons/utility package should NOT need autoload.files.\n";

// Salir con código de error para CI
exit(count($suspicious) > 0 ? 1 : 0);
