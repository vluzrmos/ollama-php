# Contribuindo

Obrigado por seu interesse em contribuir com o Ollama PHP 5.6 Client!

## Como Contribuir

### Reportando Bugs

1. Verifique se o bug já não foi reportado nos issues existentes
2. Crie um novo issue com:
   - Descrição clara do problema
   - Passos para reproduzir
   - Versão do PHP
   - Versão do Ollama
   - Código de exemplo que demonstra o problema

### Solicitando Features

1. Abra um issue descrevendo:
   - A funcionalidade desejada
   - Casos de uso
   - Exemplos de como deveria funcionar

### Enviando Pull Requests

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Faça suas alterações
4. Adicione testes se aplicável
5. Execute os testes existentes
6. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
7. Push para a branch (`git push origin feature/nova-feature`)
8. Abra um Pull Request

## Padrões de Código

- Siga as PSR-1 e PSR-2 para estilo de código PHP
- Use comentários PHPDoc para documentar métodos e classes
- Mantenha compatibilidade com PHP 5.6+
- Escreva testes para novas funcionalidades

## Configurando o Ambiente de Desenvolvimento

```bash
# Clone o projeto
git clone https://github.com/vluzr/ollama-php56.git
cd ollama-php56

# Instale as dependências
composer install

# Execute os testes
./vendor/bin/phpunit
```

## Testando

Certifique-se de que:
1. Todos os testes passam
2. Sua contribuição inclui testes adequados
3. O código funciona com PHP 5.6, 7.x e 8.x

## Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a mesma licença MIT do projeto.
