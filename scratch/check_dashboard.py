import asyncio
from playwright.async_api import async_playwright
import sys

sys.stdout.reconfigure(encoding='utf-8')

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context()
        page = await context.new_page()
        
        # Login
        await page.goto("http://127.0.0.1:8000/login")
        await page.fill("#email", "admin@dienmaypro.com.vn")
        await page.fill("#password", "admin123")
        await page.keyboard.press("Enter")
        await page.wait_for_timeout(2000)
        
        # Go to admin root
        await page.goto("http://127.0.0.1:8000/admin")
        await page.wait_for_timeout(2000)
        
        # Get content
        title = await page.title()
        html = await page.content()
        print("Page Title:", title)
        print("HTML length:", len(html))
        print("First 500 chars of HTML:", html[:500])
        
        # Take a screenshot
        await page.screenshot(path="d:/repogist/ThuongMaiDienTu/images/test_dashboard_raw.png")
        print("Saved raw screenshot to images/test_dashboard_raw.png")
        
        await browser.close()

if __name__ == "__main__":
    asyncio.run(main())
