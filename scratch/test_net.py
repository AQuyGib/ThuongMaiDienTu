import urllib.request
try:
    urllib.request.urlopen("https://www.google.com", timeout=3)
    print("Internet access is AVAILABLE.")
except Exception as e:
    print(f"Internet access is BLOCKED: {e}")
