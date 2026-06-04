import re
import os
import sys

# Reconfigure stdout to use utf-8
sys.stdout.reconfigure(encoding='utf-8')

def inspect_file(filepath):
    print(f"=== Inspecting {filepath} ===")
    if not os.path.exists(filepath):
        print("File does not exist.")
        return
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Find all headings like 7.12, 7.13, etc.
    headings = re.findall(r'^(##+ .*7\.\d+.*)$', content, re.MULTILINE)
    print(f"Found {len(headings)} headings matching 7.X:")
    for h in headings[:60]:
        print("  ", h)
    if len(headings) > 60:
        print(f"   ... and {len(headings) - 60} more")

inspect_file("d:/repogist/ThuongMaiDienTu/baocaotong.md")
inspect_file("d:/HOC/Hoc4/pywword/baocaotong.md")
inspect_file("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md")
