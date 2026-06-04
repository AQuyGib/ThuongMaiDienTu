import re
import asyncio
from playwright.async_api import async_playwright
import os
import sys

# Ensure stdout uses UTF-8 to prevent encoding issues in Windows console
sys.stdout.reconfigure(encoding='utf-8')

# Base HTML template loaded only ONCE
html_template = """
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
      border: 1px solid #000000;
      border-radius: 4px;
    }
    /* Force black and white theme on Mermaid elements */
    .node rect, .node circle, .node ellipse, .node polygon, .node path {
      fill: #ffffff !important;
      stroke: #000000 !important;
      stroke-width: 2px !important;
    }
    .edgePath .path {
      stroke: #000000 !important;
      stroke-width: 1.5px !important;
    }
    .edgeLabel rect {
      fill: #ffffff !important;
    }
    .edgeLabel text {
      fill: #000000 !important;
    }
    .labelText, .node .label {
      color: #000000 !important;
      fill: #000000 !important;
    }
    .marker {
      fill: #000000 !important;
      stroke: #000000 !important;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
  <script>
    mermaid.initialize({
      startOnLoad: false,
      theme: 'neutral',
      themeVariables: {
        background: '#ffffff',
        primaryColor: '#ffffff',
        primaryTextColor: '#000000',
        primaryBorderColor: '#000000',
        lineColor: '#000000',
        secondaryColor: '#ffffff',
        tertiaryColor: '#ffffff'
      }
    });

    async function renderDiagram(code) {
      const container = document.getElementById('container');
      container.innerHTML = '<pre class="mermaid" id="graph-div"></pre>';
      const graphDiv = document.getElementById('graph-div');
      graphDiv.textContent = code;
      try {
        await mermaid.run({ nodes: [graphDiv] });
        return { success: true };
      } catch(e) {
        return { success: false, error: e.message || String(e) };
      }
    }
  </script>
</head>
<body>
  <div id="container">
    <pre class="mermaid" id="graph-div"></pre>
  </div>
</body>
</html>
"""

async def main():
    dest_dir_1 = "d:/repogist/ThuongMaiDienTu/images"
    dest_dir_2 = "d:/HOC/Hoc4/pywword/images"
    os.makedirs(dest_dir_1, exist_ok=True)
    os.makedirs(dest_dir_2, exist_ok=True)
    
    # Read pywword baocaotong.md as the reference for flowchart extraction
    with open("d:/HOC/Hoc4/pywword/baocaotong.md", "r", encoding="utf-8") as f:
        py_content = f.read()
        
    pattern = re.compile(r'(```mermaid\s*(flowchart.*?)\s*```)', re.DOTALL | re.IGNORECASE)
    matches = list(pattern.finditer(py_content))
    print(f"Found {len(matches)} activity diagrams (flowcharts) in baocaotong.md.")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context(device_scale_factor=3)
        page = await context.new_page()
        
        # Load the base HTML page once
        await page.set_content(html_template)
        await page.wait_for_timeout(2000)
        
        replacements = []
        
        for idx, match in enumerate(matches, 1):
            full_block = match.group(1)
            mermaid_code = match.group(2)
            
            # Call dynamic rendering
            res = await page.evaluate("code => renderDiagram(code)", mermaid_code)
            if not res["success"]:
                print(f"Mermaid Error on diagram {idx}: {res.get('error')}")
                continue
                
            element = await page.query_selector("#container")
            if element:
                image_filename = f"activity_{idx}.png"
                path1 = f"{dest_dir_1}/{image_filename}"
                path2 = f"{dest_dir_2}/{image_filename}"
                
                # Take screenshot and save to both workspaces
                await element.screenshot(path=path1)
                await element.screenshot(path=path2)
                print(f"Rendered [{idx}/51] -> {image_filename}")
                
                img_tag = f"\n\n![Sơ đồ hoạt động](images/{image_filename})\n"
                replacements.append((full_block, full_block + img_tag))
            else:
                print(f"Element #container not found on diagram {idx}")
                
        await browser.close()
        
        # 1. Update d:\HOC\Hoc4\pywword\baocaotong.md
        for target, replacement in replacements:
            if target in py_content and replacement not in py_content:
                py_content = py_content.replace(target, replacement)
        with open("d:/HOC/Hoc4/pywword/baocaotong.md", "w", encoding="utf-8") as f:
            f.write(py_content)
        print("Updated d:/HOC/Hoc4/pywword/baocaotong.md successfully.")
        
        # 2. Update d:\repogist\ThuongMaiDienTu\BaoCao_DacTa_ChiTiet_ChucNang.md
        with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
            appendix_content = f.read()
        
        # Find all flowcharts in the appendix to do matching replacements
        app_matches = list(pattern.finditer(appendix_content))
        app_replacements = []
        for idx, match in enumerate(app_matches, 1):
            full_block = match.group(1)
            image_filename = f"activity_{idx}.png"
            img_tag = f"\n\n![Sơ đồ hoạt động](images/{image_filename})\n"
            app_replacements.append((full_block, full_block + img_tag))
            
        for target, replacement in app_replacements:
            if target in appendix_content and replacement not in appendix_content:
                appendix_content = appendix_content.replace(target, replacement)
                
        with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "w", encoding="utf-8") as f:
            f.write(appendix_content)
        print("Updated d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md successfully.")

if __name__ == "__main__":
    asyncio.run(main())
