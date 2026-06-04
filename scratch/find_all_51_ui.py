import re

with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
    content = f.read()

found = 0
for x in range(1, 52):
    # Regex to find: ### 7.X.2. Bố cục giao diện (UI Layout)
    # followed by everything until ### 7.X.3.
    h2_pattern = rf"### 7\.{x}\.2\.\s*Bố cục giao diện\s*\(UI Layout\)"
    h3_pattern = rf"### 7\.{x}\.3\.\s*Các tác nhân"
    
    m2 = re.search(h2_pattern, content, re.IGNORECASE)
    m3 = re.search(h3_pattern, content, re.IGNORECASE)
    
    if m2 and m3:
        found += 1
        text = content[m2.end():m3.start()].strip()
        # Print length of text
        # print(f"7.{x}: length {len(text)}")
    else:
        print(f"Missing 7.{x}!")

print(f"Total found in loop: {found}")
