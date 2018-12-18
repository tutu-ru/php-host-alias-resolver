# Библиотека HostAliasResolver

Библиотека для получения uri адреса по алиасу

## Конфигурация

`host_alias_resolver`: нужные алиасы в обратном порядке. т.е. для `mainpage.desktop.rus` добавлять `rus.desktop.mainpage=main.host.com`
`env.domain`: основной домен для сервисов

пример: `host_alias_resolver.rus.desktop.mainpage=main.host.com` 

## Инициализация

```php
$resolver = new HostAliasResolver($configContainer);

print $resolver->resolve('mainpage.desktop.rus/query?arg=1#hash'); //https://main.host.com/query?arg=1#hash
print $resolver->resolve('legal/api/v1/getList?id=1'); //http://main.host.com/api/v1/getList?id=1
```
