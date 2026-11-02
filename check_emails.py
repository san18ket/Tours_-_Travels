import os

email_dir = 'app/Views/emails'
for root, dirs, files in os.walk(email_dir):
    for f in files:
        if f.endswith('.php'):
            p = os.path.join(root, f)
            with open(p, 'r', encoding='utf-8') as file:
                content = file.read()
            if 'VoyageGo' in content:
                print("Found in:", p)

