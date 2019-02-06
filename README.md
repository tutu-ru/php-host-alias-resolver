# Библиотека HostAliasResolver

Библиотека умеет три вещи:
* Получить полный uri адреса с опредением хоста по алиасу (resolve)
* Получить хост по алиасу или адрес какого-либо ресурса (getHostByAlias)
* Получить поддомен к текущему основному домену для сервисов (getWithEnvDomain)

## Конфигурация

Конфигурационные ноды:
* `infrastrucrute.env.domain` - основной домен для сервисов
* `infrastructure.host_alias_resolver` - хранилище алиасов хостов и адресов ресурсов
  * `rus.desktop.mainpage` - главная страница десктопной русской версии сайта
  * `services.ServiceName` - адрес сервиса ServiceName

## Инициализация

```php
/** @var \TutuRu\Config\ConfigInterface $configContainer */
$resolver = new HostAliasResolver($configContainer);

print $resolver->resolve('mainpage.desktop.rus/query?arg=1#hash');
print $resolver->getHostByAlias('services.partnerApi');
```

## Особенности использования
Метод `getHostByAlias` получает ноду в прямом порядке:
```php
// services.partnerApi = https://somerestapi.com

print $resolver->getHostByAlias('services.partnerApi');
// выведет https://somerestapi.com
```
*Если ноды для алиаса нет - вернется `getWithEnvDomain` для того же алиаса.*


Для метода `resolve` алиасы надо задавать в обратном порядке:
```php
// rus.desktop.mainpage = main.host.com

print $resolver->resolve('mainpage.desktop.rus/query?arg=1#hash');
// выведет https://main.host.com/query?arg=1#hash
// https:// в ноде нет, resolve подставляет его сам 
``` 