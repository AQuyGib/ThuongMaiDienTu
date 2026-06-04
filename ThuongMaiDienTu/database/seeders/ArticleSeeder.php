<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\RepairTicket;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role_id', 1)->first() ?? User::first();
        // Fallback to admin if customer not found
        $customer = User::where('role_id', 3)->first() ?? $admin;

        // Admin Article (Lookbook)
        Article::updateOrCreate(
            ['title' => 'Thiết kế phòng khách hiện đại với Smart TV OLED'],
            [
                'slug' => Str::slug('Thiết kế phòng khách hiện đại với Smart TV OLED'),
                'summary' => 'Biến phòng khách của bạn thành rạp chiếu phim tại gia với các mẫu Smart TV OLED thế hệ mới nhất.',
                'content' => '<p>Đây là nội dung Lookbook mẫu. Không gian giải trí gia đình ngày càng được chú trọng...</p>',
                'format_type' => 'lookbook',
                'author_id' => $admin->user_id,
                'author_type' => 'admin',
                'status' => 'approved',
                'published_at' => now(),
            ]
        );

        // Customer UGC Article (Pending)
        Article::updateOrCreate(
            ['title' => 'Mẹo tiết kiệm điện khi sử dụng điều hòa mùa hè'],
            [
                'slug' => Str::slug('Mẹo tiết kiệm điện khi sử dụng điều hòa mùa hè'),
                'summary' => 'Vài mẹo nhỏ mình tự đúc kết giúp hóa đơn tiền điện giảm đáng kể.',
                'content' => '<p>Mình xin chia sẻ một số kinh nghiệm sau...</p>',
                'format_type' => 'standard',
                'author_id' => $customer->user_id,
                'author_type' => 'customer',
                'status' => 'pending',
                'published_at' => null,
            ]
        );

        // Ecosystem Article
        $ticket = RepairTicket::first();
        Article::updateOrCreate(
            ['title' => 'Nhật ký Hồi sinh: Cứu sống tủ lạnh Side-by-side bị hỏng lốc'],
            [
                'slug' => Str::slug('Nhật ký Hồi sinh: Cứu sống tủ lạnh Side-by-side bị hỏng lốc'),
                'summary' => 'Case study thực tế từ kỹ thuật viên DIENMAY PRO. Sửa chữa giúp giảm E-waste.',
                'content' => '<p>Tình trạng ban đầu máy không lạnh. Chúng tôi đã tiến hành thay lốc chính hãng...</p>',
                'format_type' => 'storytelling',
                'related_ticket_id' => $ticket ? $ticket->ticket_id : null,
                'author_id' => $admin->user_id,
                'author_type' => 'admin',
                'status' => 'approved',
                'published_at' => now(),
            ]
        );
    }
}
