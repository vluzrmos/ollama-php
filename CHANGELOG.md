# Changelog

## [1.1.0] - 2025-01-14

### Added
- Suporte para API Token (Bearer authentication)
- Compatibilidade com OpenAI API
- Compatibilidade com qualquer API no formato OpenAI
- Métodos `setApiToken()` e `getApiToken()` no OllamaClient
- Configuração de token via construtor ou método
- Exemplos de uso com diferentes provedores de API
- Testes para funcionalidade de token

### Changed
- HttpClient agora suporta header Authorization
- Documentação atualizada com exemplos de autenticação

## [1.0.0] - 2025-01-14

### Added
- Cliente PHP 5.6 para API do Ollama
- Suporte completo para todos os endpoints da API
- Autoloading PSR-4 via Composer
- Classes de modelo para estruturas de dados
- Utilitários para validação e manipulação de imagens
- Suporte para streaming de respostas
- Tool calling (function calling)
- Outputs estruturados (JSON schema)
- Suporte para imagens em modelos multimodais
- Exemplos de uso básico e avançado
- Testes unitários básicos
- Documentação completa

### Features
- ✅ Generate completion
- ✅ Chat completion 
- ✅ List models
- ✅ Show model information
- ✅ Pull model
- ✅ Push model
- ✅ Create model
- ✅ Delete model
- ✅ Copy model
- ✅ Generate embeddings
- ✅ List running models
- ✅ Version information
- ✅ Tool calling
- ✅ Structured outputs
- ✅ Image support (multimodal)
- ✅ Blob management
