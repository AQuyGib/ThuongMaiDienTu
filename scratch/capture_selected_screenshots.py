import asyncio
from playwright.async_api import async_playwright
import os
import sys

# Ensure stdout uses UTF-8 to prevent encoding issues in Windows console
sys.stdout.reconfigure(encoding='utf-8')

tasks = {
    51: {"url": "/admin", "login": "admin", "desc": "Smart setup wizard & CLI orchestrator"}
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
        
        # device_scale_factor=2 renders double pixel density for absolute sharpness when printed (no blurry images!)
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
        # Click Add to Cart button if it exists
        try:
            add_btn = await user_page.query_selector("#btnAddCart")
            if add_btn:
                await add_btn.click()
                await user_page.wait_for_timeout(1000)
        except Exception as e:
            print("Cart preparation warning:", str(e))
        
        # Also prepare guest page cart item
        print("Preparing guest cart item...")
        await guest_page.goto("http://127.0.0.1:8000/san-pham/1")
        try:
            add_btn = await guest_page.query_selector("#btnAddCart")
            if add_btn:
                await add_btn.click()
                await guest_page.wait_for_timeout(1000)
        except Exception as e:
            print("Guest Cart preparation warning:", str(e))

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
                if action == "scroll_wishlist":
                    await page.evaluate("window.scrollTo(0, 300)")
                    await page.wait_for_timeout(500)
                elif action == "lucky_wheel":
                    await page.evaluate("window.scrollTo(0, 400)")
                    await page.wait_for_timeout(500)
                elif action == "open_chatbot":
                    # Let's open the AI Chatbot
                    chatbot_fab = await page.query_selector("#chatbot-fab")
                    if chatbot_fab:
                        await chatbot_fab.click()
                        await page.wait_for_timeout(1000)
                        # Type something to make it look active
                        chat_input = await page.query_selector("#chatbot-input-field")
                        if chat_input:
                            await chat_input.fill("Tư vấn tivi Sony giúp tôi")
                            await page.wait_for_timeout(500)
                    else:
                        print("Chatbot FAB button not found")
                elif action == "scroll_flashsale":
                    await page.evaluate("window.scrollTo(0, 700)")
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
    print("Selected real application screenshots captured successfully at High-DPI scale.")

if __name__ == "__main__":
    asyncio.run(run())
