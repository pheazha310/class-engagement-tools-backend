<?php

namespace Database\Seeders;

use App\Models\WheelTheme;
use Illuminate\Database\Seeder;

class WheelThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'name' => 'Classic',
                'config' => [
                    'background' => '#ffffff',
                    'primary' => '#ef4444',
                    'secondary' => '#f8fafc',
                    'text' => '#1e293b',
                    'accent' => '#dc2626',
                ],
                'is_default' => true,
            ],
            [
                'id' => 'b2c3d4e5-f6a7-8901-bcde-fa2345678901',
                'name' => 'Ocean',
                'config' => [
                    'background' => '#f0f9ff',
                    'primary' => '#0ea5e9',
                    'secondary' => '#e0f2fe',
                    'text' => '#0c4a6e',
                    'accent' => '#0284c7',
                ],
                'is_default' => false,
            ],
            [
                'id' => 'c3d4e5f6-a7b8-9012-cdef-ab3456789012',
                'name' => 'Forest',
                'config' => [
                    'background' => '#f0fdf4',
                    'primary' => '#22c55e',
                    'secondary' => '#dcfce7',
                    'text' => '#14532d',
                    'accent' => '#16a34a',
                ],
                'is_default' => false,
            ],
            [
                'id' => 'd4e5f6a7-b8c9-0123-defa-bc4567890123',
                'name' => 'Sunset',
                'config' => [
                    'background' => '#fff7ed',
                    'primary' => '#f97316',
                    'secondary' => '#ffedd5',
                    'text' => '#7c2d12',
                    'accent' => '#ea580c',
                ],
                'is_default' => false,
            ],
            [
                'id' => 'e5f6a7b8-c9d0-1234-efbc-cd5678901234',
                'name' => 'Rainbow',
                'config' => [
                    'background' => '#fefce8',
                    'primary' => '#a855f7',
                    'secondary' => '#f3e8ff',
                    'text' => '#581c87',
                    'accent' => '#9333ea',
                ],
                'is_default' => false,
            ],
        ];

        foreach ($themes as $theme) {
            WheelTheme::updateOrCreate(
                ['id' => $theme['id']],
                $theme
            );
        }
    }
}
