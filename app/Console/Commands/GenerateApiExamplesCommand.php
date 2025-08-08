<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiExampleGeneratorService;
use App\Services\PostmanCollectionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class GenerateApiExamplesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:generate-examples 
                            {--sync : Sync to Postman after generating}
                            {--cache-clear : Clear example cache}
                            {--group= : Generate examples for specific group only}
                            {--save-file : Save examples to file}';

    /**
     * The console command description.
     */
    protected $description = 'Generate comprehensive API examples with real responses';

    private $exampleGenerator;
    private $postmanService;

    public function __construct(
        ApiExampleGeneratorService $exampleGenerator,
        PostmanCollectionService $postmanService
    ) {
        parent::__construct();
        $this->exampleGenerator = $exampleGenerator;
        $this->postmanService = $postmanService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Generating API Examples with Real Responses');
        $this->info('==============================================');

        if ($this->option('cache-clear')) {
            $this->clearCache();
        }

        $group = $this->option('group');
        
        if ($group) {
            return $this->generateGroupExamples($group);
        }

        return $this->generateAllExamples();
    }

    /**
     * Generate examples for all endpoints
     */
    private function generateAllExamples()
    {
        $this->info('🔍 Generating examples for all API endpoints...');

        try {
            $examples = $this->exampleGenerator->generateAllExamples();
            
            $this->displayResults($examples);
            
            if ($this->option('save-file')) {
                $this->saveExamplesToFile($examples);
            }

            if ($this->option('sync')) {
                $this->syncToPostman($examples);
            }

            $this->info('✅ API examples generated successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate examples: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate examples for specific group
     */
    private function generateGroupExamples(string $group)
    {
        $this->info("🔍 Generating examples for {$group} endpoints...");

        try {
            $allExamples = $this->exampleGenerator->generateAllExamples();
            
            if (!isset($allExamples[$group])) {
                $this->error("❌ Group '{$group}' not found. Available groups: " . implode(', ', array_keys($allExamples)));
                return 1;
            }

            $examples = [$group => $allExamples[$group]];
            $this->displayResults($examples);

            if ($this->option('save-file')) {
                $this->saveExamplesToFile($examples, $group);
            }

            $this->info("✅ Examples for {$group} generated successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate examples: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display generation results
     */
    private function displayResults(array $examples)
    {
        $this->info('📊 Generation Results:');
        $this->info('=====================');

        $totalEndpoints = 0;
        $totalExamples = 0;

        foreach ($examples as $group => $endpoints) {
            $endpointCount = count($endpoints);
            $exampleCount = 0;

            foreach ($endpoints as $endpoint) {
                $exampleCount += count($endpoint['examples'] ?? []);
            }

            $totalEndpoints += $endpointCount;
            $totalExamples += $exampleCount;

            $this->line("📁 {$group}: {$endpointCount} endpoints, {$exampleCount} examples");
        }

        $this->info('');
        $this->info("📈 Total: {$totalEndpoints} endpoints, {$totalExamples} examples");
    }

    /**
     * Save examples to file
     */
    private function saveExamplesToFile(array $examples, string $group = 'all')
    {
        $this->info('💾 Saving examples to file...');

        $filename = $group === 'all' ? 'api-examples-complete.json' : "api-examples-{$group}.json";
        $path = "testing/api-examples/{$filename}";

        $data = [
            'generated_at' => now()->toISOString(),
            'base_url' => config('app.url') . '/api/v1',
            'total_groups' => count($examples),
            'examples' => $examples
        ];

        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));
        
        $fullPath = storage_path("app/{$path}");
        $this->info("✅ Examples saved to: {$fullPath}");
    }

    /**
     * Sync to Postman
     */
    private function syncToPostman(array $examples)
    {
        $this->info('📮 Syncing to Postman with enhanced examples...');

        try {
            // Generate collection with examples
            $collection = $this->postmanService->generateCollection();
            
            // Enhance collection with real examples
            $collection = $this->enhanceCollectionWithExamples($collection, $examples);
            
            // Sync to Postman
            $result = $this->postmanService->syncToPostman($collection);
            
            $this->info('✅ Postman collection updated with real examples!');
            
        } catch (\Exception $e) {
            $this->error('❌ Postman sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Enhance Postman collection with real examples
     */
    private function enhanceCollectionWithExamples(array $collection, array $examples): array
    {
        foreach ($collection['item'] as &$folder) {
            $folderName = $folder['name'];
            
            // Extract group name from folder name (remove emoji)
            $groupName = strtolower(trim(preg_replace('/[^\w\s]/', '', $folderName)));
            
            if (isset($examples[$groupName])) {
                foreach ($folder['item'] as &$request) {
                    $requestName = $request['name'];
                    
                    // Find matching example
                    foreach ($examples[$groupName] as $endpoint) {
                        if ($this->matchesRequest($request, $endpoint)) {
                            $request['response'] = $this->createPostmanResponses($endpoint['examples']);
                            break;
                        }
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * Check if request matches endpoint
     */
    private function matchesRequest(array $request, array $endpoint): bool
    {
        $requestMethod = $request['request']['method'] ?? '';
        $requestUrl = $request['request']['url']['raw'] ?? '';
        
        $endpointMethod = $endpoint['method'] ?? '';
        $endpointUrl = $endpoint['url'] ?? '';
        
        return $requestMethod === $endpointMethod && 
               str_contains($requestUrl, str_replace('/api/v1', '', $endpointUrl));
    }

    /**
     * Create Postman response examples
     */
    private function createPostmanResponses(array $examples): array
    {
        $responses = [];
        
        foreach ($examples as $name => $example) {
            if (isset($example['body'])) {
                $responses[] = [
                    'name' => ucfirst(str_replace('_', ' ', $name)),
                    'originalRequest' => [
                        'method' => $example['method'] ?? 'GET',
                        'header' => [],
                        'url' => [
                            'raw' => $example['url'] ?? '',
                            'host' => ['{{base_url}}'],
                            'path' => []
                        ]
                    ],
                    'status' => $this->getStatusText($example['status_code'] ?? 200),
                    'code' => $example['status_code'] ?? 200,
                    '_postman_previewlanguage' => 'json',
                    'header' => [
                        [
                            'key' => 'Content-Type',
                            'value' => 'application/json'
                        ]
                    ],
                    'cookie' => [],
                    'body' => json_encode($example['body'], JSON_PRETTY_PRINT)
                ];
            }
        }

        return $responses;
    }

    /**
     * Get status text from code
     */
    private function getStatusText(int $code): string
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error'
        ];

        return $statusTexts[$code] ?? 'Unknown';
    }

    /**
     * Clear example cache
     */
    private function clearCache()
    {
        $this->info('🗑️ Clearing example cache...');
        
        Cache::forget('api_examples_*');
        
        $this->info('✅ Cache cleared');
    }
}
