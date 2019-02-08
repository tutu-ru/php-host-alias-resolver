# Библиотека HostAliasResolver

Библиотека умеет три вещи:
* Получить полный uri адреса с опредением хоста по алиасу (resolve)
* Получить хост по алиасу или адрес какого-либо ресурса (getHostByAlias)
* Получить поддомен к текущему основному домену для сервисов (getWithEnvDomain)

## Конфигурация

Конфигурационные ноды:
* `infrastrucrute.env.domain` - основной домен для сервисов
* `infrastructure.host_alias_resolver` - хранилище алиасов хостов и адресов ресурсов, например:
  * `rus.desktop.mainpage` - главная страница десктопной русской версии сайта
  * `external.partnerApi` - адрес партнерского апи
  * `internal.ourApi` - адрес внутреннего ресурса

## Инициализация

```php
/** @var \TutuRu\Config\ConfigInterface $configContainer */
$resolver = new HostAliasResolver($configContainer);

print $resolver->resolve('mainpage.desktop.rus/query?arg=1#hash');
print $resolver->getHostByAlias('services.partnerApi');
```

## Особенности использования
Алиасы надо задавать в обратном порядке

resolve:
```php
// rus.desktop.mainpage = main.host.com

print $resolver->resolve('mainpage.desktop.rus/query?arg=1#hash');
// выведет https://main.host.com/query?arg=1#hash
// https:// в ноде нет, resolve подставляет его сам 
``` 

getHostByAlias:
```php
// external.partnerApi = https://somerestapi.com

print $resolver->getHostByAlias('partnerApi.external');
// выведет https://somerestapi.com
```
*Если ноды для алиаса нет - вернется `getWithEnvDomain` для того же алиаса.*


