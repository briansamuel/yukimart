<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\UserTimeStamp;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes, UserTimeStamp;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'color',
        'parent_id',
        'sort_order',
        'is_active',
        'show_in_menu',
        'show_on_homepage',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_on_homepage' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method to generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.).
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors->reverse();
    }

    /**
     * Get products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get active products in this category.
     */
    public function activeProducts()
    {
        return $this->products()->where('product_status', 'active');
    }

    /**
     * Get the breadcrumb path.
     */
    protected function breadcrumb(): Attribute
    {
        return new Attribute(
            get: function() {
                $breadcrumb = [];
                $ancestors = $this->ancestors();
                
                foreach ($ancestors as $ancestor) {
                    $breadcrumb[] = $ancestor->name;
                }
                
                $breadcrumb[] = $this->name;
                
                return implode(' > ', $breadcrumb);
            }
        );
    }

    /**
     * Get the full path (including ancestors).
     */
    protected function fullPath(): Attribute
    {
        return new Attribute(
            get: function() {
                $path = [];
                $ancestors = $this->ancestors();
                
                foreach ($ancestors as $ancestor) {
                    $path[] = $ancestor->slug;
                }
                
                $path[] = $this->slug;
                
                return implode('/', $path);
            }
        );
    }

    /**
     * Get the level/depth of this category.
     */
    protected function level(): Attribute
    {
        return new Attribute(
            get: function() {
                return $this->ancestors()->count();
            }
        );
    }

    /**
     * Get status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->is_active) {
                    return '<span class="badge badge-light-success">Hoạt động</span>';
                } else {
                    return '<span class="badge badge-light-danger">Không hoạt động</span>';
                }
            }
        );
    }

    /**
     * Get products count including descendants.
     */
    protected function totalProductsCount(): Attribute
    {
        return new Attribute(
            get: function() {
                $count = $this->products()->count();
                
                foreach ($this->descendants as $descendant) {
                    $count += $descendant->products()->count();
                }
                
                return $count;
            }
        );
    }

    /**
     * Check if this category has children.
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if this category is a root category.
     */
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this category is a leaf category.
     */
    public function isLeaf()
    {
        return !$this->hasChildren();
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for categories shown in menu.
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope for categories shown on homepage.
     */
    public function scopeOnHomepage($query)
    {
        return $query->where('show_on_homepage', true);
    }

    /**
     * Scope for ordering by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get tree structure for select options.
     */
    public static function getTreeOptions($selectedId = null, $excludeId = null)
    {
        $categories = self::with('descendants')
                         ->root()
                         ->active()
                         ->ordered()
                         ->get();
        
        $options = [];
        
        foreach ($categories as $category) {
            if ($excludeId && $category->id == $excludeId) {
                continue;
            }
            
            $options[] = [
                'id' => $category->id,
                'name' => $category->name,
                'level' => 0,
                'selected' => $selectedId == $category->id,
            ];
            
            self::addChildrenToOptions($category->children, $options, 1, $selectedId, $excludeId);
        }
        
        return $options;
    }

    /**
     * Recursively add children to options array.
     */
    private static function addChildrenToOptions($children, &$options, $level, $selectedId = null, $excludeId = null)
    {
        foreach ($children as $child) {
            if ($excludeId && $child->id == $excludeId) {
                continue;
            }
            
            $options[] = [
                'id' => $child->id,
                'name' => str_repeat('— ', $level) . $child->name,
                'level' => $level,
                'selected' => $selectedId == $child->id,
            ];
            
            if ($child->children->count() > 0) {
                self::addChildrenToOptions($child->children, $options, $level + 1, $selectedId, $excludeId);
            }
        }
    }

    /**
     * Get menu tree structure.
     */
    public static function getMenuTree()
    {
        return self::with('children')
                  ->root()
                  ->active()
                  ->inMenu()
                  ->ordered()
                  ->get();
    }

    /**
     * Get homepage categories.
     */
    public static function getHomepageCategories()
    {
        return self::active()
                  ->onHomepage()
                  ->ordered()
                  ->get();
    }
}
