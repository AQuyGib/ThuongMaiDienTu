import re

with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
    content = f.read()

# Let's inspect sections from 7.1.2 to 7.1.3
pattern = re.compile(r'### 7\.(\d+)\.2\.\s*Bố cục giao diện \(UI Layout\)(.*?)(?=### 7\.\1\.3)', re.DOTALL | re.IGNORECASE)
matches = list(pattern.finditer(content))
print(f"Found {len(matches)} UI layout sections out of 51.")

if len(matches) > 0:
    print("Example 7.1.2 UI Layout text:")
    print(matches[0].group(2).strip())
