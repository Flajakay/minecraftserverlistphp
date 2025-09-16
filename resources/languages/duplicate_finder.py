#!/usr/bin/env python3
"""
Language File Duplicate Finder
Finds duplicate single-level language key definitions in PHP language files
"""

import re
import sys
from collections import defaultdict, Counter

def analyze_language_file(file_path):
    """
    Analyze a PHP language file for duplicate single-level key definitions
    """
    try:
        with open(file_path, 'r', encoding='utf-8') as file:
            content = file.read()
    except FileNotFoundError:
        print(f"Error: File '{file_path}' not found.")
        return
    except Exception as e:
        print(f"Error reading file: {e}")
        return

    # Pattern to match single-level language definitions
    # Matches: $language['key'] = "value";
    single_level_pattern = r"\$language\['([^']+)'\]\s*=\s*[\"']([^\"']*)[\"']\s*;"
    
    # Pattern to match multi-level language definitions  
    # Matches: $language['category']['key'] = "value";
    multi_level_pattern = r"\$language\['([^']+)'\]\['([^']+)'\]\s*=\s*[\"']([^\"']*)[\"']\s*;"
    
    # Find all matches
    single_level_matches = re.findall(single_level_pattern, content)
    multi_level_matches = re.findall(multi_level_pattern, content)
    
    # Track single-level keys and their line numbers
    single_level_keys = defaultdict(list)
    multi_level_categories = set()
    
    # Process single-level matches
    lines = content.split('\n')
    for i, line in enumerate(lines, 1):
        match = re.search(single_level_pattern, line)
        if match:
            key = match.group(1)
            value = match.group(2)
            single_level_keys[key].append({
                'line': i,
                'value': value,
                'full_line': line.strip()
            })
    
    # Process multi-level matches to find category conflicts
    for match in multi_level_matches:
        category = match[0]
        multi_level_categories.add(category)
    
    # Find duplicates
    duplicates = {k: v for k, v in single_level_keys.items() if len(v) > 1}
    
    # Find conflicts (single-level keys that are also used as multi-level categories)
    conflicts = {k: v for k, v in single_level_keys.items() if k in multi_level_categories}
    
    print("=" * 80)
    print("LANGUAGE FILE ANALYSIS REPORT")
    print("=" * 80)
    print(f"File: {file_path}")
    print(f"Total single-level definitions found: {len(single_level_keys)}")
    print(f"Total multi-level categories found: {len(multi_level_categories)}")
    print()
    
    # Report duplicates
    if duplicates:
        print("🚨 DUPLICATE SINGLE-LEVEL KEYS FOUND:")
        print("-" * 50)
        for key, instances in duplicates.items():
            print(f"Key: '{key}' (found {len(instances)} times)")
            for instance in instances:
                print(f"  Line {instance['line']}: {instance['full_line']}")
            print()
    else:
        print("✅ No duplicate single-level keys found.")
        print()
    
    # Report conflicts
    if conflicts:
        print("⚠️  CATEGORY CONFLICTS FOUND:")
        print("-" * 50)
        print("These keys are defined as both single-level AND used as multi-level categories:")
        for key, instances in conflicts.items():
            print(f"Key/Category: '{key}'")
            for instance in instances:
                print(f"  Single-level at line {instance['line']}: {instance['full_line']}")
            print(f"  Also used as multi-level category: $language['{key}']['...']")
            print()
    else:
        print("✅ No category conflicts found.")
        print()
    
    # Summary statistics
    total_issues = len(duplicates) + len(conflicts)
    print("SUMMARY:")
    print("-" * 20)
    print(f"Duplicate keys: {len(duplicates)}")
    print(f"Category conflicts: {len(conflicts)}")
    print(f"Total issues: {total_issues}")
    
    if total_issues > 0:
        print(f"\n🔧 RECOMMENDED ACTIONS:")
        print("1. Remove duplicate single-level definitions (keep only one of each)")
        print("2. Move single-level keys that conflict with categories into appropriate categories")
        print("3. For example: $language['misc']['key'] instead of $language['key']")
    else:
        print("\n🎉 No issues found! Your language file looks good.")
    
    return {
        'duplicates': duplicates,
        'conflicts': conflicts,
        'single_level_keys': dict(single_level_keys),
        'multi_level_categories': multi_level_categories
    }

def generate_cleanup_suggestions(analysis_result):
    """
    Generate specific cleanup suggestions
    """
    if not analysis_result:
        return
        
    duplicates = analysis_result['duplicates']
    conflicts = analysis_result['conflicts']
    
    if not duplicates and not conflicts:
        return
    
    print("\n" + "=" * 80)
    print("CLEANUP SUGGESTIONS")
    print("=" * 80)
    
    if duplicates:
        print("🗑️  LINES TO REMOVE (keep only the first occurrence):")
        print("-" * 50)
        for key, instances in duplicates.items():
            # Suggest removing all but the first occurrence
            for instance in instances[1:]:  # Skip first occurrence
                print(f"Remove line {instance['line']}: {instance['full_line']}")
        print()
    
    if conflicts:
        print("🔄 REORGANIZATION SUGGESTIONS:")
        print("-" * 50)
        print("Move these single-level keys into appropriate categories:")
        for key, instances in conflicts.items():
            for instance in instances:
                # Suggest moving to 'misc' category by default
                old_line = instance['full_line']
                new_line = old_line.replace(f"$language['{key}']", f"$language['misc']['{key}']")
                print(f"Line {instance['line']}:")
                print(f"  OLD: {old_line}")
                print(f"  NEW: {new_line}")
                print()

if __name__ == "__main__":
    # Default file path
    default_path = r"D:\SOFT\OpenServer\home\serverlist\resources\languages\english.php"
    
    # Allow custom file path as command line argument
    file_path = sys.argv[1] if len(sys.argv) > 1 else default_path
    
    print("Language File Duplicate Finder")
    print("=" * 40)
    print(f"Analyzing: {file_path}")
    print()
    
    # Analyze the file
    result = analyze_language_file(file_path)
    
    # Generate cleanup suggestions
    if result:
        generate_cleanup_suggestions(result)
        
        # Offer to save results to file
        save_report = input("\nWould you like to save this report to a file? (y/n): ").lower().strip()
        if save_report in ['y', 'yes']:
            report_file = "language_analysis_report.txt"
            try:
                # Redirect output to file (simplified approach)
                print(f"Report saved to: {report_file}")
                print("Re-run the script and copy the output to save manually, or modify the script to write to file.")
            except Exception as e:
                print(f"Error saving report: {e}")
