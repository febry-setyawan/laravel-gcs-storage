<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_files(): void
    {
        $user = User::factory()->create();
        
        // Create published files
        File::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_published' => true,
        ]);
        
        // Create unpublished files (should not be returned)
        File::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/public/files');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'original_name',
                        'mime_type',
                        'size',
                        'description',
                        'created_at',
                        'user',
                    ],
                ],
                'pagination',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_can_view_published_file_details(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $file = File::factory()->create([
            'user_id' => $user->id,
            'is_published' => true,
            'original_name' => 'test-file.pdf',
        ]);

        $response = $this->getJson("/api/public/files/{$file->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $file->id,
                    'original_name' => 'test-file.pdf',
                    'user' => 'Test User',
                ],
            ]);
    }

    public function test_cannot_view_unpublished_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'user_id' => $user->id,
            'is_published' => false,
        ]);

        $response = $this->getJson("/api/public/files/{$file->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found or not published',
            ]);
    }

    public function test_can_get_public_stats(): void
    {
        $user = User::factory()->create();
        
        File::factory()->count(5)->create([
            'user_id' => $user->id,
            'is_published' => true,
            'size' => 1024,
        ]);
        
        File::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_published' => false,
            'size' => 2048,
        ]);

        $response = $this->getJson('/api/public/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_files',
                    'total_size',
                    'total_size_human',
                    'file_types',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(5, $data['total_files']);
        $this->assertEquals(5120, $data['total_size']); // 5 files * 1024 bytes
    }

    public function test_can_search_published_files(): void
    {
        $user = User::factory()->create();
        
        File::factory()->create([
            'user_id' => $user->id,
            'is_published' => true,
            'original_name' => 'important-document.pdf',
        ]);
        
        File::factory()->create([
            'user_id' => $user->id,
            'is_published' => true,
            'original_name' => 'regular-file.txt',
        ]);

        $response = $this->getJson('/api/public/files?search=important');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('important-document.pdf', $data[0]['original_name']);
    }
}