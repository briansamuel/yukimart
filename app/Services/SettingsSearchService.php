<?php

namespace App\Services;

use Illuminate\Support\Str;

class SettingsSearchService
{
    /**
     * Search settings based on query
     *
     * @param string $query
     * @param \App\Models\User|null $user
     * @return array
     */
    public function search(string $query, $user = null): array
    {
        $items = config('settings_search');
        
        $results = [];
        
        foreach ($items as $item) {
            // Check permission - TEMPORARILY DISABLED FOR TESTING
            // TODO: Re-enable after fixing permission assignment
            // if (!empty($item['permission']) && $user && !$user->can($item['permission'])) {
            //     continue;
            // }

            // Match từ khóa
            $haystack = $this->buildSearchableText($item);
            
            if (Str::contains(strtolower($haystack), strtolower($query))) {
                $results[] = [
                    'label' => $item['label'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'url' => $this->makeUrl($item),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Build searchable text from item
     *
     * @param array $item
     * @return string
     */
    protected function buildSearchableText(array $item): string
    {
        $parts = [
            $item['label'],
            $item['description'],
        ];
        
        if (!empty($item['keywords'])) {
            $parts = array_merge($parts, $item['keywords']);
        }
        
        return implode(' ', $parts);
    }
    
    /**
     * Make URL from item
     *
     * @param array $item
     * @return string
     */
    protected function makeUrl(array $item): string
    {
        // If route is null (placeholder), return '#'
        if (empty($item['route'])) {
            return '#';
        }
        
        $url = route($item['route']);
        
        // Append section_id if exists
        if (!empty($item['section_id'])) {
            $url .= '#' . $item['section_id'];
        }
        
        return $url;
    }
    
    /**
     * Get all settings (for debugging)
     *
     * @param \App\Models\User|null $user
     * @return array
     */
    public function all($user = null): array
    {
        $items = config('settings_search');
        
        $results = [];
        
        foreach ($items as $item) {
            // Check permission
            if (!empty($item['permission']) && $user && !$user->can($item['permission'])) {
                continue;
            }
            
            $results[] = [
                'id' => $item['id'],
                'label' => $item['label'],
                'description' => $item['description'],
                'category' => $item['category'],
                'url' => $this->makeUrl($item),
            ];
        }
        
        return $results;
    }
}

