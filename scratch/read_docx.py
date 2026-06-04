import zipfile
import xml.etree.ElementTree as ET
import os

docx_path = r"d:\HOC\Hoc4\pywword\Mapping_Chuc_Nang_Theo_Thanh_Vien.docx"
output_path = r"d:\repogist\ThuongMaiDienTu\scratch\extracted_mapping.txt"

if not os.path.exists(docx_path):
    print(f"File not found: {docx_path}")
    exit(1)

try:
    with zipfile.ZipFile(docx_path) as docx:
        # Get XML content
        xml_content = docx.read('word/document.xml')
        root = ET.fromstring(xml_content)
        
        # Namespaces
        namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
        
        # Extract paragraphs
        text_lines = []
        for p in root.findall('.//w:p', namespaces):
            p_text = ""
            for r in p.findall('.//w:r', namespaces):
                t = r.find('w:t', namespaces)
                if t is not None and t.text:
                    p_text += t.text
            if p_text.strip():
                text_lines.append(p_text)
                
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write("\n".join(text_lines))
        print("Success! Extracted text written to scratch/extracted_mapping.txt")
except Exception as e:
    print(f"Error reading docx: {e}")
