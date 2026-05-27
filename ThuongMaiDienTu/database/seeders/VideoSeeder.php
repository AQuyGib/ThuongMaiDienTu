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
                'video_path' => 'uploads/video/Iphone 17.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80',
                'duration' => '00:15',
                'category' => 'Điện thoại',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Top 10 máy lọc nước bán chạy nhất',
                'description' => 'Đánh giá và so sánh chi tiết top 10 dòng máy lọc nước gia đình tốt nhất hiện nay.',
                'video_path' => 'uploads/video/Top10maylocnc.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1585832770485-e289c21880ac?auto=format&fit=crop&w=800&q=80',
                'duration' => '00:20',
                'category' => 'Gia dụng',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Hướng dẫn sử dụng điều hòa tiết kiệm điện',
                'description' => 'Mẹo bật điều hòa mát lạnh suốt mùa hè mà vẫn cực kỳ tiết kiệm điện năng cho gia đình.',
                'video_path' => 'uploads/video/dieuhoa.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1621905252507-b354bc25edac?auto=format&fit=crop&w=800&q=80',
                'duration' => '00:12',
                'category' => 'Gia dụng',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Đánh giá Máy lạnh thế hệ mới',
                'description' => 'Trải nghiệm khả năng làm lạnh nhanh và khử mùi vượt trội của dòng máy lạnh cao cấp.',
                'video_path' => 'uploads/video/maylanh.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1527018601619-a508a2be00cd?auto=format&fit=crop&w=800&q=80',
                'duration' => '00:18',
                'category' => 'Gia dụng',
                'views' => 0,
                'likes' => 0,
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

            $localPath = public_path($v['video_path']);
            if (file_exists($localPath)) {
                $v['file_size'] = filesize($localPath);
                $v['mime_type'] = 'video/mp4';
            }

            Video::updateOrCreate(
                ['title' => $v['title']],
                $v
            );
        }
    }
}
