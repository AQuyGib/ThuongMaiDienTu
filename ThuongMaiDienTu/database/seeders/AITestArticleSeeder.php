<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AITestArticleSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy khách hàng làm tác giả
        $customer = User::where('role_id', 3)->first();
        if (!$customer) {
            $this->command->warn('AITestArticleSeeder: Không có khách hàng nào (role_id=3). Sẽ lấy tài khoản đầu tiên làm tác giả.');
            $customer = User::first();
            if (!$customer) {
                $this->command->error('AITestArticleSeeder: Không tìm thấy người dùng nào trong database.');
                return;
            }
        }

        $articlesData = [
            [
                'title' => 'Đánh giá chi tiết thời lượng pin iPhone 15 Pro Max sau 6 tháng sử dụng thực tế',
                'summary' => 'Sau 6 tháng trải nghiệm làm máy chính, mình xin đánh giá chi tiết thời lượng pin của iPhone 15 Pro Max khi sử dụng mạng xã hội, chơi game và xem phim.',
                'content' => '<p>iPhone 15 Pro Max sở hữu dung lượng pin khá lớn và con chip A17 Pro tối ưu điện năng tốt. Khi lướt Facebook bằng Wifi trong 1 giờ chỉ mất khoảng 8% pin...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'approved',
                'ai_quality_score' => 88,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 88,
                    'recommended_reward_points' => 50,
                    'is_spam' => false,
                    'has_sensitive_content' => false,
                    'plagiarism_probability' => 8,
                    'moderation_verdict' => 'approved',
                    'moderation_reason' => 'Bài viết chất lượng rất cao, có hình ảnh và so sánh thông số chi tiết.',
                    'tags' => ['#iphone15', '#review', '#pin'],
                    'seo' => [
                        'title_suggestion' => 'Đánh giá pin iPhone 15 Pro Max sau 6 tháng thực tế',
                        'meta_description_suggestion' => 'Đánh giá chi tiết pin iPhone 15 Pro Max sau 6 tháng sử dụng thực tế. Xem ngay các mẹo tối ưu pin.',
                        'keywords_analysis' => [['keyword' => 'pin iphone 15', 'count' => 5, 'density' => '2.5%']],
                        'seo_score' => 85,
                        'optimization_tips' => ['Thêm hình ảnh thực tế chụp biểu đồ pin']
                    ]
                ],
                'tags' => ['#iphone15', '#review', '#pin'],
                'seo_title' => 'Đánh giá pin iPhone 15 Pro Max sau 6 tháng thực tế',
                'seo_description' => 'Đánh giá chi tiết pin iPhone 15 Pro Max sau 6 tháng sử dụng thực tế. Xem ngay các mẹo tối ưu pin.',
                'seo_score' => 85,
            ],
            [
                'title' => 'Kinh nghiệm chọn mua laptop văn phòng mỏng nhẹ dưới 15 triệu đồng năm 2026',
                'summary' => 'Hướng dẫn chi tiết cách chọn cấu hình CPU, dung lượng RAM, ổ cứng SSD và thiết kế mỏng nhẹ cho nhu cầu học tập, văn phòng.',
                'content' => '<p>Dưới 15 triệu, bạn hoàn toàn có thể sở hữu laptop chạy chip Ryzen 5 hoặc Core i5 đời mới, màn hình IPS Full HD sắc nét và bộ nhớ RAM tối thiểu 8GB để đa nhiệm mượt mà...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'approved',
                'ai_quality_score' => 81,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 81,
                    'recommended_reward_points' => 35,
                    'is_spam' => false,
                    'has_sensitive_content' => false,
                    'plagiarism_probability' => 12,
                    'moderation_verdict' => 'approved',
                    'moderation_reason' => 'Bài viết viết mạch lạc, bố cục rõ ràng phù hợp độc giả phổ thông.',
                    'tags' => ['#laptop', '#vanphong', '#tuvan'],
                    'seo' => [
                        'title_suggestion' => 'Chọn mua laptop văn phòng dưới 15 triệu đồng mỏng nhẹ',
                        'meta_description_suggestion' => 'Mẹo chọn cấu hình laptop văn phòng mỏng nhẹ dưới 15 triệu đồng tốt nhất cho học sinh, sinh viên và nhân viên.',
                        'keywords_analysis' => [['keyword' => 'laptop văn phòng', 'count' => 4, 'density' => '1.8%']],
                        'seo_score' => 78,
                        'optimization_tips' => ['Đặt thêm liên kết tới các dòng máy phổ biến']
                    ]
                ],
                'tags' => ['#laptop', '#vanphong', '#tuvan'],
                'seo_title' => 'Chọn mua laptop văn phòng dưới 15 triệu đồng mỏng nhẹ',
                'seo_description' => 'Mẹo chọn cấu hình laptop văn phòng mỏng nhẹ dưới 15 triệu đồng tốt nhất cho học sinh, sinh viên và nhân viên.',
                'seo_score' => 78,
            ],
            [
                'title' => 'Chia sẻ cách bẻ khóa các phần mềm đồ họa thiết kế nổi tiếng cực kỳ dễ dàng',
                'summary' => 'Hướng dẫn download và sử dụng công cụ crack để kích hoạt bản quyền miễn phí không tốn một xu.',
                'content' => '<p>Trong bài viết này, mình sẽ cung cấp cho các bạn link tải file kích hoạt bản quyền kèm video hướng dẫn crack chi tiết các ứng dụng chỉnh sửa ảnh...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'flagged',
                'ai_quality_score' => 60,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 60,
                    'recommended_reward_points' => 10,
                    'is_spam' => false,
                    'has_sensitive_content' => true,
                    'plagiarism_probability' => 45,
                    'moderation_verdict' => 'flagged',
                    'moderation_reason' => 'Nội dung chia sẻ các phương thức bẻ khóa phần mềm (vi phạm bản quyền chính sách). Cần admin kiểm tra lại.',
                    'tags' => ['#phầnmềm', '#thủthuật', '#crack'],
                    'seo' => [
                        'title_suggestion' => 'Chia sẻ cách bẻ khóa phần mềm đồ họa thiết kế',
                        'meta_description_suggestion' => 'Hướng dẫn tìm hiểu về bản quyền phần mềm thiết kế và các rủi ro liên quan đến bảo mật.',
                        'keywords_analysis' => [],
                        'seo_score' => 50,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#phầnmềm', '#thủthuật', '#crack'],
                'seo_title' => 'Chia sẻ cách bẻ khóa phần mềm đồ họa thiết kế',
                'seo_description' => 'Hướng dẫn tìm hiểu về bản quyền phần mềm thiết kế và các rủi ro liên quan đến bảo mật.',
                'seo_score' => 50,
            ],
            [
                'title' => 'MUA NGAY ĐIỆN THOẠI IP CŨ CHÍNH HÃNG UY TÍN TẠI TRANG WEB LỪA ĐẢO SPAM',
                'summary' => 'Click vào link ngay để mua sản phẩm giá chỉ 100k, uy tín chất lượng số 1 Việt Nam, hàng rác spam spam.',
                'content' => '<p>Chúng tôi chuyên cung cấp điện thoại cũ giá siêu rẻ. Bấm ngay vào đường link sau để được nhận ưu đãi sốc, link rác quảng cáo bẩn 12345...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'rejected',
                'ai_quality_score' => 15,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 15,
                    'recommended_reward_points' => 0,
                    'is_spam' => true,
                    'has_sensitive_content' => true,
                    'plagiarism_probability' => 95,
                    'moderation_verdict' => 'rejected',
                    'moderation_reason' => 'Bài viết chứa nội dung spam quảng cáo bẩn và chứa nhiều từ khóa viết hoa vô nghĩa.',
                    'tags' => ['#spam', '#quangcao', '#rac'],
                    'seo' => [
                        'title_suggestion' => 'Điện thoại cũ giá rẻ',
                        'meta_description_suggestion' => 'Nội dung spam quảng cáo không chuẩn SEO.',
                        'keywords_analysis' => [],
                        'seo_score' => 10,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#spam', '#quangcao', '#rac'],
                'seo_title' => 'Điện thoại cũ giá rẻ',
                'seo_description' => 'Nội dung spam quảng cáo không chuẩn SEO.',
                'seo_score' => 10,
            ],
            [
                'title' => 'Cách vệ sinh máy giặt cửa trước cực nhanh chỉ với giấm ăn và baking soda',
                'summary' => 'Hướng dẫn bạn từng bước tự vệ sinh lồng giặt máy giặt lồng ngang định kỳ tại nhà không cần gọi thợ.',
                'content' => '<p>Máy giặt cửa trước sau một thời gian sử dụng sẽ bị bám cặn bột giặt, xơ vải và nấm mốc gây mùi hôi khó chịu. Để khắc phục, bạn chỉ cần chuẩn bị 1 bát giấm ăn và 100g bột baking soda...</p>',
                'status' => 'pending',
                'ai_checked' => 0,
                'ai_moderation_verdict' => null,
                'ai_quality_score' => null,
                'reward_points_awarded' => 0,
                'ai_analysis' => null,
                'tags' => null,
                'seo_title' => null,
                'seo_description' => null,
                'seo_score' => null,
            ],
            [
                'title' => 'Top 5 thói quen sai lầm khiến tủ lạnh nhà bạn nhanh hỏng và tốn điện gấp đôi',
                'summary' => 'Tổng hợp các thói quen sử dụng tủ lạnh sai cách mà hầu như gia đình nào cũng mắc phải hàng ngày.',
                'content' => '<p>Tủ lạnh chạy liên tục 24/24 nên rất dễ gây tiêu hao điện năng lớn nếu dùng sai cách. Các lỗi phổ biến gồm: mở tủ lạnh quá lâu, để thức ăn nóng trực tiếp vào tủ, nhồi nhét quá nhiều thực phẩm...</p>',
                'status' => 'approved',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'approved',
                'ai_quality_score' => 85,
                'reward_points_awarded' => 30,
                'ai_analysis' => [
                    'quality_score' => 85,
                    'recommended_reward_points' => 30,
                    'is_spam' => false,
                    'has_sensitive_content' => false,
                    'plagiarism_probability' => 5,
                    'moderation_verdict' => 'approved',
                    'moderation_reason' => 'Nội dung bài viết bổ ích, hướng dẫn thiết thực cho gia đình.',
                    'tags' => ['#tulanh', '#meovat', '#tietkiem'],
                    'seo' => [
                        'title_suggestion' => 'Sai lầm khiến tủ lạnh nhanh hỏng và tốn điện',
                        'meta_description_suggestion' => 'Xem ngay 5 thói quen sử dụng tủ lạnh sai cách khiến thiết bị nhanh hỏng và tiêu tốn nhiều điện năng.',
                        'keywords_analysis' => [['keyword' => 'dùng tủ lạnh', 'count' => 3, 'density' => '2.0%']],
                        'seo_score' => 80,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#tulanh', '#meovat', '#tietkiem'],
                'seo_title' => 'Sai lầm khiến tủ lạnh nhanh hỏng và tốn điện',
                'seo_description' => 'Xem ngay 5 thói quen sử dụng tủ lạnh sai cách khiến thiết bị nhanh hỏng và tiêu tốn nhiều điện năng.',
                'seo_score' => 80,
            ],
            [
                'title' => 'Đánh giá camera của Samsung Galaxy S24 Ultra sau một tháng chụp ảnh đường phố',
                'summary' => 'Trải nghiệm chụp ảnh đường phố (street photography) bằng camera zoom 5x và 10x mới trên Galaxy S24 Ultra.',
                'content' => '<p>Galaxy S24 Ultra mang đến nhiều nâng cấp về cảm biến camera chính và camera telephoto. Trong điều kiện đủ sáng, máy bắt nét cực nhanh và tái tạo màu sắc rất trung thực...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'approved',
                'ai_quality_score' => 84,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 84,
                    'recommended_reward_points' => 45,
                    'is_spam' => false,
                    'has_sensitive_content' => false,
                    'plagiarism_probability' => 6,
                    'moderation_verdict' => 'approved',
                    'moderation_reason' => 'Bài viết chất lượng tốt, chia sẻ trải nghiệm thực tế sinh động.',
                    'tags' => ['#samsung', '#s24ultra', '#camera'],
                    'seo' => [
                        'title_suggestion' => 'Đánh giá camera Galaxy S24 Ultra chụp ảnh đường phố',
                        'meta_description_suggestion' => 'Đánh giá chi tiết camera Samsung Galaxy S24 Ultra sau 1 tháng trải nghiệm chụp ảnh đường phố thực tế.',
                        'keywords_analysis' => [['keyword' => 'camera s24 ultra', 'count' => 4, 'density' => '2.1%']],
                        'seo_score' => 82,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#samsung', '#s24ultra', '#camera'],
                'seo_title' => 'Đánh giá camera Galaxy S24 Ultra chụp ảnh đường phố',
                'seo_description' => 'Đánh giá chi tiết camera Samsung Galaxy S24 Ultra sau 1 tháng trải nghiệm chụp ảnh đường phố thực tế.',
                'seo_score' => 82,
            ],
            [
                'title' => 'Mẹo bảo quản và nâng cao tuổi thọ của pin máy hút bụi không dây cầm tay',
                'summary' => 'Làm thế nào để viên pin của máy hút bụi Dyson hoặc Xiaomi của bạn không bị chai sau một năm sử dụng.',
                'content' => '<p>Máy hút bụi cầm tay không dây rất tiện lợi nhưng pin của chúng thường bị suy giảm dung lượng nhanh chóng nếu không sạc xả đúng cách. Hãy tránh hút ở chế độ Max quá lâu...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'approved',
                'ai_quality_score' => 80,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 80,
                    'recommended_reward_points' => 30,
                    'is_spam' => false,
                    'has_sensitive_content' => false,
                    'plagiarism_probability' => 10,
                    'moderation_verdict' => 'approved',
                    'moderation_reason' => 'Bài viết cung cấp thông tin hữu ích về kỹ thuật bảo quản thiết bị gia đình.',
                    'tags' => ['#mayhutbui', '#dyson', '#pin'],
                    'seo' => [
                        'title_suggestion' => 'Nâng cao tuổi thọ pin máy hút bụi cầm tay',
                        'meta_description_suggestion' => 'Hướng dẫn chi tiết cách sạc và sử dụng để bảo vệ pin máy hút bụi không dây cầm tay bền lâu.',
                        'keywords_analysis' => [['keyword' => 'pin máy hút bụi', 'count' => 3, 'density' => '1.5%']],
                        'seo_score' => 75,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#mayhutbui', '#dyson', '#pin'],
                'seo_title' => 'Nâng cao tuổi thọ pin máy hút bụi cầm tay',
                'seo_description' => 'Hướng dẫn chi tiết cách sạc và sử dụng để bảo vệ pin máy hút bụi không dây cầm tay bền lâu.',
                'seo_score' => 75,
            ],
            [
                'title' => 'Cách tự cài đặt phần mềm giám sát và theo dõi điện thoại người khác từ xa',
                'summary' => 'Hướng dẫn cài đặt ứng dụng theo dõi ngầm tin nhắn, cuộc gọi và định vị GPS của người khác.',
                'content' => '<p>Hiện nay có một số ứng dụng cho phép bạn theo dõi hoạt động của điện thoại mục tiêu mà người dùng không hề hay biết. Bạn cần tải file APK về máy nạn nhân...</p>',
                'status' => 'pending',
                'ai_checked' => 1,
                'ai_moderation_verdict' => 'flagged',
                'ai_quality_score' => 55,
                'reward_points_awarded' => 0,
                'ai_analysis' => [
                    'quality_score' => 55,
                    'recommended_reward_points' => 15,
                    'is_spam' => false,
                    'has_sensitive_content' => true,
                    'plagiarism_probability' => 38,
                    'moderation_verdict' => 'flagged',
                    'moderation_reason' => 'Nội dung chia sẻ các phương pháp xâm nhập và theo dõi quyền riêng tư cá nhân bất hợp pháp.',
                    'tags' => ['#theodoi', '#phầnmềm', '#bao-mat'],
                    'seo' => [
                        'title_suggestion' => 'Tìm hiểu phần mềm giám sát điện thoại',
                        'meta_description_suggestion' => 'Các rủi ro bảo mật liên quan đến ứng dụng theo dõi điện thoại từ xa.',
                        'keywords_analysis' => [],
                        'seo_score' => 45,
                        'optimization_tips' => []
                    ]
                ],
                'tags' => ['#theodoi', '#phầnmềm', '#bao-mat'],
                'seo_title' => 'Tìm hiểu phần mềm giám sát điện thoại',
                'seo_description' => 'Các rủi ro bảo mật liên quan đến ứng dụng theo dõi điện thoại từ xa.',
                'seo_score' => 45,
            ],
            [
                'title' => 'Những mẫu tivi giá rẻ đáng mua nhất cho phòng trọ sinh viên dịp cuối năm nay',
                'summary' => 'Tổng hợp các mẫu tivi kích thước 32 inch đến 43 inch giá chỉ từ 3 triệu đồng chạy hệ điều hành thông minh.',
                'content' => '<p>Dành cho các bạn sinh viên hoặc người đi làm ở phòng trọ cần tivi giải trí nhỏ gọn, các hãng Coocaa, Casper hay TCL đều có những mẫu sản phẩm vô cùng rẻ...</p>',
                'status' => 'pending',
                'ai_checked' => 0,
                'ai_moderation_verdict' => null,
                'ai_quality_score' => null,
                'reward_points_awarded' => 0,
                'ai_analysis' => null,
                'tags' => null,
                'seo_title' => null,
                'seo_description' => null,
                'seo_score' => null,
            ]
        ];

        $titles = array_column($articlesData, 'title');
        Article::whereIn('title', $titles)->forceDelete();

        $count = 0;
        foreach ($articlesData as $data) {
            $data['slug'] = Str::slug($data['title']) . '-' . time();
            $data['author_id'] = $customer->user_id;
            $data['author_type'] = 'customer';
            $data['format_type'] = 'standard';
            $data['created_at'] = now()->subMinutes(rand(10, 1000));
            $data['updated_at'] = now();

            Article::create($data);
            $count++;
        }

        $this->command->info("AITestArticleSeeder: Đã tạo thành công {$count} bài viết mẫu thử nghiệm (bao gồm các trạng thái duyệt, chờ duyệt đạt chuẩn AI, bị gắn cờ, và vi phạm).");
    }
}
