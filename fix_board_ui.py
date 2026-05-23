import re

file_path = "/Users/kiki/Documents/Web Develop/Website Manake/resources/views/availability/board.blade.php"
with open(file_path, "r") as f:
    content = f.read()

replacements = [
    # Backgrounds
    (r'bg-white dark:bg-slate-900', 'bg-[#111113]'),
    (r'bg-slate-50 dark:bg-slate-900/50', 'bg-[#0A0A0B]'),
    (r'bg-slate-50 dark:bg-slate-900/40', 'bg-[#0A0A0B]'),
    (r'bg-slate-50/50 dark:bg-slate-900/30', 'bg-[#0A0A0B]'),
    (r'bg-slate-50 dark:bg-slate-900/30', 'bg-[#0A0A0B]'),
    (r'bg-white dark:bg-slate-800', 'bg-[#0A0A0B]'),
    (r'bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700', 'bg-[#0A0A0B] hover:bg-[#1A1A1E]'),
    (r'hover:bg-slate-50 dark:hover:bg-slate-850', 'hover:bg-[#1A1A1E]'),
    (r'bg-slate-50', 'bg-[#0A0A0B]'),
    (r'bg-white', 'bg-[#111113]'),
    (r'bg-slate-950/60', 'bg-slate-950/60'), # keep this for backdrop
    
    # Borders
    (r'border-slate-200 dark:border-slate-800/50', 'border-[#1A1A1E]'),
    (r'border-slate-200 dark:border-slate-800', 'border-[#1A1A1E]'),
    (r'border-slate-200 dark:border-slate-750', 'border-[#1A1A1E]'),
    (r'border-slate-200', 'border-[#1A1A1E]'),
    
    # Text
    (r'text-slate-900 dark:text-slate-100', 'text-[#E8E8EC]'),
    (r'text-slate-800 dark:text-slate-200', 'text-[#E8E8EC]'),
    (r'text-slate-700 dark:text-slate-300', 'text-[#E8E8EC]'),
    (r'text-slate-650 dark:text-slate-350', 'text-[#A0A0A8]'),
    (r'text-slate-600 dark:text-slate-400', 'text-[#A0A0A8]'),
    (r'text-slate-500 dark:text-slate-450', 'text-[#A0A0A8]'),
    (r'text-slate-500 dark:text-slate-400', 'text-[#A0A0A8]'),
    (r'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200', 'text-[#A0A0A8] hover:text-[#D4A843]'),
    (r'text-slate-400 dark:text-slate-500', 'text-[#A0A0A8]'),
    (r'text-slate-300 dark:text-slate-700', 'text-[#1A1A1E]'),
    
    (r'text-slate-900', 'text-[#E8E8EC]'),
    (r'text-slate-800', 'text-[#E8E8EC]'),
    (r'text-slate-700', 'text-[#E8E8EC]'),
    (r'text-slate-600', 'text-[#A0A0A8]'),
    (r'text-slate-500', 'text-[#A0A0A8]'),
    (r'text-slate-400', 'text-[#6A6A78]'),
    (r'text-slate-300', 'text-[#1A1A1E]'),
    
    # Divide
    (r'divide-slate-100', 'divide-[#1A1A1E]')
]

for old, new in replacements:
    content = content.replace(old, new)

with open(file_path, "w") as f:
    f.write(content)
print("Replaced UI classes successfully.")
