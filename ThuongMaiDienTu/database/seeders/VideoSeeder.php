<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Seed sample video data into the videos table.
     */
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first() ?? User::first();
        $adminId = $admin ? $admin->user_id : 1;

        $videos = [
            [
                'title' => 'Đánh giá iPhone 17 - Siêu phẩm tương lai',
                'description' => 'Review chi tiết thiết kế, hiệu năng và camera của iPhone 17 thế hệ mới.',
                'youtube_url' => 'https://www.youtube.com/watch?v=k3Vz3eYm0xY',
                'video_path' => null,
                'thumbnail_path' => null,
                'duration' => '15:20',
                'category' => 'Điện thoại',
                'views' => 1250,
                'likes' => 340,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Top 10 máy lọc nước bán chạy nhất',
                'description' => 'Đánh giá và so sánh chi tiết top 10 dòng máy lọc nước gia đình tốt nhất hiện nay.',
                'youtube_url' => 'https://www.youtube.com/watch?v=W0vV5k9K5kI',
                'video_path' => null,
                'thumbnail_path' => null,
                'duration' => '10:45',
                'category' => 'Gia dụng',
                'views' => 840,
                'likes' => 120,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Hướng dẫn sử dụng điều hòa tiết kiệm điện',
                'description' => 'Mẹo bật điều hòa mát lạnh suốt mùa hè mà vẫn cực kỳ tiết kiệm điện năng cho gia đình.',
                'youtube_url' => 'https://www.youtube.com/watch?v=3mZpXJ6oN0w',
                'video_path' => null,
                'thumbnail_path' => null,
                'duration' => '08:12',
                'category' => 'Gia dụng',
                'views' => 2100,
                'likes' => 450,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Đánh giá Máy lạnh thế hệ mới',
                'description' => 'Trải nghiệm khả năng làm lạnh nhanh và khử mùi vượt trội của dòng máy lạnh cao cấp.',
                'youtube_url' => 'https://www.youtube.com/watch?v=D-w-w7G_H_A',
                'video_path' => null,
                'thumbnail_path' => null,
                'duration' => '12:18',
                'category' => 'Gia dụng',
                'views' => 620,
                'likes' => 95,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
        ];

        foreach ($videos as $v) {
            $cat = \App\Models\Category::where('name', 'like', '%' . $v['category'] . '%')->first();
            if ($cat) {
                $v['category_id'] = $cat->category_id;
                $v['category'] = $cat->name;
            }

            Video::updateOrCreate(
                ['title' => $v['title']],
                $v
            );
        }
    }
}
