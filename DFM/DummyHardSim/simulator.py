#!/usr/bin/env python3
"""
DFM Hardware Device Simulator
Simulates all 4 device types for testing backend endpoints
"""

import http.server
import socketserver
import json
import urllib.request
import urllib.error
import uuid
import random
from datetime import datetime, timezone, timedelta
from pathlib import Path
import threading
import time

CONFIG_FILE = Path(__file__).parent / "config.json"
PORT = 3030

class SimulatorHandler(http.server.SimpleHTTPRequestHandler):
    """Handles both serving the HTML UI and API proxy requests"""
    
    def do_GET(self):
        if self.path == '/' or self.path == '/index.html':
            self.serve_html()
        elif self.path == '/api/config':
            self.serve_config()
        else:
            super().do_GET()
            
    def do_POST(self):
        if self.path.startswith('/api/proxy'):
            self.handle_proxy()
        else:
            self.send_error(404)
            
    def serve_html(self):
        index_path = Path(__file__).parent / "index.html"
        try:
            with open(index_path, 'r', encoding='utf-8') as f:
                html = f.read()
            self.send_response(200)
            self.send_header('Content-type', 'text/html')
            self.end_headers()
            self.wfile.write(html.encode('utf-8'))
        except Exception as e:
            self.send_error(500, str(e))
        
    def serve_config(self):
        try:
            with open(CONFIG_FILE) as f:
                config = json.load(f)
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps(config).encode())
        except Exception as e:
            self.send_error(500, str(e))
    def handle_proxy(self):
        content_length = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(content_length)
        
        try:
            req_data = json.loads(body.decode('utf-8'))
            target_url = req_data.get('url')
            headers = req_data.get('headers', {})
            payload = req_data.get('payload', {})
            method = req_data.get('method', 'POST' if payload else 'GET')
            
            # If payload is provided, ensure Content-Type is application/json
            if payload is not None and 'Content-Type' not in headers and 'content-type' not in headers:
                headers['Content-Type'] = 'application/json'
            if 'Accept' not in headers and 'accept' not in headers:
                headers['Accept'] = 'application/json'
                
            # Forward the request to the target URL
            req = urllib.request.Request(
                target_url, 
                data=json.dumps(payload).encode('utf-8') if payload else None,
                headers=headers,
                method=method
            )
            
            with urllib.request.urlopen(req, timeout=10) as response:
                response_data = response.read()
                self.send_response(response.status)
                self.send_header('Content-type', 'application/json')
                self.end_headers()
                self.wfile.write(response_data)
                
        except urllib.error.HTTPError as e:
            self.send_response(e.code)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(e.read())
        except Exception as e:
            self.send_response(500)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"error": str(e)}).encode())

class ReuseTCPServer(socketserver.TCPServer):
    allow_reuse_address = True

def run_server():
    with ReuseTCPServer(("", PORT), SimulatorHandler) as httpd:
        print(f"Simulator UI running at http://localhost:{PORT}")
        httpd.serve_forever()

if __name__ == "__main__":
    run_server()

