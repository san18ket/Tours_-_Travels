with open('voyagego_matches.txt', 'r', encoding='utf-8') as f:
    lines = f.readlines()

categories = {
    'views_layouts': [],
    'views_emails': [],
    'views_office': [],
    'views_driver': [],
    'services_controllers': [],
    'languages': [],
    'configs_env': [],
    'tests': [],
    'others': []
}

for line in lines:
    parts = line.strip().split(':', 2)
    if len(parts) < 3:
        continue
    path, line_no, text = parts[0], parts[1], parts[2]
    
    if 'tests' in path:
        categories['tests'].append((path, line_no, text))
    elif 'Languages' in path:
        categories['languages'].append((path, line_no, text))
    elif 'emails' in path:
        categories['views_emails'].append((path, line_no, text))
    elif 'office' in path:
        categories['views_office'].append((path, line_no, text))
    elif 'driver' in path or 'mobile-driver' in path:
        categories['views_driver'].append((path, line_no, text))
    elif 'layouts' in path:
        categories['views_layouts'].append((path, line_no, text))
    elif 'Services' in path or 'Controllers' in path or 'Core' in path:
        categories['services_controllers'].append((path, line_no, text))
    elif '.env' in path or 'config' in path:
        categories['configs_env'].append((path, line_no, text))
    else:
        categories['others'].append((path, line_no, text))

for cat, items in categories.items():
    print(f"\n=== {cat} ({len(items)}) ===")
    for p, l, t in items[:10]:
        print(f"  {p}:{l} -> {t[:80]}")

