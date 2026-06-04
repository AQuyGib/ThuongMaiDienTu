import re
import os

def parse_ui_to_mermaid(title, ui_text):
    # Parse lines
    lines = ui_text.strip().split('\n')
    nodes = []
    subgraphs = {}
    current_subgraph = None
    
    # Clean text helper
    def clean(text):
        t = re.sub(r'[*_\-\[\]()]', '', text).strip()
        t = re.sub(r'\s+', ' ', t)
        # Escape quotes for Mermaid labels
        t = t.replace('"', '\\"')
        return t

    # Regex to count indentation and match bullet points
    # Level is determined by the number of leading spaces/tabs
    bullet_pattern = re.compile(r'^(\s*)[*\-]\s*(.*)$')
    
    node_id_counter = 0
    
    mermaid_lines = []
    mermaid_lines.append("flowchart TB")
    mermaid_lines.append(f'    subgraph MainApp ["Khung giao diện: {title}"]')
    mermaid_lines.append("        direction TB")
    
    # Custom CSS theme for neutral/wireframe style
    # White background, black text, thin black borders
    
    last_level_0_id = None
    last_level_1_id = None
    
    for line in lines:
        match = bullet_pattern.match(line)
        if not match:
            continue
        indent, text = match.groups()
        indent_len = len(indent)
        
        # Determine level (approximate: 0, 4, 8 spaces)
        if indent_len == 0:
            level = 0
        elif indent_len <= 4:
            level = 1
        else:
            level = 2
            
        cleaned_text = clean(text)
        if not cleaned_text:
            continue
            
        # Shorten text for node label if too long, or split title vs desc
        parts = cleaned_text.split(':', 1)
        node_title = parts[0].strip()
        node_desc = parts[1].strip() if len(parts) > 1 else ""
        
        node_id_counter += 1
        node_id = f"node_{node_id_counter}"
        
        # Build node string
        if node_desc:
            label = f'"{node_title}\\n({node_desc})"'
        else:
            label = f'"{node_title}"'
            
        if level == 0:
            # Main panel
            mermaid_lines.append(f'        subgraph {node_id} ["{node_title}"]')
            mermaid_lines.append("            direction TB")
            if node_desc:
                mermaid_lines.append(f'            {node_id}_desc["{node_desc}"]')
            current_subgraph = node_id
            last_level_0_id = node_id
            last_level_1_id = None
        elif level == 1:
            # Component inside main panel
            if current_subgraph:
                # If there are sub-bullets, we might want to group them too
                # For simplicity, let's create a node inside the current subgraph
                mermaid_lines.append(f'            {node_id}[{label}]')
                last_level_1_id = node_id
            else:
                mermaid_lines.append(f'        {node_id}[{label}]')
                last_level_1_id = node_id
        else:
            # Level 2 detail inside level 1 component
            if last_level_1_id:
                # Show relationship or put inside if we nested it
                # Since flowchart doesn't support nested subgraphs easily without complex syntax,
                # we just link it with a dotted line or put it next to it.
                mermaid_lines.append(f'            {node_id}[{label}]')
                mermaid_lines.append(f'            {last_level_1_id} -.-> {node_id}')
            else:
                mermaid_lines.append(f'        {node_id}[{label}]')
                
    # Close any open subgraphs (we had 1 for each level 0 and 1 for MainApp)
    # We close each level 0 subgraph dynamically if opened.
    # In our simple logic, we just close subgraphs when we see level 0.
    # Let's rewrite the parser to handle nested subgraphs properly.
    return node_id_counter

print("Parser template defined.")
