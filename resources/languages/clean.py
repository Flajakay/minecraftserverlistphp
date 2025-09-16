#!/usr/bin/env python3

file_path = r"D:\SOFT\OpenServer\home\serverlist\resources\languages\english.php"

# Lines to remove
lines_to_remove = [631, 612, 621, 666, 626, 643, 722, 681, 682, 614, 611, 693, 609, 702, 640, 639, 721, 740, 718, 630, 629, 683]

# Read file
with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Remove lines (sort descending to maintain line numbers)
for line_num in sorted(lines_to_remove, reverse=True):
    if line_num <= len(lines):
        del lines[line_num - 1]  # Convert to 0-indexed

# Write back
with open(file_path, 'w', encoding='utf-8') as f:
    f.writelines(lines)

print(f"Removed {len(lines_to_remove)} duplicate lines from {file_path}")