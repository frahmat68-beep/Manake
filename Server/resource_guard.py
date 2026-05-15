import time
import psutil
import random
import sys
import os

# Config
TARGET_OCPU = 1
TARGET_RAM_MB = 1024
AI_PROCESS_NAMES = ['ollama', 'postgres', 'mysqld']
CPU_THRESHOLD = 10.0 # Percent

def get_competing_load():
    """Check if AI or DB processes are active and using CPU."""
    try:
        load = 0.0
        for proc in psutil.process_iter(['name', 'cpu_percent']):
            if any(name in proc.info['name'].lower() for name in AI_PROCESS_NAMES):
                load += proc.info['cpu_percent']
        return load
    except (psutil.NoSuchProcess, psutil.AccessDenied):
        return 0.0

def run_jitter():
    """Light CPU/RAM usage to stay active for Oracle's idle detection."""
    # Small math task
    _ = sum(i * i for i in range(10000))
    # Small memory allocation (10MB)
    hold = ' ' * (10 * 1024 * 1024)
    time.sleep(random.uniform(1, 5))
    del hold

def main():
    print("🛡️ Manake Resource Guard Active")
    print(f"Monitoring for: {AI_PROCESS_NAMES}")
    
    while True:
        competing_load = get_competing_load()
        
        if competing_load > CPU_THRESHOLD:
            # AI/DB is working hard, we stay idle to give them all resources
            # We still need a tiny bit of activity every few minutes 
            # so Oracle doesn't think the VM is dead.
            print(f"⚙️ System Busy ({competing_load}%). Guard yielding...")
            time.sleep(300) 
        else:
            # System is idle, we perform "stealth" activity
            # to stay above the 10% weekly average.
            run_jitter()
            time.sleep(random.randint(30, 60))

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        sys.exit(0)
