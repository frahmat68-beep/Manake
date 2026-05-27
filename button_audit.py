import os
import re
import json

def audit():
    issues = []
    
    # regex patterns to flag
    bad_hover_bg = r'hover:bg-(blue|slate|gray|indigo|rose|red)-[1-9]00'
    bad_hover_text = r'hover:text-(blue|slate|gray|indigo|rose|red)-[1-9]00'
    bad_bg = r'bg-(blue|slate|gray|indigo|rose|red)-[1-9]00|bg-white'
    bad_text = r'text-(blue|slate|gray|indigo|rose|red)-[1-9]00|text-black'
    
    for root, dirs, files in os.walk('resources/views'):
        for file in files:
            if not file.endswith('.blade.php'):
                continue
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
                
            # find all tags that might be buttons (button, a)
            for m in re.finditer(r'<(button|a)\s+[^>]*class="([^"]+)"[^>]*>', content):
                tag = m.group(1)
                classes = m.group(2)
                
                # Check if it has button-like classes if it's an 'a' tag
                if tag == 'a' and not any(c in classes for c in ['btn', 'px-', 'py-', 'rounded']):
                    continue
                    
                element_issues = []
                
                if re.search(bad_hover_bg, classes):
                    element_issues.append('Bad hover background')
                if re.search(bad_hover_text, classes):
                    element_issues.append('Bad hover text')
                if re.search(bad_bg, classes):
                    element_issues.append('Inconsistent background')
                if re.search(bad_text, classes):
                    element_issues.append('Inconsistent text color')
                    
                if element_issues:
                    issues.append({
                        'file': path,
                        'tag': tag,
                        'classes': classes,
                        'issues': element_issues
                    })
                    
    with open('button_audit_results.json', 'w') as f:
        json.dump(issues, f, indent=2)
        
if __name__ == '__main__':
    audit()
