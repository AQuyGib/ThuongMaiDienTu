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
                'title' => 'Đánh giá Robot hút bụi Ecovacs Deebot X2 Omni',
                'description' => 'Trải nghiệm thực tế robot hút bụi lau nhà thông minh thế hệ mới với thiết kế vuông đột phá, lực hút mạnh mẽ và trạm sạc Omni tự động giặt giẻ bằng nước nóng.',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80',
                'video_path' => 'https://assets.mixkit.co/videos/preview/mixkit-smart-home-devices-controlled-by-voice-command-40019-large.mp4',
                'youtube_url' => 'https://www.youtube.com/embed/uD9nZf7-Xas',
                'duration' => '00:15',
                'category' => 'Gia dụng',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Trên tay Tivi Samsung Neo QLED 8K đỉnh cao hiển thị',
                'description' => 'Công nghệ Quantum Mini LED đem lại trải nghiệm độ sáng rực rỡ và độ tương phản tuyệt đối trên màn hình lớn sắc nét, đưa trải nghiệm xem phim gia đình lên tầm cao mới.',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&w=800&q=80',
                'video_path' => 'https://assets.mixkit.co/videos/preview/mixkit-modern-living-room-with-a-big-screen-tv-43285-large.mp4',
                'youtube_url' => 'https://www.youtube.com/embed/bSg1OOpL-fQ',
                'duration' => '00:23',
                'category' => 'Tivi & Soundbar',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Đánh giá Laptop Asus Zenbook Duo màn hình kép OLED',
                'description' => 'Trải nghiệm gõ phím, vẽ và làm việc đa nhiệm cực đỉnh trên chiếc laptop trang bị hai màn hình Lumina OLED 14 inch sắc nét, hiệu năng mạnh mẽ cho dân văn phòng.',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
                'video_path' => 'https://assets.mixkit.co/videos/preview/mixkit-hands-of-a-man-typing-on-a-laptop-42352-large.mp4',
                'youtube_url' => 'https://www.youtube.com/embed/2u32c7pZ28A',
                'duration' => '00:10',
                'category' => 'Laptop & Gear',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Review Tai nghe chụp tai Sony WH-1000XM5 chống ồn tốt nhất',
                'description' => 'Đánh giá chi tiết chất âm, thời lượng pin 30 tiếng và khả năng chống ồn chủ động ANC đỉnh cao của Sony WH-1000XM5 trong các môi trường tiếng ồn phức tạp.',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1545173168-9f18d85f9ea6?auto=format&fit=crop&w=800&q=80',
                'video_path' => 'https://assets.mixkit.co/videos/preview/mixkit-woman-loading-the-washing-machine-41560-large.mp4',
                'youtube_url' => 'https://www.youtube.com/embed/5y0C2w2Y07w',
                'duration' => '00:18',
                'category' => 'Phụ kiện',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ],
            [
                'title' => 'Trải nghiệm Tủ lạnh thông minh Samsung Family Hub',
                'description' => 'Khám phá chiếc tủ lạnh trang bị màn hình cảm ứng 21.5 inch siêu lớn. Hỗ trợ xem camera trong tủ lạnh từ xa, quản lý hạn sử dụng thực phẩm và phát nhạc giải trí.',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=800&q=80',
                'video_path' => 'https://assets.mixkit.co/videos/preview/mixkit-woman-opening-a-refrigerator-in-the-kitchen-41561-large.mp4',
                'youtube_url' => 'https://www.youtube.com/embed/Pj152d27YtI',
                'duration' => '00:26',
                'category' => 'Gia dụng',
                'views' => 0,
                'likes' => 0,
                'status' => 'published',
                'uploaded_by_admin' => true,
                'user_id' => $adminId,
                'published_at' => now(),
            ]
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
