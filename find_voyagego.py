import os

EXCLUDE_DIRS = {'.git', 'vendor', 'storage', 'node_modules', '.gemini', 'email_previews'}

matches = []
for root, dirs, files in os.walk('.'):
    dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS]
    for file in files:
        if file.endswith(('.php', '.js', '.json', '.html', '.css', '.env', '.example', '.md')):
            path = os.path.join(root, file)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    for line_no, line in enumerate(f, 1):
                        if 'voyagego' in line.lower():
                            matches.append((path, line_no, line.strip()))
            except Exception as e:
                pass

print(f"Total occurrences found: {len(matches)}")
print("\nFirst 30 occurrences:")
for p, l, t in matches[:30]:
    print(f"{p}:{l} -> {t[:100]}")

with open('voyagego_matches.txt', 'w', encoding='utf-8') as out:
    for p, l, t in matches:
        out.write(f"{p}:{l}:{t}\n")
print("\nSaved full list to voyagego_matches.txt")

