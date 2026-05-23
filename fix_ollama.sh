#!/bin/bash
sudo sed -i '/\[Service\]/a Environment="OLLAMA_HOST=0.0.0.0"' /etc/systemd/system/ollama.service 2>/dev/null || sudo sed -i '/\[Service\]/a Environment="OLLAMA_HOST=0.0.0.0"' /lib/systemd/system/ollama.service
sudo systemctl daemon-reload
sudo systemctl restart ollama
sudo iptables -A INPUT -p tcp --dport 11434 -j ACCEPT
echo "Ollama configured!"
