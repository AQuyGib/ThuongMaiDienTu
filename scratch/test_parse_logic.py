import re

text = """
*   **Trang nhập mã OTP (User Side):**
    *   Thiết kế tối giản, tập trung vào ô nhập liệu.
    *   **Khung nhập OTP:** Gồm 6 ô nhập chữ số riêng biệt (mỗi ô nhận 1 chữ số, tự động chuyển focus sang ô tiếp theo).
    *   **Bộ đếm thời gian:** Hiển thị thời gian đếm ngược dạng `01:30` (90 giây).
    *   **Liên kết gửi lại:** "Gửi lại mã" (mờ đi khi bộ đếm đang chạy, nổi lên khi bộ đếm về 0).
    *   **Nút xác nhận:** "Xác nhận OTP" (bị vô hiệu hóa cho đến khi điền đủ 6 chữ số).
"""

def generate_mermaid_flowchart(lines_text):
    lines = lines_text.strip().split('\n')
    parsed_items = []
    
    # Simple regex to get indent and content
    bullet_pat = re.compile(r'^(\s*)[*\-]\s*(.*)$')
    
    for line in lines:
        m = bullet_pat.match(line)
        if not m:
            continue
        indent_str, content = m.groups()
        indent = len(indent_str)
        
        # Determine level
        if indent == 0:
            level = 0
        elif indent <= 4:
            level = 1
        else:
            level = 2
            
        # Clean markdown formatting from content
        clean_content = re.sub(r'[*_\-\[\]()]', '', content).strip()
        clean_content = re.sub(r'\s+', ' ', clean_content)
        clean_content = clean_content.replace('"', '\\"')
        
        parsed_items.append({'level': level, 'text': clean_content})
        
    # Generate flowchart
    mermaid = []
    mermaid.append("flowchart TB")
    
    # Track state
    open_subgraphs = 0
    node_counter = 0
    
    for i, item in enumerate(parsed_items):
        level = item['level']
        txt = item['text']
        
        # Lookahead to see if next item is child
        has_child = False
        if i + 1 < len(parsed_items):
            if parsed_items[i+1]['level'] > level:
                has_child = True
                
        node_counter += 1
        node_id = f"n_{node_counter}"
        
        # Split text into title and desc
        parts = txt.split(':', 1)
        title = parts[0].strip()
        desc = parts[1].strip() if len(parts) > 1 else ""
        
        label = f'"{title}\\n({desc})"' if desc else f'"{title}"'
        
        if level == 0:
            # If there's an open subgraph, close it
            while open_subgraphs > 0:
                mermaid.append("    " * open_subgraphs + "end")
                open_subgraphs -= 1
            mermaid.append(f'    subgraph sg_{node_id} ["{title}"]')
            mermaid.append(f'        direction TB')
            open_subgraphs = 1
        elif level == 1:
            # If we need to close level 1 subgraph
            if open_subgraphs > 1:
                mermaid.append("        end")
                open_subgraphs = 1
                
            if has_child:
                mermaid.append(f'        subgraph sg_{node_id} ["{title}"]')
                mermaid.append(f'            direction TB')
                open_subgraphs = 2
            else:
                mermaid.append(f'        {node_id}[{label}]')
        elif level == 2:
            # Child node
            mermaid.append(f'            {node_id}[{label}]')
            
    # Close any open subgraphs
    while open_subgraphs > 0:
        mermaid.append("    " * open_subgraphs + "end")
        open_subgraphs -= 1
        
    return "\n".join(mermaid)

print(generate_mermaid_flowchart(text))
