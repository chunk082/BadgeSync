<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncHabboBadges extends Command
{
    protected $signature = 'habbo:sync-badges {--hotel=com} {--limit=100} {--offset=0} {--format=gif}';
    protected $description = 'Sync badges from HabboAssets API and store them in badge_definitions + save badge images';

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
        $this->info("🔄 Fetching badge data from: $apiUrl");

        $response = Http::get($apiUrl);

        if (!$response->successful()) {
            $this->error("❌ Failed to fetch badge data from API.");
            return;
        }

        $badges = $response->json()['badges'] ?? [];

        $savePath = '/public/test/habbobadges/';
        if (!file_exists($savePath)) mkdir($savePath, 0755, true);

        foreach ($badges as $badge) {
            $code = $badge['code'] ?? null;
            $name = $badge['name'] ?? null;
            $desc = $badge['description'] ?? '';

            if (!$code) continue;

            $filename = $savePath . $code . '.' . $format;
            $remoteUrl = ($format === 'png')
                ? "https://images.habbo.com/c_images/album1584/{$code}.png"
                : "https://images.habbo.com/c_images/album1584/{$code}.gif";

            // Skip if file already exists
            if (file_exists($filename)) {
                $this->line("⏩ Skipped (already exists): $code.$format");
            } else {
                try {
                    $imageData = file_get_contents($remoteUrl);

                    if ($imageData) {
                        file_put_contents($filename, $imageData);
                        $this->info("✅ Downloaded: $code.$format");
                    } else {
                        $this->warn("⚠️ Empty image response for: $code");
                        continue;
                    }

                } catch (\Exception $e) {
                    $this->warn("❌ Failed to download image for $code — " . $e->getMessage());
                    continue;
                }
            }

            // Sync to database
            DB::table('badge_definitions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $desc,
                ]
            );

            $this->line("📝 Synced badge: $code");
        }

        $this->info("🎉 Badge sync complete!");
    }
}
