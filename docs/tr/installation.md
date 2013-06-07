# Kurulum #

Aşağıdaki iki alternatiften herhangi biri ile mevcut en güncel sürümün bir kopyasını elde edebilirsiniz.

### Alternatif 1 ###

[Skeleton Uygulamasını](https://github.com/larukedi/Scabbia-Skeleton/archive/master.zip) indirerek `./composer_update.sh` veya `composer_update.cmd`'yi çalıştırabilirsiniz.

### Alternatif 2 ###

*nix üzerinde:

- `php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"`
- `php composer.phar create-project larukedi/scabbia-skeleton -s dev`

Windows üzerinde:

- [Composer-Setup.exe](http://getcomposer.org/Composer-Setup.exe)'yi indirip kurun. Ardından:
- `composer create-project larukedi/scabbia-skeleton -s dev`



## Gereksinimler ##

### PHP ###
Scabbia Framework'ü kullanabilmek için tek şart **PHP 5.3.7** veya daha güncel bir PHP kurulumuna sahip olmak olsa dahi. Daha düzgün bir çalışma ortamı ve tam işlevsellik adına ekstra gereksinimler bulunmaktadır.

Veritabanı desteği için **PDO** veya **Mysqli** uzantılarından birinin sunucu üzerinde kurulu olduğunu varsayıyoruz.

Yine çoklu dil desteği için mbstring uzantısına gereksinim duyulmaktadır.


### Web Sunucu ###
Scabbia Framework Apache, IIS ve Nginx üzerinde Debian ve Windows platformlarında test edilmiştir.

Eğer web sunucunuz **url rewrite** kapasitesine sahip ise, framework'e ait **Fancy URL** özelliğini kullanabileceksiniz. Bunun için kurulum paketi içerisinde ayrıca .htaccess veya web.config dosyalarını bulabilirsiniz.


## Kurulum Adımları ##
Aşağıdaki adımları takip ederek Scabbia Framework kurulumunu gerçekleştirebilirsiniz:

* Scabbia Framework'ü [Github Repository](http://larukedi.github.com/Scabbia-Framework/)'den forklayın veya [zip paketi](http://larukedi.github.com/Scabbia-Framework/archive/master.zip) olarak indirin
* İndirdiğiniz dosyaları web sunucuya yerleştirin
* Dosyaları yerleştirdiğiniz web konumunu ziyaret ederek kuruluma web arabirimi üzerinden devam edin

Daha sonraki adımlar için [Başlarken](gettingstarted.md) bölümü incelenebilir.