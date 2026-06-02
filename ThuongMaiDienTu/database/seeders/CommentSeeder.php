<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Video;
use App\Models\Review;
use App\Models\VideoComment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dọn dẹp dữ liệu cũ trước khi seed
        Review::query()->delete();
        VideoComment::query()->delete();

        // Lấy danh sách users, products, videos
        $users = User::where('role_id', '!=', 1)->get(); // Users thường
        $admin = User::where('role_id', 1)->first() ?? User::first(); // Admin
        $products = Product::limit(10)->get();
        $videos = Video::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        // --- SEED PRODUCT REVIEWS ---
        $reviewContents = [
            5 => [
                'Sản phẩm dùng cực kỳ tốt, đóng gói cẩn thận, giao hàng siêu nhanh!',
                'Chất lượng tuyệt vời đúng như mô tả, nhân viên hỗ trợ nhiệt tình.',
                'Đáng tiền mua nha mọi người, máy chạy êm và tiết kiệm điện cực kỳ.',
                'Mua lần thứ 2 rồi vẫn rất hài lòng về dịch vụ của DienMayPro.',
            ],
            4 => [
                'Sản phẩm hoạt động tốt, thiết kế đẹp nhưng giao hàng hơi chậm tí.',
                'Chất lượng ổn áp so với tầm giá, sẽ tiếp tục ủng hộ shop.',
                'Dùng tốt, nhưng dây nguồn hơi ngắn, còn lại mọi thứ đều hoàn hảo.',
            ],
            3 => [
                'Chất lượng bình thường, không quá nổi bật.',
                'Sản phẩm tạm ổn, tuy nhiên hướng dẫn sử dụng hơi khó hiểu.',
                'Màu sắc thực tế hơi tối hơn trên ảnh, hoạt động ở mức trung bình.',
            ],
            2 => [
                'Sản phẩm dùng nhanh bị nóng, thiết kế chưa được tối ưu.',
                'Không hài lòng lắm, dịch vụ giao hàng thiếu phụ kiện đi kèm.',
            ],
            1 => [
                'Sản phẩm bị lỗi hỏng ngay khi mở hộp, yêu cầu đổi trả gấp!',
                'Hàng kém chất lượng, quảng cáo sai sự thật, không khuyên dùng!',
                'Thái độ nhân viên giao hàng quá tệ, sản phẩm móp méo.',
            ]
        ];

        foreach ($products as $index => $product) {
            // Mỗi sản phẩm tạo khoảng 2-4 đánh giá
            $numReviews = rand(2, 4);
            for ($i = 0; $i < $numReviews; $i++) {
                $user = $users->random();
                $rating = rand(1, 5);
                $contents = $reviewContents[$rating];
                $content = $contents[array_rand($contents)];
                
                // Giả lập một số đánh giá bị báo cáo vi phạm
                $isReported = (rand(1, 10) > 8);
                $reportCount = $isReported ? rand(1, 5) : 0;
                
                // Giả lập có đính kèm ảnh ở một số đánh giá
                $media = null;
                if (rand(1, 10) > 7) {
                    $media = ['https://picsum.photos/800/600?random=' . rand(1, 1000)];
                }

                $review = Review::create([
                    'product_id' => $product->product_id,
                    'user_id' => $user->user_id,
                    'author_name' => $user->full_name,
                    'rating' => $rating,
                    'content' => $content,
                    'media' => $media,
                    'is_approved' => 1,
                    'report_count' => $reportCount,
                ]);

                // 50% cơ hội Admin hoặc Manager sẽ trả lời phản hồi
                if (rand(1, 10) > 5) {
                    $adminReplyContents = [
                        'Dạ DienMayPro xin cảm ơn phản hồi của anh/chị. Chúc anh/chị có trải nghiệm tuyệt vời cùng sản phẩm ạ!',
                        'Dạ chào anh/chị, rất tiếc vì sự cố anh/chị gặp phải. Bộ phận kỹ thuật bên em sẽ liên hệ xử lý ngay cho mình ạ.',
                        'Dạ cảm ơn đánh giá của anh/chị, shop luôn cố gắng nâng cao chất lượng dịch vụ mỗi ngày ạ.',
                        'Cảm ơn bạn đã tin tưởng lựa chọn DienMayPro. Hy vọng được phục vụ bạn trong những đơn hàng tiếp theo!',
                    ];

                    $replyMedia = null;
                    if (rand(1, 10) > 6) {
                        $replyMedia = ['https://picsum.photos/800/600?random=' . rand(1001, 2000)];
                    }

                    Review::create([
                        'product_id' => $product->product_id,
                        'user_id' => $admin->user_id,
                        'author_name' => $admin->full_name,
                        'parent_id' => $review->id,
                        'rating' => 5,
                        'content' => $adminReplyContents[array_rand($adminReplyContents)],
                        'media' => $replyMedia,
                        'is_approved' => 1,
                        'report_count' => 0,
                    ]);
                }
            }
        }

        // --- SEED VIDEO CORNER COMMENTS ---
        $videoCommentContents = [
            'Video bổ ích quá, cảm ơn admin đã review chi tiết nhé!',
            'Mình đang phân vân không biết mua loại nào thì gặp ngay video này.',
            'iPhone 17 thiết kế đẹp xuất sắc thật sự, muốn rước một em ghê.',
            'Máy lọc nước nhà mình cũng đang dùng loại này, cực kỳ sạch và bền nha.',
            'Cách hướng dẫn tiết kiệm điện điều hòa rất thiết thực, tối nay áp dụng luôn.',
            'Cho mình xin giá của mẫu máy lạnh trong video với ạ!',
            'Video quay dựng chất lượng và chuyên nghiệp quá, like mạnh.',
        ];

        foreach ($videos as $video) {
            // Mỗi video tạo khoảng 2-3 bình luận
            $numComments = rand(2, 3);
            for ($i = 0; $i < $numComments; $i++) {
                $user = $users->random();
                $content = $videoCommentContents[array_rand($videoCommentContents)];
                
                // Giả lập bình luận bị báo cáo
                $isReported = (rand(1, 10) > 8);
                $reportCount = $isReported ? rand(1, 4) : 0;

                $comment = VideoComment::create([
                    'video_id' => $video->id,
                    'user_id' => $user->user_id,
                    'content' => $content,
                    'is_approved' => 1,
                    'report_count' => $reportCount,
                ]);

                // 40% cơ hội phản hồi bình luận video
                if (rand(1, 10) > 6) {
                    $videoReplyContents = [
                        'Dạ cảm ơn bạn đã quan tâm theo dõi góc video của DienMayPro ạ!',
                        'Dạ bạn có thể inbox trực tiếp cho page để nhận báo giá chi tiết nhất nhé ạ.',
                        'Dạ cảm ơn đóng góp ý kiến của bạn, shop sẽ ra thêm nhiều video hữu ích nữa ạ.',
                    ];

                    VideoComment::create([
                        'video_id' => $video->id,
                        'parent_id' => $comment->id,
                        'user_id' => $admin->user_id,
                        'content' => $videoReplyContents[array_rand($videoReplyContents)],
                        'is_approved' => 1,
                        'report_count' => 0,
                    ]);
                }
            }
        }
    }
}
