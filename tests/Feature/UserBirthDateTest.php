<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UserBirthDateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user birth date formatting with Carbon object
     */
    public function test_user_birth_date_with_carbon_object()
    {
        $user = User::factory()->create([
            'birth_date' => Carbon::parse('1990-05-15')
        ]);

        $this->assertEquals('1990-05-15', $user->formatted_birth_date);
        $this->assertEquals('15/05/1990', $user->display_birth_date);
    }

    /**
     * Test user birth date formatting with string date
     */
    public function test_user_birth_date_with_string_date()
    {
        $user = User::factory()->create([
            'birth_date' => '1990-05-15'
        ]);

        $this->assertEquals('1990-05-15', $user->formatted_birth_date);
        $this->assertEquals('15/05/1990', $user->display_birth_date);
    }

    /**
     * Test user birth date formatting with null value
     */
    public function test_user_birth_date_with_null_value()
    {
        $user = User::factory()->create([
            'birth_date' => null
        ]);

        $this->assertEquals('', $user->formatted_birth_date);
        $this->assertEquals('', $user->display_birth_date);
    }

    /**
     * Test pivot date formatting helper
     */
    public function test_format_pivot_date_helper()
    {
        // Test with valid date string
        $this->assertEquals('15/05/1990', User::formatPivotDate('1990-05-15'));
        
        // Test with Carbon object
        $this->assertEquals('15/05/1990', User::formatPivotDate(Carbon::parse('1990-05-15')));
        
        // Test with null
        $this->assertEquals('-', User::formatPivotDate(null));
        
        // Test with empty string
        $this->assertEquals('-', User::formatPivotDate(''));
        
        // Test with invalid date
        $this->assertEquals('-', User::formatPivotDate('invalid-date'));
        
        // Test with custom format
        $this->assertEquals('1990-05-15', User::formatPivotDate('1990-05-15', 'Y-m-d'));
    }

    /**
     * Test birth date casting in model
     */
    public function test_birth_date_casting()
    {
        $user = User::factory()->create([
            'birth_date' => '1990-05-15'
        ]);

        // Refresh from database to test casting
        $user = $user->fresh();

        $this->assertInstanceOf(Carbon::class, $user->birth_date);
        $this->assertEquals('1990-05-15', $user->birth_date->format('Y-m-d'));
    }

    /**
     * Test user edit page doesn't throw error with birth date
     */
    public function test_user_edit_page_with_birth_date()
    {
        $user = User::factory()->create([
            'birth_date' => '1990-05-15'
        ]);

        $response = $this->actingAs($user)
                        ->get(route('admin.users.edit', $user->id));

        $response->assertStatus(200);
        $response->assertSee('1990-05-15'); // Should see formatted date in form
    }

    /**
     * Test user edit page doesn't throw error with null birth date
     */
    public function test_user_edit_page_with_null_birth_date()
    {
        $user = User::factory()->create([
            'birth_date' => null
        ]);

        $response = $this->actingAs($user)
                        ->get(route('admin.users.edit', $user->id));

        $response->assertStatus(200);
    }
}
