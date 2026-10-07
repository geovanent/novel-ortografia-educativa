"""Integração HTTP somente no contêiner isolado local da auditoria (porta 8090)."""
import http.cookiejar
import json
import re
import secrets
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

BASE = 'http://localhost:8090/'
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def get(path):
    return client.open(BASE + path).read().decode('utf-8')

def token(html):
    return re.search(r'name="csrf_test_name" value="([a-f0-9]+)"', html)[1]

def post(path, fields, image=None):
    if image is None:
        data = urllib.parse.urlencode(fields).encode()
        headers = {}
    else:
        boundary = secrets.token_hex(16)
        chunks = []
        for name, value in fields.items():
            chunks.append(('--' + boundary + '\r\nContent-Disposition: form-data; name="' + name + '"\r\n\r\n' + value + '\r\n').encode())
        name, mime, contents = image
        chunks.append(('--' + boundary + '\r\nContent-Disposition: form-data; name="imagem"; filename="' + name + '"\r\nContent-Type: ' + mime + '\r\n\r\n').encode() + contents + b'\r\n')
        chunks.append(('--' + boundary + '--\r\n').encode())
        data = b''.join(chunks)
        headers = {'Content-Type': 'multipart/form-data; boundary=' + boundary}
    html = client.open(urllib.request.Request(BASE + path, data=data, headers=headers)).read().decode('utf-8')
    return html + ''.join(json.loads(message) for message in re.findall(r'Novel.notify\(("(?:[^"\\]|\\.)*")', html))

try:
    post('administracao/salvar/palavra', {'id': ''})
    raise AssertionError('POST sem CSRF aceito')
except urllib.error.HTTPError as error:
    assert error.code == 403
print('OK POST sem CSRF rejeitado')
html = get('administracao')
html = post('administracao/fazerLogin', {'csrf_test_name': token(html), 'login': 'teste123', 'senha': '123@Mudar'})
assert 'Conteúdo da Academia' in html
html = get('administracao/cadastrarPalavra')
fields = {'csrf_test_name': token(html), 'id': '', 'palavraIncompleta': 'Via_em', 'palavraCompleta': 'Viagem', 'letraGabarito': 'g', 'enunciado': '<script>alert(1)</script>', 'justificativa': 'Substantivo com g.', 'codGrafema': '1'}
image = Path('assets/img/grafemas/palavra/g_j.png').read_bytes()
html = post('administracao/salvar/palavra', fields, ('exemplo.png', 'image/png', image))
assert 'Conteúdo salvo.' in html, re.findall(r'Novel.notify\([^<]+', html)[-1:]
ids = re.findall(r'administracao/editarPalavra/(\d+)', html)
path = 'administracao/editarPalavra/' + ids[-1]
html = get(path)
assert '&lt;script&gt;alert(1)&lt;/script&gt;' in html and '<script>alert(1)</script>' not in html
uploaded = re.search(r'<img src="([^"]+/uploads/[^"]+)"', html)[1]
assert client.open(uploaded).read() == image
print('OK upload PNG persistido e conteúdo escapado no editor')
fields.update({'id': ids[-1], 'csrf_test_name': token(html)})
html = post('administracao/salvar/palavra', fields, ('forjado.png', 'image/png', b'<?php echo "invalido"; ?>'))
assert 'Imagem inválida.' in html
assert uploaded in get(path)
print('OK arquivo disfarçado rejeitado, imagem anterior preservada')
html = get('administracao/cadastrarTexto')
html = post('administracao/salvar/texto', {'csrf_test_name': token(html), 'id': '', 'textoIncompleto': 'Na via_em, vimos uma _irafa.', 'respostas': 'g\ng', 'grafemas[]': '1'})
assert 'Conteúdo salvo.' in html
text_id = re.findall(r'administracao/editarTexto/(\d+)', html)[-1]
text_path = 'administracao/editarTexto/' + text_id
html = get(text_path)
assert 'Na via_em, vimos uma _irafa.' in html
html = post('administracao/salvar/texto', {'csrf_test_name': token(html), 'id': text_id, 'textoIncompleto': 'Na via_em, vimos uma _irafa.', 'respostas': 'g', 'grafemas[]': '1'})
assert 'Informe uma resposta por lacuna' in html
assert 'g\ng' in get(text_path)
print('OK texto incompleto rejeitado sem perder as duas respostas')
get('logoff')
html = get('administracao/cadastrarPalavra')
assert 'Entrar na administração' in html
try:
    get('minha-conta/salvar')
    raise AssertionError('Mutação por GET aceita')
except urllib.error.HTTPError as error:
    assert error.code == 405
print('OK guarda administrativa e método de mutação verificados')

# Perfil e reautenticação exercitados somente em uma conta criada por este teste.
tag = secrets.token_hex(4)
login = 'http_' + tag
email = login + '@example.invalid'
html = get('cadastro')
html = post('cadastro/salvar', {'csrf_test_name': token(html), 'nome': 'Teste HTTP', 'email': email, 'login': login, 'avatar': 'cecilia', 'senha1': 'test-http-123', 'senha2': 'test-http-123'})
assert 'Seu próximo passo' in html
get('sair')
html = get('login')
html = post('autenticar', {'csrf_test_name': token(html), 'login': login, 'senha': 'test-http-123'})
assert 'Seu próximo passo' in html
html = get('minha-conta')
html = post('minha-conta/salvar', {'csrf_test_name': token(html), 'nome': '<script>alert(1)</script>', 'email': email, 'login': login})
assert '&lt;script&gt;alert(1)&lt;/script&gt;' in html and '<script>alert(1)</script>' not in html
print('OK perfil persistido com escape de dados editáveis')
html = post('minha-conta/senha', {'csrf_test_name': token(html), 'senhaAtual': 'errada-123', 'senha1': 'new-http-123', 'senha2': 'new-http-123'})
assert 'A senha atual não confere.' in html
get('sair')
html = get('login')
html = post('autenticar', {'csrf_test_name': token(html), 'login': login, 'senha': 'test-http-123'})
assert 'Seu próximo passo' in html
print('OK alteração sem senha atual rejeitada; senha anterior funciona')
html = get('minha-conta')
html = post('minha-conta/senha', {'csrf_test_name': token(html), 'senhaAtual': 'test-http-123', 'senha1': 'new-http-123', 'senha2': 'new-http-123'})
assert 'Senha atualizada.' in html
get('sair')
html = get('login')
html = post('autenticar', {'csrf_test_name': token(html), 'login': login, 'senha': 'new-http-123'})
assert 'Seu próximo passo' in html
print('OK alteração autenticada e login com nova senha na conta isolada')
