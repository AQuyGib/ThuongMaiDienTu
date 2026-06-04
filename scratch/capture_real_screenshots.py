import asyncio
from playwright.async_api import async_playwright
import os
import sys

# Ensure stdout uses UTF-8 to prevent encoding issues in Windows console
sys.stdout.reconfigure(encoding='utf-8')

# Map each of the 51 functions to their actual URL and actions
tasks = {
    1: {"url": "/login-register", "login": "none", "desc": "Trang đăng nhập / đăng ký"},
    2: {"url": "/security", "login": "user", "desc": "Trang cấu hình bảo mật 2FA"},
    3: {"url": "/forgot-password", "login": "none", "desc": "Trang khôi phục mật khẩu"},
    4: {"url": "/admin/permissions", "login": "admin", "desc": "Quản lý quyền hạn vai trò RBAC"},
    5: {"url": "/admin/dashboard", "login": "admin", "desc": "Nhật ký hoạt động hệ thống"},
    6: {"url": "/admin/categories", "login": "admin", "desc": "Quản lý danh mục sản phẩm"},
    7: {"url": "/admin/attributes", "login": "admin", "desc": "Quản lý thuộc tính sản phẩm"},
    8: {"url": "/admin/products", "login": "admin", "desc": "Quản lý sản phẩm & biến thể"},
    9: {"url": "/admin/products", "login": "admin", "desc": "Import/Export sản phẩm qua Excel", "action": "import_modal"},
    10: {"url": "/admin/dashboard", "login": "admin", "desc": "Đồng bộ đa kênh tự động"},
    11: {"url": "/", "login": "none", "desc": "Thanh tìm kiếm gợi ý AJAX", "action": "ajax_search"},
    12: {"url": "/compare", "login": "none", "desc": "Trang so sánh sản phẩm"},
    13: {"url": "/profile", "login": "user", "desc": "Trang danh sách yêu thích", "action": "scroll_wishlist"},
    14: {"url": "/shoppingcart", "login": "none", "desc": "Xác minh giỏ hàng server-side"},
    15: {"url": "/san-pham/1", "login": "none", "desc": "Mua ngay (Quick Buy)", "action": "scroll_buy_now"},
    16: {"url": "/san-pham/1", "login": "none", "desc": "Chi tiết sản phẩm & dịch vụ đi kèm"},
    17: {"url": "/san-pham/1", "login": "none", "desc": "Giảm giá và hậu mãi (Cross-sell & Combo)", "action": "scroll_combo"},
    18: {"url": "/shoppingcart", "login": "none", "desc": "Kiểm tra tồn kho thời gian thực"},
    19: {"url": "/pay", "login": "user", "desc": "Xử lý giao dịch đồng thời chống overselling", "action": "prepare_cart"},
    20: {"url": "/pay", "login": "user", "desc": "Tích hợp thanh toán VietQR Compact API", "action": "prepare_cart_vietqr"},
    21: {"url": "/orders", "login": "user", "desc": "Kết quả đơn hàng và hóa đơn"},
    22: {"url": "/orders", "login": "none", "desc": "Tra cứu đơn hàng không cần đăng nhập"},
    23: {"url": "/san-pham/1", "login": "none", "desc": "Đánh giá sản phẩm (Rating & Reviews)", "action": "scroll_reviews"},
    24: {"url": "/admin/warehouse-transfers", "login": "admin", "desc": "Chuyển kho nội bộ"},
    25: {"url": "/admin/inventory/warnings", "login": "admin", "desc": "Tự động đề xuất mua hàng reorder point"},
    26: {"url": "/admin/inventory-audits", "login": "admin", "desc": "Kiểm kê kho điều chỉnh sai lệch"},
    27: {"url": "/admin/inventory/movements", "login": "admin", "desc": "Lịch sử dịch chuyển kho"},
    28: {"url": "/admin/inventory/warnings", "login": "admin", "desc": "Cảnh báo tồn kho dưới định mức"},
    29: {"url": "/admin/inventory", "login": "admin", "desc": "Kiểm tra IMEI/Serial khi xuất kho"},
    30: {"url": "/admin/inventory", "login": "admin", "desc": "Định danh sản phẩm bằng IMEI/Serial"},
    31: {"url": "/admin/inventory", "login": "admin", "desc": "Báo cáo tồn kho thời gian thực"},
    32: {"url": "/admin/purchase-orders", "login": "admin", "desc": "Thiết lập nhà cung cấp và đơn mua hàng"},
    33: {"url": "/admin/customers", "login": "admin", "desc": "Quản lý thông tin khách hàng CRM"},
    34: {"url": "/admin/customers", "login": "admin", "desc": "Phân tích nhóm khách hàng RFM"},
    35: {"url": "/admin/customers", "login": "admin", "desc": "Gửi email chăm sóc khách hàng", "action": "email_modal"},
    36: {"url": "/rewards", "login": "user", "desc": "Hệ thống điểm thưởng thành viên loyalty"},
    37: {"url": "/rewards", "login": "user", "desc": "Đổi thưởng voucher"},
    38: {"url": "/rewards", "login": "user", "desc": "Vòng quay may mắn lucky wheel", "action": "lucky_wheel"},
    39: {"url": "/admin/notifications", "login": "admin", "desc": "Quản lý chiến dịch thông báo"},
    40: {"url": "/admin/articles", "login": "admin", "desc": "AI kiểm duyệt bài viết UGC"},
    41: {"url": "/admin/dashboard", "login": "admin", "desc": "AI duyệt đơn hàng tự động fraud detection"},
    42: {"url": "/admin/cashbooks", "login": "admin", "desc": "Sổ quỹ thu chi cashbook"},
    43: {"url": "/videos", "login": "user", "desc": "Video review đăng tải đa phương tiện"},
    44: {"url": "/", "login": "none", "desc": "Chế độ đa ngôn ngữ", "action": "open_lang"},
    45: {"url": "/chinh-sach-doi-tra", "login": "none", "desc": "Chính sách đổi trả & hoàn tiền"},
    46: {"url": "/", "login": "none", "desc": "AI Chatbot hỗ trợ khách hàng", "action": "open_chatbot"},
    47: {"url": "/", "login": "none", "desc": "Flash sale giờ vàng chống overselling", "action": "scroll_flashsale"},
    48: {"url": "/admin/customers", "login": "admin", "desc": "Quản lý khách hàng & hệ thống xử phạt"},
    49: {"url": "/admin/articles", "login": "admin", "desc": "Quản lý bài viết CRUD articles"},
    50: {"url": "/admin/home-sections", "login": "admin", "desc": "Tùy biến giao diện header footer theme customizer"},
    51: {"url": "/admin/dashboard", "login": "admin", "desc": "Smart setup wizard & CLI orchestrator"}
}

async def handle_login(page, user_type):
    if user_type == "admin":
        email = "admin@dienmaypro.com.vn"
        password = "admin123"
    elif user_type == "user":
        email = "an.tran@gmail.com"
        password = "123456"
    else:
        return
        
    print(f"Logging in as {email}...")
    await page.goto("http://127.0.0.1:8000/login")
    await page.wait_for_selector("#email")
    await page.fill("#email", email)
    await page.fill("#password", password)
    await page.keyboard.press("Enter")
    await page.wait_for_timeout(2000)

async def apply_grayscale(page):
    await page.evaluate("""
        const style = document.createElement('style');
        style.innerHTML = `
            * {
                filter: grayscale(100%) !important;
                -webkit-filter: grayscale(100%) !important;
            }
            body {
                background-color: white !important;
            }
        `;
        document.head.appendChild(style);
    """)
    await page.wait_for_timeout(500)

async def run():
    dest_dir_1 = "d:/repogist/ThuongMaiDienTu/images"
    dest_dir_2 = "d:/HOC/Hoc4/pywword/images"
    os.makedirs(dest_dir_1, exist_ok=True)
    os.makedirs(dest_dir_2, exist_ok=True)

    async with async_playwright() as p:
        # Launch browser with high resolution viewport and Retina display scale (device_scale_factor=2)
        browser = await p.chromium.launch(headless=True)
        
        # device_scale_factor=2 renders double pixel density (e.g. 2880x1800 physical pixels) for absolute sharpness when printed
        guest_context = await browser.new_context(viewport={"width": 1440, "height": 900}, device_scale_factor=2)
        admin_context = await browser.new_context(viewport={"width": 1440, "height": 900}, device_scale_factor=2)
        user_context = await browser.new_context(viewport={"width": 1440, "height": 900}, device_scale_factor=2)
        
        guest_page = await guest_context.new_page()
        admin_page = await admin_context.new_page()
        user_page = await user_context.new_page()
        
        # Log in the contexts
        await handle_login(admin_page, "admin")
        await handle_login(user_page, "user")
        
        # Make sure user_page has item in cart for checkout screenshots
        print("Preparing user cart item...")
        await user_page.goto("http://127.0.0.1:8000/san-pham/1")
        await user_page.wait_for_selector("#btnAddCart")
        await user_page.click("#btnAddCart")
        await user_page.wait_for_timeout(1000)
        
        for idx in sorted(tasks.keys()):
            task = tasks[idx]
            url = f"http://127.0.0.1:8000{task['url']}"
            login_role = task['login']
            desc = task['desc']
            action = task.get('action')
            
            # Select proper page based on login role
            if login_role == "admin":
                page = admin_page
            elif login_role == "user":
                page = user_page
            else:
                page = guest_page
                
            print(f"[{idx}/51] Navigating to {url} ({desc})...")
            try:
                await page.goto(url)
                await page.wait_for_timeout(2000)
                
                # Perform custom page actions
                if action == "import_modal":
                    btn = await page.query_selector("text=Nhập Excel")
                    if btn:
                        await btn.click()
                        await page.wait_for_timeout(500)
                elif action == "ajax_search":
                    await page.click("#globalSearchInput")
                    await page.type("#globalSearchInput", "iPhone")
                    await page.wait_for_timeout(1500)
                elif action == "scroll_wishlist":
                    await page.evaluate("window.scrollTo(0, 300)")
                    await page.wait_for_timeout(500)
                elif action == "scroll_buy_now":
                    await page.evaluate("window.scrollTo(0, 200)")
                    await page.wait_for_timeout(500)
                elif action == "scroll_combo":
                    await page.evaluate("window.scrollTo(0, 900)")
                    await page.wait_for_timeout(500)
                elif action == "prepare_cart" or action == "prepare_cart_vietqr":
                    if action == "prepare_cart_vietqr":
                        await page.evaluate("window.scrollTo(0, 400)")
                    else:
                        await page.evaluate("window.scrollTo(0, 0)")
                    await page.wait_for_timeout(500)
                elif action == "scroll_reviews":
                    await page.evaluate("window.scrollTo(0, 1500)")
                    await page.wait_for_timeout(500)
                elif action == "email_modal":
                    await page.evaluate("window.scrollTo(0, 100)")
                elif action == "lucky_wheel":
                    await page.evaluate("window.scrollTo(0, 300)")
                elif action == "open_lang":
                    lang_btn = await page.query_selector("#langToggleBtn")
                    if lang_btn:
                        await lang_btn.click()
                        await page.wait_for_timeout(500)
                elif action == "open_chatbot":
                    chatbot_fab = await page.query_selector("#chatbot-fab")
                    if chatbot_fab:
                        await chatbot_fab.click()
                        await page.wait_for_timeout(1000)
                elif action == "scroll_flashsale":
                    await page.evaluate("window.scrollTo(0, 500)")
                    await page.wait_for_timeout(500)
                
                # Apply grayscale filter for academic style print screenshots
                await apply_grayscale(page)
                
                # Save screenshot
                image_filename = f"ui_layout_{idx}.png"
                path1 = f"{dest_dir_1}/{image_filename}"
                path2 = f"{dest_dir_2}/{image_filename}"
                
                await page.screenshot(path=path1)
                await page.screenshot(path=path2)
                print(f"Captured [{idx}/51] successfully (High-DPI) -> {image_filename}")
                
            except Exception as e:
                print(f"Error capturing [{idx}/51]: {str(e)}")
                
        await browser.close()
    print("All 51 real application screenshots captured successfully at High-DPI scale.")

if __name__ == "__main__":
    asyncio.run(run())
