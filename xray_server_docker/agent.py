#!/usr/bin/env python3
import os
import signal
from http.server import HTTPServer, BaseHTTPRequestHandler

API_TOKEN = os.environ.get("API_TOKEN", "")

class Handler(BaseHTTPRequestHandler):
    def do_POST(self):
        if self.path != "/restart-xray":
            self.send_response(404)
            self.end_headers()
            return

        auth = self.headers.get("Authorization", "")
        if auth != f"Bearer {API_TOKEN}":
            self.send_response(401)
            self.end_headers()
            self.wfile.write(b"Unauthorized")
            return

        try:
            os.kill(1, signal.SIGHUP)
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b"OK")
        except Exception as e:
            self.send_response(500)
            self.end_headers()
            self.wfile.write(str(e).encode())

    def do_GET(self):
        if self.path == "/health":
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b"OK")
        else:
            self.send_response(404)
            self.end_headers()

    def log_message(self, format, *args):
        pass

if __name__ == "__main__":
    print("Agent listening on :8080", flush=True)
    HTTPServer(("0.0.0.0", 8080), Handler).serve_forever()