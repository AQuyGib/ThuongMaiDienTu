import re
import asyncio
from playwright.async_api import async_playwright
import os
import sys

# Ensure stdout uses UTF-8 to prevent encoding issues in Windows console
sys.stdout.reconfigure(encoding='utf-8')

# HTML template for rendering Mermaid diagrams with custom neutral theme
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
      stroke-width: 1.5px !important;
    }
    .edgePath .path {
      stroke: #000000 !important;
      stroke-width: 1.2px !important;
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
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
      font-size: 11px !important;
    }
    .marker {
      fill: #000000 !important;
      stroke: #000000 !important;
    }
    .cluster rect {
      fill: #fafafa !important;
      stroke: #000000 !important;
      stroke-width: 1px !important;
      stroke-dasharray: 4 !important;
    }
    .cluster .label {
      font-weight: bold !important;
      font-size: 12px !important;
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

def parse_ui_to_mermaid(func_num, func_title, ui_text):
    lines = ui_text.strip().split('\n')
    parsed_items = []
    
    # Regex to capture indent and content of bullet points
    bullet_pat = re.compile(r'^(\s*)[*\-]\s*(.*)$')
    
    for line in lines:
        m = bullet_pat.match(line)
        if not m:
            continue
        indent_str, content = m.groups()
        indent = len(indent_str)
        
        # Determine level based on indentation spaces
        if indent == 0:
            level = 0
        elif indent <= 4:
            level = 1
        else:
            level = 2
            
        # Clean formatting, replace double quotes with single quotes, and remove backticks
        clean_content = re.sub(r'[*_\-\[\]()]', '', content).strip()
        clean_content = re.sub(r'\s+', ' ', clean_content)
        clean_content = clean_content.replace('"', "'").replace("`", "")
        
        parsed_items.append({'level': level, 'text': clean_content})
        
    # Group items into tree structure
    groups = []
    current_group = None
    current_subgroup = None
    
    for item in parsed_items:
        level = item['level']
        txt = item['text']
        
        parts = txt.split(':', 1)
        title = parts[0].strip().replace('"', "'").replace("`", "")
        desc = parts[1].strip().replace('"', "'").replace("`", "") if len(parts) > 1 else ""
        
        if level == 0:
            current_group = {
                'title': title,
                'desc': desc,
                'items': []
            }
            groups.append(current_group)
            current_subgroup = None
        elif level == 1:
            if current_group is None:
                current_group = {
                    'title': 'Giao diện chính',
                    'desc': '',
                    'items': []
                }
                groups.append(current_group)
            
            current_subgroup = {
                'title': title,
                'desc': desc,
                'children': []
            }
            current_group['items'].append(current_subgroup)
        elif level == 2:
            if current_subgroup is None:
                if current_group is None:
                    current_group = {
                        'title': 'Giao diện chính',
                        'desc': '',
                        'items': []
                    }
                    groups.append(current_group)
                current_subgroup = {
                    'title': 'Thông tin chi tiết',
                    'desc': '',
                    'children': []
                }
                current_group['items'].append(current_subgroup)
            current_subgroup['children'].append({
                'title': title,
                'desc': desc
            })
            
    # Build Mermaid code
    mermaid_lines = []
    mermaid_lines.append("flowchart TB")
    
    # We will use a main outer subgraph representing the page
    func_title_clean = func_title.replace('"', "'").replace("`", "")
    mermaid_lines.append(f'    subgraph MainApp ["Khung giao diện: {func_title_clean}"]')
    mermaid_lines.append("        direction TB")
    
    if not groups:
        # Default fallback if parsing resulted in empty tree
        clean_text = ui_text.strip().replace('"', "'").replace("`", "").replace('\n', '\\n')
        mermaid_lines.append(f'        placeholder["{func_title_clean}\\n({clean_text[:100]}...)"]')
    else:
        for g_idx, g in enumerate(groups):
            g_node_id = f"g_{func_num}_{g_idx}"
            g_title = g['title']
            
            mermaid_lines.append(f'        subgraph {g_node_id} ["{g_title}"]')
            mermaid_lines.append(f'            direction TB')
            
            prev_item_id = None
            for item_idx, item in enumerate(g['items']):
                item_node_id = f"{g_node_id}_item_{item_idx}"
                item_title = item['title']
                item_desc = item['desc']
                
                # Check if it has children
                if item['children']:
                    mermaid_lines.append(f'            subgraph {item_node_id} ["{item_title}"]')
                    mermaid_lines.append(f'                direction TB')
                    
                    prev_child_id = None
                    for c_idx, c in enumerate(item['children']):
                        c_node_id = f"{item_node_id}_child_{c_idx}"
                        c_title = c['title']
                        c_desc = c['desc']
                        
                        max_len = 80
                        if len(c_desc) > max_len:
                            c_desc = c_desc[:max_len] + "..."
                        
                        c_label = f'"{c_title}\\n({c_desc})"' if c_desc else f'"{c_title}"'
                        mermaid_lines.append(f'                {c_node_id}[{c_label}]')
                        
                        if prev_child_id:
                            mermaid_lines.append(f'                {prev_child_id} --> {c_node_id}')
                        prev_child_id = c_node_id
                        
                    mermaid_lines.append(f'            end')
                else:
                    max_len = 80
                    if len(item_desc) > max_len:
                        item_desc = item_desc[:max_len] + "..."
                    item_label = f'"{item_title}\\n({item_desc})"' if item_desc else f'"{item_title}"'
                    mermaid_lines.append(f'            {item_node_id}[{item_label}]')
                    
                if prev_item_id:
                    mermaid_lines.append(f'            {prev_item_id} --> {item_node_id}')
                prev_item_id = item_node_id
                
            mermaid_lines.append(f'        end')
            
    mermaid_lines.append("    end")
    return "\n".join(mermaid_lines)

async def main():
    dest_dir_1 = "d:/repogist/ThuongMaiDienTu/images"
    dest_dir_2 = "d:/HOC/Hoc4/pywword/images"
    os.makedirs(dest_dir_1, exist_ok=True)
    os.makedirs(dest_dir_2, exist_ok=True)
    
    file_path = "d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md"
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    # First, let's clean up any existing UI layouts images references to avoid duplicates
    content = re.sub(r'\n\s*!\[Cấu trúc giao diện\]\(images/ui_layout_\d+\.png\)\s*\n', '\n', content)
    
    # We will locate all 51 sections
    sections = []
    
    for x in range(1, 52):
        h2_pattern = rf"### 7\.{x}\.2\.\s*Bố cục giao diện\s*\(UI Layout\)"
        h3_pattern = rf"### 7\.{x}\.3\.\s*Các tác nhân"
        
        m2 = re.search(h2_pattern, content, re.IGNORECASE)
        m3 = re.search(h3_pattern, content, re.IGNORECASE)
        
        if m2 and m3:
            section_text = content[m2.end():m3.start()].strip()
            title_pattern = rf"## 7\.{x}\.\s*(.*?)\n"
            m_title = re.search(title_pattern, content[:m2.start()], re.IGNORECASE)
            title = m_title.group(1).strip() if m_title else f"Chức năng {x}"
            
            sections.append({
                'num': x,
                'title': title,
                'text': section_text,
                'start_pos': m2.end(),
                'end_pos': m3.start()
            })
            
    print(f"Parsed {len(sections)} sections successfully.")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        await page.set_content(html_template)
        await page.wait_for_timeout(2000)
        
        # To avoid index shifting, we will do replacements from back to front
        sections.reverse()
        
        for sec in sections:
            x = sec['num']
            title = sec['title']
            text = sec['text']
            
            # Generate Mermaid code
            mermaid_code = parse_ui_to_mermaid(x, title, text)
            
            # Render using Playwright
            res = await page.evaluate("code => renderDiagram(code)", mermaid_code)
            if not res["success"]:
                print(f"Mermaid Error on UI Layout {x}: {res.get('error')}")
                continue
                
            element = await page.query_selector("#container")
            if element:
                image_filename = f"ui_layout_{x}.png"
                path1 = f"{dest_dir_1}/{image_filename}"
                path2 = f"{dest_dir_2}/{image_filename}"
                
                await element.screenshot(path=path1)
                await element.screenshot(path=path2)
                print(f"Rendered [{x}/51] -> {image_filename}")
                
                img_tag = f"\n\n![Cấu trúc giao diện](images/{image_filename})\n"
                
                pos = sec['end_pos']
                content = content[:pos] + img_tag + content[pos:]
            else:
                print(f"Element #container not found for UI Layout {x}")
                
        await browser.close()
        
    # Write back to file
    with open(file_path, "w", encoding="utf-8") as f:
        f.write(content)
        
    print("Updated BaoCao_DacTa_ChiTiet_ChucNang.md successfully with all 51 UI layouts.")

if __name__ == "__main__":
    asyncio.run(main())
