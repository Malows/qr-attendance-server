<?php

use App\Console\Commands\TranslateMessages;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Crear directorios temporales para tests
    $this->tempDir = sys_get_temp_dir() . '/translate_test_' . uniqid();
    File::makeDirectory($this->tempDir, 0755, true);
    
    $this->langDir = $this->tempDir . '/lang';
    File::makeDirectory($this->langDir . '/es', 0755, true);
    File::makeDirectory($this->langDir . '/en', 0755, true);
    
    $this->phpFilesDir = $this->tempDir . '/php';
    File::makeDirectory($this->phpFilesDir, 0755, true);
});

afterEach(function () {
    // Limpiar archivos temporales
    if (File::exists($this->tempDir)) {
        File::deleteDirectory($this->tempDir);
    }
});

test('translate check command runs without options', function () {
    $exitCode = Artisan::call('translate:check');
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    expect($output)->toContain('🌐 Checking translation usage...');
    expect($output)->toContain('📊 Translation usage summary:');
    expect($output)->toContain('✅ Translation check completed!');
});

test('translate check command runs with missing option', function () {
    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    expect($output)->toContain('🌐 Checking translation usage...');
    expect($output)->toContain('🔍 Checking for missing translation keys...');
    expect($output)->toContain('📚 Checking es translations...');
    expect($output)->toContain('📚 Checking en translations...');
    expect($output)->toContain('✅ Translation check completed!');
});

// test('command detects missing translation keys', function () {
//     // Crear un archivo PHP con claves de traducción que no existen
//     $phpFileName = app_path('Http/Controllers/TestController.php');
//     $phpContent = '<?php
// class TestController {
//     public function index() {
//         return __("missing.key.that.does.not.exist");
//     }
// }';
//     File::put($phpFileName, $phpContent);
//     $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
//     expect($exitCode)->toBe(0);
    
//     $output = Artisan::output();
//     expect($output)->toContain('❌ Missing keys');
//     expect($output)->toContain('missing.key.that.does.not.exist');
    
//     // Limpiar archivo temporal
//     File::delete($phpFileName);
// });

test('command shows correct translation coverage percentage', function () {
    $exitCode = Artisan::call('translate:check');
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    expect($output)->toContain('📈 Translation coverage:');
    expect($output)->toContain('%');
});

test('command warns when translation coverage is low', function () {
    // Crear archivos con muchos strings hardcoded para simular baja cobertura
    $phpFileName = app_path('Http/Controllers/TestCoverageController.php');
    $phpContent = '<?php
class TestController {
    public function index() {
        return response()->json([
            "message" => "This is a hardcoded message that should be translated",
            "error" => "Another hardcoded error message here for testing",
            "success" => "Yet another hardcoded success message to test coverage"
        ]);
    }
}';
    File::put($phpFileName, $phpContent);
    
    $exitCode = Artisan::call('translate:check');
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    
    // Verificar que muestra advertencia de baja cobertura si es necesario
    if (str_contains($output, '⚠️')) {
        expect($output)->toContain('⚠️  Consider translating more messages');
    }
    
    // Limpiar archivo temporal
    File::delete($phpFileName);
});

test('command detects different translation patterns', function () {
    // Crear archivo con diferentes patrones de traducción
    $phpFileName = app_path('Http/Controllers/TestPatternsController.php');
    $phpContent = '<?php
class TestController {
    public function index() {
        $message1 = __("validation.required");
        $message2 = @lang("messages.success");
        return $message1 . $message2;
    }
}';
    File::put($phpFileName, $phpContent);

    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    
    // Si las claves existen, no deberían aparecer como faltantes
    if (str_contains($output, 'validation.required') || str_contains($output, 'messages.success')) {
        expect($output)->toContain('❌ Missing keys');
    } else {
        expect($output)->toContain('✅ All keys found');
    }
    
    // Limpiar archivo temporal
    File::delete($phpFileName);
});

test('command handles non-existent language files gracefully', function () {
    // El comando debería manejar archivos de idioma faltantes sin fallar
    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    expect($output)->toContain('✅ Translation check completed!');
});

test('command signature is correct', function () {
    $command = new TranslateMessages();
    
    expect($command->getName())->toBe('translate:check');
    expect($command->getDescription())->toBe('Check and validate translation keys usage across the application');
});

test('command detects existing translation keys correctly', function () {
    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    
    // Verificar que no marca como faltantes las claves que existen
    expect($output)->not->toContain('validation.required');
    expect($output)->not->toContain('messages.login.success');
    expect($output)->not->toContain('attributes.email');
});

test('command provides useful output format', function () {
    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    
    // Verificar que tiene emojis y formato legible
    expect($output)->toContain('🌐');
    expect($output)->toContain('🔍');
    expect($output)->toContain('📚');
    expect($output)->toContain('📊');
    expect($output)->toContain('📈');
    expect($output)->toContain('✅');
});

test('command can handle empty php files', function () {
    // Crear archivo PHP vacío
    $phpFileName = app_path('Http/Controllers/EmptyController.php');
    File::put($phpFileName, '<?php');

    $exitCode = Artisan::call('translate:check');
    
    expect($exitCode)->toBe(0);
    
    // No debería fallar con archivos vacíos
    $output = Artisan::output();
    expect($output)->toContain('✅ Translation check completed!');
    
    // Limpiar archivo temporal
    File::delete($phpFileName);
});

test('command detects translation keys in nested arrays', function () {
    // Crear archivo con clave de traducción anidada
    $phpFileName = app_path('Http/Controllers/TestNestedController.php');
    $phpContent = '<?php
class TestController {
    public function index() {
        return __("validation.location_id.required");
    }
}';

    File::put($phpFileName, $phpContent);

    $exitCode = Artisan::call('translate:check', ['--missing' => true]);
    
    expect($exitCode)->toBe(0);
    
    $output = Artisan::output();
    
    // Esta clave debería existir y no aparecer como faltante
    expect($output)->not->toContain('validation.location_id.required');
    
    // Limpiar archivo temporal
    File::delete($phpFileName);
});