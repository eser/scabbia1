# Access Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}

+ yüklendiğinde config dosyalarından konfigurasyon bilgilerini okur.
+ çalıştırıldığında bakım modu kontrolü yapar.
+ çalıştırıldığında ip kontrolü yapar.

## Alanlar ## {#fields}
[$maintenance](#maintenance)
[$maintenanceExcludeIps](#maintenanceExcludeIps)
[$ipFilters](#ipFilters)

### $maintenance ### {#maintenance}
{bool} access::$maintenance

> Sistemin bakımda olup olmadığı bilgisini saklar.

### $maintenanceExcludeIps ### {#maintenanceExcludeIps}
{array} access::$maintenanceExcludeIps

> Sistem bakımdayken hangi ip'lerin sisteme erişebileceği bilgisini saklar.

### $ipFilters ### {#ipFilters}
{array} access::$ipFilters

> Sistem tarafında izinli ve yasaklı ip listesini saklar.

## Metodlar ## {#methods}

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com