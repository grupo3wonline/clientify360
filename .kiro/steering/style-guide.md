# Clientify360 - Style Guide & Convenções de Código

## Idioma

- Todo o código, comentários, variáveis e documentação devem estar em **inglês**
- Toda a interface do usuário (labels, mensagens, textos) deve estar em **português (pt-BR)**
- Arquivos de tradução em `lang/pt_BR/`

## PHP / Laravel

### Nomenclatura

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| Classes | PascalCase | `ClientService` |
| Métodos | camelCase | `findByUserId()` |
| Variáveis | camelCase | `$clientData` |
| Constantes | UPPER_SNAKE | `MAX_ATTEMPTS` |
| Tabelas DB | snake_case plural | `clients`, `service_orders` |
| Colunas DB | snake_case | `created_at`, `user_id` |
| Rotas | kebab-case | `/service-orders` |
| Views | kebab-case | `service-orders/index.blade.php` |
| Config | snake_case | `app.timezone` |

### Estrutura de Classes

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ClientRepositoryInterface;
use App\DTOs\ClientDTO;

final class ClientService
{
    public function __construct(
        private readonly ClientRepositoryInterface $repository
    ) {}

    public function findAll(): Collection
    {
        return $this->repository->all();
    }
}
```

### Regras PHP
- Usar `declare(strict_types=1)` em todos os arquivos
- Usar type hints em parâmetros e retornos
- Usar `readonly` properties quando possível
- Usar `final` em classes que não devem ser estendidas
- Usar early return para reduzir aninhamento
- Máximo 20 linhas por método (ideal)
- Máximo 200 linhas por classe (ideal)

### Models

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasUuid, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        // ...
    ];

    // Scope para isolamento de dados
    protected static function booted(): void
    {
        static::addGlobalScope('owned', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });
    }
}
```

### Controllers

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;

final class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $service
    ) {}

    public function index()
    {
        $clients = $this->service->findAll();
        return view('clients.index', compact('clients'));
    }

    public function store(StoreClientRequest $request)
    {
        $this->service->create($request->validated());
        return redirect()->route('clients.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }
}
```

## Frontend (Blade + Alpine.js + Tailwind)

### Blade Templates

- Usar layouts com `@extends` e `@section`
- Componentes reutilizáveis com `<x-component>`
- Breadcrumbs em todas as páginas internas
- `autocomplete="off"` em todos os inputs

### Alpine.js

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>
        Conteúdo
    </div>
</div>
```

### Tailwind CSS (Mobile First)

```html
<!-- Mobile first: base é mobile, md: tablet, lg: desktop -->
<div class="p-4 md:p-6 lg:p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- cards -->
    </div>
</div>
```

### Tema Claro/Escuro

```html
<html class="dark" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      :class="{ 'dark': darkMode }">
```

### SweetAlert2

```javascript
Swal.fire({
    title: 'Sucesso!',
    text: 'Registro salvo com sucesso.',
    icon: 'success',
    confirmButtonText: 'OK'
});
```

### Font Awesome

```html
<i class="fa-solid fa-users"></i>
<i class="fa-solid fa-chart-line"></i>
```

## Estrutura de Arquivos por Feature

```
// Para cada entidade/módulo:
app/
├── Models/Client.php
├── Http/
│   ├── Controllers/ClientController.php
│   ├── Requests/
│   │   ├── StoreClientRequest.php
│   │   └── UpdateClientRequest.php
│   └── Resources/ClientResource.php
├── Services/ClientService.php
├── Repositories/ClientRepository.php
├── Interfaces/ClientRepositoryInterface.php
├── DTOs/ClientDTO.php
├── Actions/CreateClientAction.php
└── Entities/ClientEntity.php

database/
├── migrations/xxxx_create_clients_table.php
├── factories/ClientFactory.php
└── seeders/ClientSeeder.php

resources/views/clients/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

## Commits

- Usar Conventional Commits em português:
  - `feat: adicionar cadastro de clientes`
  - `fix: corrigir validação de email`
  - `refactor: extrair lógica para ClientService`
  - `docs: atualizar README com instruções de instalação`

## Testes

- Feature tests para controllers e rotas
- Unit tests para services e repositories
- Nomenclatura: `test_it_can_create_a_client()`
