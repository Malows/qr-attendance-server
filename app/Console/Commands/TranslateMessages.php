<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslateMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:check {--missing : Show missing translation keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and validate translation keys usage across the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Checking translation usage...');
        
        if ($this->option('missing')) {
            $this->checkMissingTranslations();
        }
        
        $this->checkTranslationUsage();
        
        $this->info('✅ Translation check completed!');
    }
    
    private function checkMissingTranslations()
    {
        $this->info('🔍 Checking for missing translation keys...');
        
        $patterns = [
            '__\([\'"]([^\'"]+)[\'"]\)',
            '@lang\([\'"]([^\'"]+)[\'"]\)',
        ];
        
        $usedKeys = [];
        $files = $this->getPhpFiles();
        
        foreach ($files as $file) {
            $content = File::get($file);
            
            foreach ($patterns as $pattern) {
                if (preg_match_all('/' . $pattern . '/', $content, $matches)) {
                    foreach ($matches[1] as $key) {
                        $usedKeys[] = $key;
                    }
                }
            }
        }
        
        $usedKeys = array_unique($usedKeys);
        
        $langFiles = [
            'es' => resource_path('lang/es'),
            'en' => resource_path('lang/en'),
        ];
        
        foreach ($langFiles as $locale => $path) {
            $this->info("📚 Checking {$locale} translations...");
            $missing = [];
            
            foreach ($usedKeys as $key) {
                if (!$this->keyExists($key, $path)) {
                    $missing[] = $key;
                }
            }
            
            if ($missing) {
                $this->warn("❌ Missing keys in {$locale}:");
                foreach ($missing as $key) {
                    $this->line("  - {$key}");
                }
            } else {
                $this->info("✅ All keys found in {$locale}");
            }
        }
    }
    
    private function checkTranslationUsage()
    {
        $this->info('📊 Translation usage summary:');
        
        // Count hardcoded strings that could be translated
        $requestFiles = glob(app_path('Http/Requests/**/*.php'));
        $controllerFiles = glob(app_path('Http/Controllers/**/*.php'));
        
        $hardcodedCount = 0;
        $translatedCount = 0;
        
        foreach (array_merge($requestFiles, $controllerFiles) as $file) {
            $content = File::get($file);
            
            // Count __() usage
            $translatedCount += preg_match_all('/__\(/', $content);
            
            // Count potential hardcoded messages (simple heuristic)
            $hardcodedCount += preg_match_all('/[\'"][A-Z][a-zA-Z\s]{10,}[\'"]\s*[,\]]/', $content);
        }
        
        $total = $hardcodedCount + $translatedCount;
        $percentage = $total > 0 ? round(($translatedCount / $total) * 100, 2) : 0;
        
        $this->info("📈 Translation coverage: {$percentage}% ({$translatedCount}/{$total})");
        
        if ($percentage < 80) {
            $this->warn("⚠️  Consider translating more messages for better internationalization");
        }
    }
    
    private function getPhpFiles()
    {
        return array_merge(
            glob(app_path('Http/Controllers/**/*.php')),
            glob(app_path('Http/Requests/**/*.php')),
            glob(resource_path('views/**/*.php')),
        );
    }
    
    private function keyExists($key, $langPath)
    {
        $parts = explode('.', $key);
        $file = $parts[0];
        $filePath = $langPath . '/' . $file . '.php';
        
        if (!File::exists($filePath)) {
            return false;
        }
        
        $translations = include $filePath;
        $current = $translations;
        
        for ($i = 1; $i < count($parts); $i++) {
            if (!isset($current[$parts[$i]])) {
                return false;
            }
            $current = $current[$parts[$i]];
        }
        
        return true;
    }
}
