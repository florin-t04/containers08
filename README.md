# Numele lucrării de laborator: Integrare continuă cu GitHub Actions

## Scopul lucrării:
- În cadrul acestei lucrări studenții vor învăța să configureze integrarea continuă cu ajutorul Github Actions.

## Sarcina:
- Crearea unei aplicații Web, scrierea testelor pentru aceasta și configurarea integrării continue cu ajutorul Github Actions pe baza containerelor.



## 2. Execuție pas cu pas a proiectului

1. **Clonarea și inițializarea repository-ului**
   ```bash
   git clone <URL_REPO> containers08
   cd containers08
   git init
   ```

2. **Crearea structurii de directoare**
   ```bash
   mkdir -p site/{modules,templates,styles,sql}
   mkdir tests
   ```

3. **Implementarea aplicației web**
   - Adăugare fișiere în `site/` conform structurii:
     - `modules/database.php`
     - `modules/page.php`
     - `templates/index.tpl`
     - `styles/style.css`
     - `config.php`
     - `index.php`

4. **Pregătirea schema-ului bazei de date**
   - `site/sql/schema.sql` conține comenzi SQL pentru crearea și popularea mesei `page`.

5. **Definirea testelor**
   - În `tests/testframework.php` implementăm un micro-framework pentru teste.
```bash
<?php

function message($type, $message) {
    $time = date('Y-m-d H:i:s');
    echo "{$time} [{$type}] {$message}" . PHP_EOL;
}

function info($message) {
    message('INFO', $message);
}

function error($message) {
    message('ERROR', $message);
}

function assertExpression($expression, $pass = 'Pass', $fail = 'Fail'): bool {
    if ($expression) {
        info($pass);
        return true;
    }
    error($fail);
    return false;
}

class TestFramework {
    private $tests = [];
    private $success = 0;

    public function add($name, $test) {
        $this->tests[$name] = $test;
    }

    public function run() {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            if ($test()) {
                $this->success++;
            }
            info("End test {$name}");
        }
    }

    public function getResult() {
        return "{$this->success} / " . count($this->tests);
    }
}
```

   - În `tests/tests.php` adăugăm teste pentru toate metodele claselor `Database` și `Page`.
```bash
<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$testFramework = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    // ...
}

// test 2: test count method
function testDbCount() {
    global $config;
    // ...
}

// test 3: test create method
function testDbCreate() {
    global $config;
    // ...
}

// test 4: test read method
function testDbRead() {
    global $config;
    // ...
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('table count', 'testDbCount');
$tests->add('data create', 'testDbCreate');
// ...

// run tests
$tests->run();

echo $tests->getResult();
```

6. **Containerizare cu Docker**
   - Creăm `Dockerfile` în rădăcina proiectului cu imagine bază `php:7.4-fpm` și extensia SQLite.
   - Configurăm volum pentru baza de date și copiem aplicația în container.
```bash
FROM php:7.4-fpm as base

RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite

VOLUME ["/var/www/db"]

COPY sql/schema.sql /var/www/db/schema.sql

RUN echo "prepare database" && \
    cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm -rf /var/www/db/schema.sql && \
    echo "database is ready"

COPY site /var/www/html
```

7. **Configurarea GitHub Actions**
   - Creăm `.github/workflows/main.yml` cu job-uri pentru:
     - Checkout
     - Build imagine Docker
     - Creare și pornire container
     - Copiere teste în container
     - Rulare teste
     - Oprire și ștergere container
```bash
name: CI

on:
  push:
    branches:
      - main

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Build the Docker image
        run: docker build -t containers08 .
      - name: Create `container`
        run: docker create --name container --volume database:/var/www/db containers08
      - name: Copy tests to the container
        run: docker cp ./tests container:/var/www/html
      - name: Up the container
        run: docker start container
      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container
```
8. **Testare și verificare în GitHub**
   - Commit și push pe branch `main`.
   - Verificare fila **Actions** în GitHub pentru log-urile workflow-ului.

## 3. Răspunsuri la întrebări

### 3.1. Ce este integrarea continuă?
Integrarea continuă (Continuous Integration, CI) este o practică de dezvoltare software în care modificările de cod sunt integrate frecvent într-o ramură principală a repository-ului. Fiecare integrare este verificată automat prin rularea build-urilor și a testelor, pentru a detecta rapid erori și incompatibilități.

### 3.2. Pentru ce sunt necesare testele unitare? Cât de des trebuie să fie executate?
- **Testele unitare** verifică funcționalitatea unor componente individuale de cod (clase, funcții) în izolare. Ele asigură că fiecare modul funcționează corect și ajută la detectarea regresiilor.
- Testele unitare trebuie rulate de fiecare dată când modificăm codul: local înainte de commit, în pipeline-ul CI la fiecare push și la fiecare pull request.

### 3.3. Ce modificări trebuie făcute în fișierul `.github/workflows/main.yml` pentru a rula testele la fiecare Pull Request?
Trebuie să adăugăm evenimentul `pull_request` la secțiunea `on`. Exemplu:
```yaml
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
```

### 3.4. Ce trebuie adăugat în `.github/workflows/main.yml` pentru a șterge imaginile create după testare?
După pașii de stop și `docker rm` pentru container, adăugăm un pas:
```yaml
      - name: Remove Docker image
        run: |
          docker rmi containers08 || true
```
Acest pas șterge imaginea locală `containers08` pentru a elibera spațiu.

## 4. Concluzii

- Am configurat un workflow CI complet folosind GitHub Actions și Docker.
- Testele unitare și rularea lor automată asigură îmbunătățirea continuă a calității codului.
- Practica CI/CD îmbunătățește vizibilitatea erorilor și accelerează livrarea de software fiabil.
- Extensibilitatea workflow-ului permite includerea altor etape, precum analiza statică sau deploy-ul automat.

