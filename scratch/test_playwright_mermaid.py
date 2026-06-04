import asyncio
from playwright.async_api import async_playwright
import os

html_content = """
<!DOCTYPE html>
<html>
<head>
  <style>
    body {
      background-color: white;
      margin: 0;
      padding: 10px;
      display: inline-block;
    }
    #container {
      background-color: white;
      padding: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
  <script>
    mermaid.initialize({
      startOnLoad: false,
      theme: 'neutral'
    });
  </script>
</head>
<body>
  <div id="container">
    <pre class="mermaid">
usecaseDiagram
    actor Guest as "Khách vãng lai"
    actor Google as "Google OAuth API"
    
    Guest --> (Đăng ký tài khoản thủ công)
    Guest --> (Đăng nhập bằng Email/Password)
    Guest --> (Đăng nhập qua Google SSO)
    (Đăng nhập qua Google SSO) --> Google
    </pre>
  </div>
  <script>
    try {
      mermaid.run().then(() => {
         window.rendered = true;
      }).catch(err => {
         window.error = err.message || String(err);
      });
    } catch(e) {
      window.error = e.message || String(e);
    }
  </script>
</body>
</html>
"""

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        await page.set_content(html_content)
        
        # Wait for rendering to complete
        try:
            await page.wait_for_function("window.rendered === true || window.error !== undefined", timeout=10000)
        except Exception as e:
            print("Timeout waiting for render:", repr(e))
            await browser.close()
            return
        
        err = await page.evaluate("window.error")
        if err:
            with open("d:/repogist/ThuongMaiDienTu/scratch/mermaid_err.txt", "w", encoding="utf-8") as f:
                f.write(err)
            print("Mermaid Error saved to scratch/mermaid_err.txt")
            await browser.close()
            return
            
        # Get container element bounding box and take screenshot
        element = await page.query_selector("#container")
        if element:
            await element.screenshot(path="d:/repogist/ThuongMaiDienTu/images/test_usecase.png")
            print("Successfully rendered to d:/repogist/ThuongMaiDienTu/images/test_usecase.png")
        else:
            print("Container not found.")
        await browser.close()

if __name__ == "__main__":
    asyncio.run(main())
