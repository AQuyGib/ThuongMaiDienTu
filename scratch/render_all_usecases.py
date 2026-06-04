import re
import asyncio
from playwright.async_api import async_playwright
import os
import sys

# Ensure stdout uses UTF-8 to prevent encoding issues in Windows console
sys.stdout.reconfigure(encoding='utf-8')

def translate_usecase_to_flowchart(usecase_code):
    lines = usecase_code.strip().split('\n')
    actors = {}
    usecases = {}
    uc_counter = 0
    connections = []
    
    for line in lines:
        line = line.strip()
        if not line or line.startswith('usecaseDiagram'):
            continue
        
        # Parse actor definition: actor Name as "Label" or actor Name
        actor_match = re.match(r'^actor\s+(\w+)(?:\s+as\s+"([^"]+)")?', line, re.IGNORECASE)
        if actor_match:
            name = actor_match.group(1)
            label = actor_match.group(2) if actor_match.group(2) else name
            actors[name] = label
            continue
            
        # Parse connections and extract use cases in parentheses
        uc_matches = re.findall(r'\(([^)]+)\)', line)
        for uc_text in uc_matches:
            uc_text_clean = uc_text.strip()
            if uc_text_clean not in usecases:
                usecases[uc_text_clean] = f"uc_{uc_counter}"
                uc_counter += 1
                
        # Translate connection lines
        if '-->' in line:
            label = ""
            if ':' in line:
                line_parts = line.split(':', 1)
                line = line_parts[0].strip()
                label = line_parts[1].strip()
                
            parts = line.split('-->')
            if len(parts) == 2:
                left = parts[0].strip()
                right = parts[1].strip()
                
                if left.startswith('(') and left.endswith(')'):
                    uc_val = left[1:-1].strip()
                    left = usecases[uc_val]
                if right.startswith('(') and right.endswith(')'):
                    uc_val = right[1:-1].strip()
                    right = usecases[uc_val]
                
                if label:
                    label_clean = label.replace('"', '').replace("'", "")
                    connections.append(f"{left} -->|{label_clean}| {right}")
                else:
                    connections.append(f"{left} --> {right}")
                
    # Build flowchart code
    out = "flowchart LR\n"
    out += "    classDef actor fill:#ffffff,stroke:#000000,stroke-width:2px;\n"
    out += "    classDef usecase fill:#ffffff,stroke:#000000,stroke-width:1.5px;\n"
    out += "    linkStyle default stroke:#000000,stroke-width:1.5px,fill:none;\n\n"
    
    for name, label in actors.items():
        out += f'    {name}["{label}\\n(Actor)"]:::actor\n'
        
    for text, uc_id in usecases.items():
        out += f'    {uc_id}(["{text}"]):::usecase\n'
        
    for conn in connections:
        out += f"    {conn}\n"
        
    return out

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
    os.makedirs("d:/repogist/ThuongMaiDienTu/images", exist_ok=True)
    
    # Read BaoCao_DacTa_ChiTiet_ChucNang.md
    with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
        content = f.read()
        
    # Find all occurrences of ```mermaid ... ``` containing usecaseDiagram
    pattern = re.compile(r'(```mermaid\s*(usecaseDiagram.*?)\s*```)', re.DOTALL)
    matches = list(pattern.finditer(content))
    print(f"Found {len(matches)} usecase diagrams in appendix file.")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        
        # Load the base HTML page once
        await page.set_content(html_template)
        # Wait a moment for mermaid to load from CDN
        await page.wait_for_timeout(2000)
        
        replacements = []
        
        for idx, match in enumerate(matches, 1):
            full_block = match.group(1)
            usecase_code = match.group(2)
            
            # Translate to flowchart
            flowchart_code = translate_usecase_to_flowchart(usecase_code)
            
            # Call dynamic rendering function
            res = await page.evaluate("code => renderDiagram(code)", flowchart_code)
            if not res["success"]:
                print(f"Mermaid Error on diagram {idx}: {res.get('error')}")
                continue
                
            # Take screenshot of container
            element = await page.query_selector("#container")
            if element:
                image_filename = f"use_case_{idx}.png"
                image_path = f"d:/repogist/ThuongMaiDienTu/images/{image_filename}"
                await element.screenshot(path=image_path)
                print(f"Rendered [{idx}/51] -> {image_filename}")
                
                # Replace the original block with the original block + image link
                img_tag = f"\n\n![Sơ đồ Use-case chức năng](images/{image_filename})\n"
                replacements.append((full_block, full_block + img_tag))
            else:
                print(f"Element #container not found on diagram {idx}")
                
        await browser.close()
        
        # Perform replacements in the file content
        for target, replacement in replacements:
            if target in content and replacement not in content:
                content = content.replace(target, replacement)
                
        # Clean up any duplicate image embeds or previous custom embeds for 7.1
        content = content.replace("![Sơ đồ Use-case Đăng ký / Đăng nhập](images/use_case_auth_diagram.png)", "")
        content = content.replace("![Sơ đồ Use-case Đăng ký và Đăng nhập](images/use_case_auth_diagram.png)", "")
        
        # Write back to BaoCao_DacTa_ChiTiet_ChucNang.md
        with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "w", encoding="utf-8") as f:
            f.write(content)
        print("Updated BaoCao_DacTa_ChiTiet_ChucNang.md successfully.")

if __name__ == "__main__":
    asyncio.run(main())
