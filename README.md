## Reversive templator

Простенький шаблонизатор, младший брат smarty или mustache, назовем его Dummy. Все что он умеет это подставлять экранированные и неэкранированные (html-escaped) переменные в строку.

### Пример использования
На вход подаются `шаблон` (`template`) и `результат` (`result`), на выходе ожидается `массив переменных` (`params`)

Входные данные:
- *template* `Hello, my name is {{name}}.`
- *result* `Hello, my name is Juni.`

Выходные параметры:
- *params* `['name' => "Juni"]`


### Граничные условия
- строки регистрозависимы
- имя переменной не должно содержать специальные символы


### Запуск с Docker

Установка зависимостей

    docker-compose run --rm php-fpm composer install

Запуск контейнера

    docker-compose up -d

### Запуск тестов

    vendor/bin/codecept run unit models/DummyTemplateTest
