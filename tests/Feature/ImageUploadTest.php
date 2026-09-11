<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletImage;
use App\Models\Territory;
use App\Models\User;
use App\Support\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    // PNG 1x1 valid (68 byte). Padding di akhir tetap lolos finfo/getimagesize.
    private function pngBytes(int $size): string
    {
        $base = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return str_pad($base, $size, "\0");
    }

    private function pngFile(string $name = 'foto.png', int $size = 2048): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'sn').'.png';
        file_put_contents($path, $this->pngBytes($size));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_valid_image_stored_with_safe_name(): void
    {
        Storage::fake('public');

        $stored = ImageUpload::store($this->pngFile(), 'outlets');

        $this->assertStringStartsWith('outlets/', $stored);
        Storage::disk('public')->assertExists($stored);
    }

    public function test_oversize_rejected(): void
    {
        Storage::fake('public');

        try {
            ImageUpload::store($this->pngFile('besar.png', 6 * 1024 * 1024), 'outlets');
            $this->fail('Di atas 5 MB harus ditolak.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('5 MB', $e->getMessage());
        }
    }

    public function test_non_image_rejected_despite_extension(): void
    {
        Storage::fake('public');
        $path = tempnam(sys_get_temp_dir(), 'sn').'.png';
        file_put_contents($path, str_repeat('bukan-gambar', 100));
        $fake = new UploadedFile($path, 'palsu.png', 'image/png', null, true);

        try {
            ImageUpload::store($fake, 'outlets');
            $this->fail('Konten non-gambar harus ditolak walau ekstensi .png.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
    }

    public function test_sixth_outlet_image_rejected(): void
    {
        Storage::fake('public');
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $territory = Territory::factory()->create();
        $dist->distributorProfile()->create(['status' => 'approved', 'territory_id' => $territory->id]);
        $outlet = Outlet::factory()->create(['territory_id' => $territory->id, 'distributor_id' => $dist->id]);
        foreach (range(1, 5) as $i) {
            OutletImage::create(['outlet_id' => $outlet->id, 'path' => "outlets/f{$i}.png", 'disk' => 'public', 'sort_order' => $i]);
        }

        $files = [];
        for ($i = 0; $i < 1; $i++) {
            $files[] = $this->pngFile("baru{$i}.png");
        }

        $this->actingAs($dist)->post(route('distributor.outlet.images.store', $outlet), ['images' => $files])
            ->assertSessionHasErrors('images');
        $this->assertSame(5, $outlet->images()->count());
    }

    public function test_gallery_renders_without_cropping_and_lazy(): void
    {
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $territory = Territory::factory()->create();
        $dist->distributorProfile()->create(['status' => 'approved', 'territory_id' => $territory->id]);
        $outlet = Outlet::factory()->create([
            'territory_id' => $territory->id, 'distributor_id' => $dist->id,
            'latitude' => -6.1, 'longitude' => 106.8,
        ]);
        OutletImage::create(['outlet_id' => $outlet->id, 'path' => 'outlets/foto.png', 'disk' => 'public', 'sort_order' => 0]);

        $html = $this->actingAs($dist)->get(route('distributor.outlet.show', $outlet))->getContent();

        // Gambar utama: contain (tanpa crop), lazy + fallback bila rusak.
        $this->assertStringContainsString('object-contain', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('onerror', $html);
        // Iframe peta memakai koordinat tersimpan (bukan parse ulang URL).
        $this->assertStringContainsString('-6.1', $html);
        $this->assertStringContainsString('output=embed', $html);
    }
}
