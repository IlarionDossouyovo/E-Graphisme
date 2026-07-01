#!/usr/bin/env python3
"""
Serveur HTTP personnalisé pour E-Graphisme
- Sert les fichiers statiques
- Utilise 404.html pour les pages non trouvées
"""
import http.server
import socketserver
import os

PORT = 12000

class CustomHTTPRequestHandler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=os.getcwd(), **kwargs)
    
    def send_error(self, code, message=None, explain=None):
        """Override pour utiliser 404.html personnalisé"""
        if code == 404:
            self.path = '/404.html'
            try:
                # Tenter de servir 404.html
                f = self.send_head()
                if f:
                    f.close()
                return
            except:
                pass
        # Comportement par défaut pour les autres erreurs
        super().send_error(code, message, explain)
    
    def end_headers(self):
        # Ajouter les headers pour le cache
        self.send_header('Cache-Control', 'no-cache, must-revalidate')
        super().end_headers()
    
    def do_GET(self):
        # If requesting root, serve index.html
        if self.path == '/':
            self.path = '/index.html'
        return super().do_GET()

class ReuseAddrTCPServer(socketserver.TCPServer):
    allow_reuse_address = True

def run_server():
    with ReuseAddrTCPServer(("", PORT), CustomHTTPRequestHandler) as httpd:
        print(f"✅ E-Graphisme Server started!")
        print(f"🌐 http://localhost:{PORT}")
        print(f"📱 http://localhost:{PORT}/index.html")
        print(f"🎨 http://localhost:{PORT}/portfolio.html")
        print(f"⚙️  http://localhost:{PORT}/services.html")
        print(f"🎬 http://localhost:{PORT}/studio.html")
        print(f"\nAppuyez Ctrl+C pour arrêter\n")
        httpd.serve_forever()

if __name__ == "__main__":
    run_server()