import re

with open("d:/repogist/ThuongMaiDienTu/BaoCao_DacTa_ChiTiet_ChucNang.md", "r", encoding="utf-8") as f:
    content = f.read()

pattern = re.compile(r'(```mermaid\s*(usecaseDiagram.*?)\s*```)', re.DOTALL)
matches = list(pattern.finditer(content))
print(f"Total usecase matches: {len(matches)}")

if len(matches) > 0:
    first_match = matches[0].group(1)
    print("First match in content?", first_match in content)
    
    # Try replacing it with a test string
    test_replaced = content.replace(first_match, first_match + "\n\n<!-- TEST USE CASE EMBED -->\n")
    print("Is test tag in replaced content?", "<!-- TEST USE CASE EMBED -->" in test_replaced)
