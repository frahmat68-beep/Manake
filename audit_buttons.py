import os
import re

def audit_directory(directory):
    bad_patterns = [
        # Hover states that revert to default Tailwind colors instead of dark mode colors
        r'hover:bg-blue-[4-9]00',
        r'hover:text-blue-[4-9]00',
        r'hover:bg-slate-[1-9]00',
        r'hover:text-slate-[1-9]00',
        r'hover:bg-gray-[1-9]00',
        r'hover:text-gray-[1-9]00',
        # Backgrounds and texts that might not be overridden globally
        r'bg-blue-500', r'bg-blue-700', # bg-blue-600 is overridden, but what about 500/700?
        r'text-black', r'bg-white'
    ]

    for root, _, files in os.walk(directory):
        for file in files:
            if not file.endswith('.blade.php'):
                continue
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
                
            # Find all class attributes
            for match in re.finditer(r'class="([^"]+)"', content):
                classes = match.group(1)
                
                # Check if it's likely a button or actionable item
                if 'btn' in classes or 'hover:' in classes or 'px-' in classes:
                    for pattern in bad_patterns:
                        if re.search(pattern, classes):
                            print(f"{path}: Found potentially bad class '{pattern}' in '{classes}'")
                            break # Only print once per element

if __name__ == '__main__':
    audit_directory('resources/views')
