import re

def main():
    # 1. Update BaoCao_ChiTiet_DuAn.md
    main_path = "d:/repogist/ThuongMaiDienTu/BaoCao_ChiTiet_DuAn.md"
    print(f"Reading {main_path}...")
    with open(main_path, "r", encoding="utf-8") as f:
        main_content = f.read()

    # Define the replacements for the 6 mockups
    mockup_replacements = {
        r"!\[Giao diện Đăng nhập & Xác thực 2FA OTP\]\(images/login_2fa_ui\.png\)": 
        '![Giao diện Đăng nhập & Xác thực 2FA OTP](images/login_2fa_ui.png)\n\n*Hình 4.1: Bản vẽ thiết kế giao diện Đăng nhập & Xác thực 2 lớp (2FA OTP).*',
        
        r"!\[Giao diện Giỏ hàng & Thanh toán VietQR\]\(images/cart_payment_ui\.png\)":
        '![Giao diện Giỏ hàng & Thanh toán VietQR](images/cart_payment_ui.png)\n\n*Hình 4.2: Bản vẽ thiết kế giao diện Giỏ hàng & Thanh toán tự động qua mã VietQR.*',
        
        r"!\[Giao diện Đặt lịch sửa chữa & Live Stepper\]\(images/repair_portal_ui\.png\)":
        '![Giao diện Đặt lịch sửa chữa & Live Stepper](images/repair_portal_ui.png)\n\n*Hình 4.3: Bản vẽ thiết kế giao diện cổng đăng ký sửa chữa trực tuyến và thanh trạng thái Live Stepper.*',
        
        r"!\[Giao diện Vòng quay may mắn & Tích điểm\]\(images/lucky_wheel_ui\.png\)":
        '![Giao diện Vòng quay may mắn & Tích điểm](images/lucky_wheel_ui.png)\n\n*Hình 4.4: Bản vẽ thiết kế giao diện Vòng quay may mắn & Quản lý điểm Loyalty.*',
        
        r"!\[Giao diện AI Chatbot tư vấn khách hàng\]\(images/ai_chatbot_ui\.png\)":
        '![Giao diện AI Chatbot tư vấn khách hàng](images/ai_chatbot_ui.png)\n\n*Hình 4.5: Bản vẽ thiết kế giao diện cửa sổ Trợ lý ảo AI Chatbot tư vấn RAG.*',
        
        r"!\[Giao diện Admin Dashboard & AI Audit Order\]\(images/admin_dashboard_ui\.png\)":
        '![Giao diện Admin Dashboard & AI Audit Order](images/admin_dashboard_ui.png)\n\n*Hình 4.6: Bản vẽ thiết kế giao diện Admin Dashboard và AI kiểm duyệt đơn hàng tự động.*'
    }

    for pattern, repl in mockup_replacements.items():
        # check if already replaced
        if repl.split('\n\n')[-1] not in main_content:
            main_content = re.sub(pattern, repl, main_content)

    with open(main_path, "w", encoding="utf-8") as f:
        f.write(main_content)
    print("Updated BaoCao_ChiTiet_DuAn.md mockup captions.")

    # 2. Update BaoCao_DacTa_ChiTiet_ChucNang.md
    appendix_path = "d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md"
    print(f"Reading {appendix_path}...")
    with open(appendix_path, "r", encoding="utf-8") as f:
        appendix_content = f.read()

    # We will split by section header ## 7.X.
    sections = re.split(r'^(## 7\.\d+.*)$', appendix_content, flags=re.MULTILINE)

    new_sections = []
    new_sections.append(sections[0]) # everything before the first section

    for i in range(1, len(sections), 2):
        heading = sections[i]
        body = sections[i+1] if i+1 < len(sections) else ""
        
        # Extract number and title
        m = re.search(r'7\.(\d+)\.\s*(.*)', heading)
        if m:
            num = m.group(1)
            title = m.group(2).strip()
            
            # Clean title
            clean_title = title.replace("/", " hoặc ")
            
            # Replace the three image tags
            ui_pattern = rf'!\[Cấu trúc giao diện\]\(images/ui_layout_{num}\.png\)'
            ui_repl = f'![Cấu trúc giao diện](images/ui_layout_{num}.png)\n\n*Hình 7.{num}.1: Bản vẽ thiết kế bố cục giao diện (UI Layout) của chức năng {title}.*'
            
            uc_pattern = rf'!\[Sơ đồ Use-case chức năng\]\(images/use_case_{num}\.png\)'
            uc_repl = f'![Sơ đồ Use-case chức năng](images/use_case_{num}.png)\n\n*Hình 7.{num}.2: Sơ đồ Use-case mô tả các tác nhân tương tác với chức năng {title}.*'
            
            act_pattern = rf'!\[Sơ đồ [hH]oạt động(?: (?:Diagram|\(Activity Diagram\)))?\]\(images/activity_{num}\.png\)'
            act_repl = f'![Sơ đồ hoạt động](images/activity_{num}.png)\n\n*Hình 7.{num}.3: Sơ đồ hoạt động (Activity Diagram) thể hiện quy trình xử lý của chức năng {title}.*'
            
            # Replace only if not already captioned (defensive checking)
            if f"*Hình 7.{num}.1" not in body:
                body = re.sub(ui_pattern, ui_repl, body)
            if f"*Hình 7.{num}.2" not in body:
                body = re.sub(uc_pattern, uc_repl, body)
            if f"*Hình 7.{num}.3" not in body:
                body = re.sub(act_pattern, act_repl, body)
                
        new_sections.append(heading)
        new_sections.append(body)

    new_appendix_content = "".join(new_sections)
    with open(appendix_path, "w", encoding="utf-8") as f:
        f.write(new_appendix_content)
    print("Updated BaoCao_DacTa_ChiTiet_ChucNang.md image captions.")

if __name__ == "__main__":
    main()
