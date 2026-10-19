<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Buku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_akses_endpoint_buku_tanpa_token_mengembalikan_401(): void
    {
        $response = $this->getJson('/api/buku');

        $response->assertStatus(401);
    }

    public function test_tambah_buku_dengan_token_mengembalikan_201(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/buku', [
            'judul' => 'Pemrograman Web Modern',
            'penulis' => 'Bhayudha',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2026,
            'harga' => 95000,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['judul' => 'Pemrograman Web Modern']);
    }
}