import re
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

def check_file(filepath):
    print(f"=== Checking {filepath} ===")
    if not os.path.exists(filepath):
        print("File does not exist.")
        return
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    targets = [12, 13, 14, 22, 36, 37, 38, 39, 40, 41, 46, 47, 48, 49, 50, 51]
    
    # We want to check for headings like "7.12", "7.13", and look at the text below them to see if ui_layout_X.png or use_case_X.png is referenced!
    # Let's split by heading "## 7.X"
    sections = re.split(r'^(## 7\.\d+.*)$', content, flags=re.MULTILINE)
    
    # sections is a list where odd indices are headings, and even indices (after them) are body content.
    # Let's map each heading to its body content.
    for i in range(1, len(sections), 2):
        heading = sections[i]
        body = sections[i+1] if i+1 < len(sections) else ""
        
        # Extract the section number, e.g. 7.12
        m = re.search(r'7\.(\d+)', heading)
        if m:
            num = int(m.group(1))
            if num in targets:
                print(f"Section {num}: {heading[:40]}...")
                # Search for any image links like ![...](images/...)
                img_links = re.findall(r'!\[.*?\]\((images/.*?)\)', body)
                print(f"  Images referenced: {img_links}")
                # Check if ui_layout_X.png is referenced
                expected = f"images/ui_layout_{num}.png"
                expected_uc = f"images/use_case_{num}.png" # Wait, wait! What about the use-case and activity diagram images?
                # The user wants "hình ảnh giao diện (UI Layout)" for each function to be giống với trangchu.png.
                # Let's see if expected is in img_links
                found_ui = any(expected in link for link in img_links)
                print(f"  Found ui_layout_{num}.png: {found_ui}")

check_file("d:/repogist/ThuongMaiDienTu/baocaotong.md")
check_file("d:/HOC/Hoc4/pywword/baocaotong.md")
