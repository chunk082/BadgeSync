<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncHabboBadges extends Command
{
    protected $signature = 'habbo:sync-badges {--hotel=com} {--limit=100} {--offset=0} {--format=gif}';
    protected $description = 'Sync badges from HabboAssets API and store them in badge_definitions + save images';

    public function handle()
    {
        $hotel = $this->option('hotel');
        $limit = $this->option('limit');
        $offset = $this->option('offset');
        $format = strtolower($this->option('format'));

        if (!in_array($format, ['gif', 'png'])) {
            $this->error("❌ Invalid format. Use --format=gif or --format=png");
            return;
        }

        $apiUrl = "https://www.habboassets.com/api/v1/badges?hotel=$hotel&limit=$limit&offset=$offset";
        $this->info("🔍 Fetching badge data from: $apiUrl");

        $response = Http::get($apiUrl);

        if (!$response->successful()) {
            $this->error("❌ Failed to fetch badge data from API.");
            return;
        }

        $badges = $response->json()['badges'] ?? [];

        $imagePath = '/public/test/image/';
        if (!file_exists($imagePath)) {
            mkdir($imagePath, 0755, true);
        }

        foreach ($badges as $badge) {
            $code = $badge['code'] ?? null;
            $name = $badge['name'] ?? null;
            $desc = $badge['description'] ?? '';

            if (!$code) continue;

            $filename = "$imagePath/{$code}.$format";

            if (!file_exists($filename)) {
                $remoteGif = "https://images.habbo.com/c_images/album1584/{$code}.gif";

                try {
                    $gifData = file_get_contents($remoteGif);

                    if ($format === 'png') {
                        $tmpFile = tmpfile();
                        $tmpPath = stream_get_meta_data($tmpFile)['uri'];
                        file_put_contents($tmpPath, $gifData);

                        $gifImage = @imagecreatefromgif($tmpPath);
                        if (!$gifImage) {
                            $this->warn("⚠️ Could not convert $code to PNG");
                            fclose($tmpFile);
                            continue;
                        }

                        imagepng($gifImage, $filename);
                        imagedestroy($gifImage);
                        fclose($tmpFile);
                        $this->info("🖼️ Downloaded & converted to PNG: $code");

                    } else {
                        file_put_contents($filename, $gifData);
                        $this->info("🖼️ Downloaded GIF for: $code");
                    }

                } catch (\Exception $e) {
                    $this->warn("⚠️ Failed to download image for $code: {$e->getMessage()}");
                    continue;
                }
            } else {
                $this->line("⏭️ Skipped existing image: $code.$format");
            }

            DB::table('badge_definitions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $desc,
                ]
            );

            $this->info("✅ Synced badge: $code");
        }

        $this->info("🎉 Badge sync complete!");
    }
}
