import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

def find_images(filepath):
    print(f"=== Images in {filepath} ===")
    with open(filepath, "r", encoding="utf-8") as f:
        lines = f.readlines()
    for idx, line in enumerate(lines):
        matches = re.findall(r'!\[.*?\]\((images/.*?)\)', line)
        if matches:
            print(f"Line {idx+1}: {line.strip()} -> {matches}")

find_images("d:/repogist/ThuongMaiDienTu/BaoCao_ChiTiet_DuAn.md")
