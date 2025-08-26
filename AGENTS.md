# Ollama/OpenAI Client - Agents Instructions

The project goals to support php 5.6+ and provide an easy-to-use interface to interact with Ollama server and also includes OpenAI API compatibility.

# Development Guidelines (fully compatible with PHP 5.6+)

 - Always use short array syntax `[]` instead of `array()`.
 - Always use `<?php` tags, avoid short tags `<?`.
 - Always use `::class` constant to reference classes instead of `"Fully\\Qualified\\ClassName"`. Example: `MyClass::class`.
 - Avoid using scalar type hints and return type declarations.
 - Avoid using named arguments in function calls.
 - Avoid using the null coalescing operator (`??`), use `isset()` instead.
 - Use type hints in functions or methods only for classes, interfaces, arryays, and callable types.
 - All documentation, comments and strings should be in English.

# Running

Project has Dockerfile with all dependencies installed, you can run it with:

```bash 
docker build -t ollama-php .
docker run -it --rm ollama-php
```

## Docker Environment Variables

You can set the following environment variables to configure the client:


| Variable | Default Value | Description |
| :------- | :-----------: | :---------- |
| OPENAI_API_URL | http://localhost:11434/v1 | Defines the base URL for OpenAI API requests |
| OLLAMA_API_URL | http://localhost:11434 | Defines the base URL for Ollama API requests |
| RUN_INTEGRATION_TESTS | 1 | Set to 1 to enable integration tests, requires Ollama server running locally |
| TEST_MODEL | llama3.2:1b | Defines the model to be used in tests |

> Make sure to adjust the URLs and ports according to your Ollama/OpenAI server configuration.

### Example using variables

```bash
docker run -it --rm \
-e OPENAI_API_URL=http://your-ollama-server:port/v1 \
-e OLLAMA_API_URL=http://your-ollama-server:port \
ollama-php
```

### Running custom commands or custom project folder (development mode)
You can run custom commands inside the Docker container using the following syntax:

```bash
docker run -it --rm ollama-php php path/to/your/your-script.php
```

The project workdir inside the container is `/app`, so you can mount your local files using Docker's `-v` option if needed.

```bash
docker run -it --rm -v /path/to/your/local/files:/app ollama-php php your-script.php
```

> Note: You may need to install the dependencies inside the container if you share a volume in /app. You can do this by running `composer install` inside the container. `docker run -it --rm -v /path/to/your/local/files:/app ollama-php composer install`
