## Консольная команда

```
php bin/console app:parse <type>
```
Парсит захардкоженные данные.

Типы:
- json
- svg
- html
## Запросы
 ```
 api/data 
  ```
Возвращает
```
{
    "name": "some name",
    "site": "site",
    "count": 1,
    "tickets": {
        "1": {
            "sector": "A",
            "row": 3,
            "seat": 14,
            "price": 50
        }
}
```
