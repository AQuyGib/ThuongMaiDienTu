import re

with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
    content = f.read()

# Match each section 7.X.2 up to 7.X.3
pattern = re.compile(r'## 7\.(\d+)\.(.*?)\n### 7\.\1\.2\.\s*Bố cục giao diện \(UI Layout\)(.*?)(?=### 7\.\1\.3)', re.DOTALL | re.IGNORECASE)
matches = list(pattern.finditer(content))

with open("d:/repogist/ThuongMaiDienTu/scratch/ui_layouts_dump.txt", "w", encoding="utf-8") as f:
    for idx, match in enumerate(matches, 1):
        num = match.group(1)
        title = match.group(2).strip()
        body = match.group(3).strip()
        f.write(f"=== FUNCTION 7.{num}: {title} ===\n")
        f.write(body + "\n\n")

print(f"Dumped {len(matches)} layouts successfully.")
