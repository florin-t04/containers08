# Laboratorul 8: Integrare continuă cu GitHub Actions

## 1. Descrierea proiectului

**Numele lucrării de laborator:** Integrare continuă cu GitHub Actions

**Scopul lucrării:**
- Configurarea procesului de integrare continuă folosind GitHub Actions și containere Docker pentru o aplicație PHP cu bază SQLite.

**Sarcina:**
- Crearea unei aplicații web PHP cu operații CRUD pe o bază de date SQLite.
- Scrierea testelor unitare pentru clasele `Database` și `Page`.
- Containerizarea aplicației cu Docker.
- Configurarea unui workflow CI în GitHub Actions pentru rularea automată a testelor.

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
   - În `tests/tests.php` adăugăm teste pentru toate metodele claselor `Database` și `Page`.

6. **Containerizare cu Docker**
   - Creăm `Dockerfile` în rădăcina proiectului cu imagine bază `php:7.4-fpm` și extensia SQLite.
   - Configurăm volum pentru baza de date și copiem aplicația în container.

7. **Configurarea GitHub Actions**
   - Creăm `.github/workflows/main.yml` cu job-uri pentru:
     - Checkout
     - Build imagine Docker
     - Creare și pornire container
     - Copiere teste în container
     - Rulare teste
     - Oprire și ștergere container

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

