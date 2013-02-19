# Database Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)
[Metodlar: databaseConnection](#methodsDatabaseConnection)
[Metodlar: databaseQuery](#methodsDatabaseQuery)
[Metodlar: databaseQueryResult](#methodsDatabaseQueryResult)

## Rutin ## {#routine}

+ yüklendiğinde config dosyalarından konfigurasyon bilgilerini okur.

## Alanlar ## {#fields}
[$database](#database)
[$datasets](#datasets)
[$default](#default)

### $database ### {#database}
{array} database::$databases

> Kayıt edilmiş database bağlantı bilgilerini saklar.

### $datasets ### {#datasets}
{array} database::$datasets

> Tanımlanmış dataset bilgilerini saklar.

### $default ### {#default}
{databaseConnection} database::$default

> Varsayılan olarak seçilmiş veritabana ait bilgileri saklar.

## Metodlar ## {#methods}
[get](#get)

### get ### {#get}
{databaseConnection} database::get([$key])

> Eğer belirtilmişse parametre olarak belirtilmiş veritabanı nesnesini döndürür. Aksi takdirde varsayılan veritabanı nesnesini döndürür.

~~~

// Komut:
$database = database::get();

~~~

## Metodlar: databaseConnection ## {#methodsDatabaseConnection}
[open](#open)
[close](#close)
[beginTransaction](#beginTransaction)
[commit](#commit)
[rollBack](#rollBack)
[execute](#execute)
[query](#query)
[lastInsertId](#lastInsertId)
[serverInfo](#serverInfo)
[dataset](#dataset)
[createQuery](#createQuery)

### open ### {#open}
{void} databaseConnection->open()
> İlgili database bağlantısını sağlar.

### close ### {#close}
{void} databaseConnection->close()
> İlgili database bağlantısını sonlandırır.

### beginTransaction ### {#beginTransaction}
{void} databaseConnection->beginTransaction()
> Yeni bir transaction oluşturur.

### commit ### {#commit}
{void} databaseConnection->commit()
> Oluşan transaction'u commit eder.

### rollBack ### {#rollBack}
{void} databaseConnection->rollBack()
> Oluşan transaction'u geri alır.

### execute ### {#execute}
{void} databaseConnection->execute($query)
> Belirtilen sorguyu parametreler olmaksızın çalıştırır.

~~~

// Komut:
database::get()->execute(
	'SET NAMES utf8'
)

~~~

### query ### {#query}
{databaseQueryResult} databaseConnection->query($query[, $parameters = array(), $caching = database::CACHE_MEMORY])
> Belirtilen sorguyu parametreleriyle birlikte çalıştırır, geriye sonuç objesi döndürür.

~~~

// Komut:
database::get()->query(
	'SELECT * FROM users WHERE auth=:auth AND hometown=:hometown',
	array('auth' => 1, 'hometown' => $hometown)
);

~~~

### lastInsertId ### {#lastInsertId}
{mixed} databaseConnection->lastInsertId([$sequenceName = null])
> INSERT sorgusu sonucunda veritabanına yazılmış ID bilgisini döndürür.

~~~

// Komut:
$database = database::get();
$database->query(
	'INSERT INTO test (b) VALUES (:b)',
	array('b' => 1)
)->execute();

$database->lastInsertId('testsequence')

// Sonuç:
string('35')

~~~

### serverInfo ### {#serverInfo}
{mixed} databaseConnection->serverInfo()
> Database sunucusuna ait runtime bilgisi döndürür.

~~~

// Komut:
database::get()->serverInfo()

// Sonuç:
string('PID: 26037; Client Encoding: UTF8; Is Superuser: off; Session Authorization: scabbia; Date Style: ISO, MDY')

~~~

### dataset ### {#dataset}
{databaseQueryResult} databaseConnection->dataset([$parameters ...])
> Belirtilen dataset'i verilen parametrelerle birlikte çalıştırır, geriye sonuç objesi döndürür.

~~~

// Komut:
database::get()->dataset('createUser', 'eser', 'password', 'izmir', 1)->execute();

~~~

### createQuery ### {#createQuery}
{databaseQuery} databaseConnection->createQuery()
> Bağlantı üzerinden çalışacak yeni bir sorgu nesnesi oluşturur.

~~~

// Komut:
$query = database::get()->createQuery();

~~~

## Metodlar: databaseQuery ## {#methodsDatabaseQuery}
[setDatabase](#setDatabase)
[setDatabaseName](#setDatabaseName)
[clear](#clear)
[setTable](#setTable)
[joinTable](#joinTable)
[setFields](#setFields)
[setFieldsDirect](#setFieldsDirect)
[addField](#addField)
[addFieldDirect](#addFieldDirect)
[addParameter](#addParameter)
[setWhere](#setWhere)
[andWhere](#andWhere)
[orWhere](#orWhere)
[setGroupBy](#setGroupBy)
[addGroupBy](#addGroupBy)
[setOrderBy](#setOrderBy)
[addOrderBy](#addOrderBy)
[setLimit](#setLimit)
[setOffset](#setOffset)
[setSequence](#setSequence)
[setReturning](#setReturning)
[setCaching](#setCaching)
[insert](#insert)
[update](#update)
[delete](#delete)
[getQuery](#getQuery)
[get](#get2)
[calculate](#calculate)

### setDatabase ### {#setDatabase}
{void} databaseQuery->setDatabase([$database = null])
> Sorgu nesnesinin üzerinde çalışacağı database'i belirler.

### setDatabaseName ### {#setDatabaseName}
{void} databaseQuery->setDatabaseName($databaseName)
> Sorgu nesnesinin üzerinde çalışacağı database'in ismini vererek, database'in belirlenmesini sağlar.

### clear ### {#clear}
{void} databaseQuery->clear()
> Oluşturulan sorgu için hazırlanan parametre bilgilerini temizler.

### setTable ### {#setTable}
{databaseQuery} databaseQuery->setTable($table)
> Hedef tabloyu belirler.

### joinTable ### {#joinTable}
{databaseQuery} databaseQuery->joinTable($table, $condition[, $joinType = 'INNER'])
> Hedef tablo listesine ek bir join tablo ekler.

### setFields ### {#setFields}
{databaseQuery} databaseQuery->setFields($array)
> Sorguda kullanılacak alanları parametreler yardımıyla ekler.

### setFieldsDirect ### {#setFieldsDirect}
{databaseQuery} databaseQuery->setFieldsDirect($array)
> Sorguda kullanılacak alanları direkt olarak ekler.

### addField ### {#addField}
{databaseQuery} databaseQuery->addField($field[, $value = null])
> Sorguda kullanılacak yeni bir alanı parametreler yardımıyla ekler.

### addFieldDirect ### {#addFieldDirect}
{databaseQuery} databaseQuery->addFieldDirect($field, $value)
> Sorguda kullanılacak yeni bir alanı direkt olarak ekler.

### addParameter ### {#addParameter}
{databaseQuery} databaseQuery->addParameter($parameter, $value)
> Sorguda kullanılacak yeni bir parametre ekler.

### setWhere ### {#setWhere}
{databaseQuery} databaseQuery->setWhere($condition[, $list = null])
> Sorguda kullanılacak WHERE deyimini düzenler.

### andWhere ### {#andWhere}
{databaseQuery} databaseQuery->andWhere($condition[, $list = null, $keyword = 'OR'])
> Sorguda kullanılacak WHERE deyimine yeni bir AND şartı ekler.

### orWhere ### {#orWhere}
{databaseQuery} databaseQuery->orWhere($condition[, $list = null, $keyword = 'OR'])
> Sorguda kullanılacak WHERE deyimine yeni bir OR şartı ekler.

### setGroupBy ### {#setGroupBy}
{databaseQuery} databaseQuery->setGroupBy($groupby)
> Sorguda kullanılacak GROUP BY deyimini düzenler.

### addGroupBy ### {#addGroupBy}
{databaseQuery} databaseQuery->addGroupBy($groupby)
> Sorguda kullanılacak GROUP BY deyimine yeni bir alan ekler.

### setOrderBy ### {#setOrderBy}
{databaseQuery} databaseQuery->setOrderBy($orderby[, $order = null])
> Sorguda kullanılacak ORDER BY deyimini düzenler.

### addOrderBy ### {#addOrderBy}
{databaseQuery} databaseQuery->addOrderBy($orderby[, $order = null])
> Sorguda kullanılacak ORDER BY deyimine yeni bir alan ekler.

### setLimit ### {#setLimit}
{databaseQuery} databaseQuery->setLimit($limit)
> Sorguda kullanılacak LIMIT deyimini düzenler.

### setOffset ### {#setOffset}
{databaseQuery} databaseQuery->setOffset($offset)
> Sorguda kullanılacak OFFSET deyimini düzenler.

### setSequence ### {#setSequence}
{databaseQuery} databaseQuery->setSequence($sequence)
> Sorguda kullanılacak SEQUENCE deyimini düzenler.

### setReturning ### {#setReturning}
{databaseQuery} databaseQuery->setReturning($returning)
> Sorguda kullanılacak RETURNING deyimini düzenler.

### setCaching ### {#setCaching}
{databaseQuery} databaseQuery->setCaching($caching)
> Sorgunun cache'e alınıp alınmayacağını belirler.

### insert ### {#insert}
{databaseQueryResult} databaseQuery->insert()
> Belirtilmiş parametrelerle bir INSERT deyimi oluşturur ve çalıştırır.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('name', 'eser')
							->addField('password', 'password')
							->addField('hometown', 'izmir')
							->addField('auth', 1)
							->insert()
							->execute();

~~~

### update ### {#update}
{databaseQueryResult} databaseQuery->update()
> Belirtilmiş parametrelerle bir UPDATE deyimi oluşturur ve çalıştırır.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('password', 'password')
							->addField('auth', 1)
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->setLimit(1)
							->update()
							->execute();

~~~

### delete ### {#delete}
{databaseQueryResult} databaseQuery->delete()
> Belirtilmiş parametrelerle bir DELETE deyimi oluşturur ve çalıştırır.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->setLimit(1)
							->delete()
							->execute();

~~~

### getQuery ### {#getQuery}
{string} databaseQuery->getQuery()
> Belirtilmiş parametrelerle bir SELECT deyimi oluşturur ve geri döndürür.

~~~

// Komut:
$sqlQuery = database::get()->createQuery()
							->setTable('users')
							->addField('*')
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->andWhere('deletedate IS NULL')
							->getQuery();

// Sonuç:
string('SELECT * FROM users WHERE userid=:userid AND deletedate IS NULL')

~~~

### get ### {#get2}
{databaseQueryResult} databaseQuery->get()
> Belirtilmiş parametrelerle bir SELECT deyimi oluşturur ve çalıştırır.

~~~

// Komut (Tek Row):
$result = database::get()->createQuery()
							->setTable('users')
							->addField('*')
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->andWhere('deletedate IS NULL')
							->setLimit(1)
							->get()
							->row();

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('*')
							->setWhere('deletedate IS NULL')
							->get()
							->all();

~~~

### calculate ### {#calculate}
{databaseQueryResult} databaseQuery->calculate($table[, $operation = 'COUNT', $field = '*', $where = null)
> Parametrelere gerek kalmaksızın tablo üzerinde bir AGGREGATE fonksiyon çalıştırır.

~~~

// Komut:
$result = database::get()->calculate('users', 'COUNT', '*', 'auth=\'1\'');

~~~

## Metodlar: databaseQueryResult ## {#methodsDatabaseQueryResult}
[execute](#execute)
[all](#all)
[column](#column)
[row](#row)
[scalar](#scalar)
[close](#close2)
[lastInsertId](#lastInsertId)

### execute ### {#execute}
{void} databaseQueryResult->execute()
> Oluşturulan sorguyu direkt olarak çalıştırır.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('password', 'password')
							->addField('auth', 1)
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->setLimit(1)
							->update()
							->execute();

~~~

### all ### {#all}
{array} databaseQueryResult->all()
> Oluşturulan sorguyu çalıştırır ve tüm sonucu döndürür.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('*')
							->setWhere('deletedate IS NULL')
							->get()
							->all();

// Sonuç:
array(4) {
	[0] = array(6) {
		[userid] = string('291a615a-c7da-454b-aea9-2abfd866e78c')
		[username] = string('eser')
		[password] = string('password')
		[auth] = integer('1')
		[deletedate] = null
		[deleteowner] = null
	}
	[1] = array(6) {
		[userid] = string('1e0c7552-cae2-4a14-856a-774846969908')
		[username] = string('cengiz')
		[password] = string('password')
		[auth] = integer('1')
		[deletedate] = null
		[deleteowner] = null
	}
}

~~~

### column ### {#column}
{array} databaseQueryResult->column($key)
> Oluşturulan sorguyu çalıştırır ve tüm sonuçtan yalnızca tek sütun döndürür.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('username')
							->addField('*')
							->setWhere('deletedate IS NULL')
							->get()
							->column('username');

// Sonuç:
array(2) {
	[0] = string('eser')
	[1] = string('cengiz')
}

~~~

### row ### {#row}
{array} databaseQueryResult->row()
> Oluşturulan sorguyu çalıştırır ve sonuçtan yalnızca tek satır döndürür.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('*')
							->addParameter('userid', $userid)
							->setWhere('userid=:userid')
							->andWhere('deletedate IS NULL')
							->setLimit(1)
							->get()
							->row();

// Sonuç:
array(6) {
	[userid] = string('291a615a-c7da-454b-aea9-2abfd866e78c')
	[username] = string('eser')
	[password] = string('password')
	[auth] = integer('1')
	[deletedate] = null
	[deleteowner] = null
}

~~~

### scalar ### {#scalar}
{mixed} databaseQueryResult->scalar([$column = 0, $default = false])
> Oluşturulan sorguyu çalıştırır ve sonuçtan tek hücre döndürmeye çalışır, aksi takdirde false döndürür.

~~~

// Komut:
$result = database::get()->createQuery()
							->setTable('users')
							->addField('COUNT(*)')
							->setWhere('deletedate IS NULL')
							->get()
							->scalar();

// Sonuç:
string('2')

~~~

### close ### {#close2}
{void} databaseQueryResult->close()
> Oluşturulan sorguya ait database cursor'unu serbest bırakır.

~~~

// Komut:
$result->close();

~~~

### lastInsertId ### {#lastInsertId}
{void} databaseQueryResult->lastInsertId()
> Oluşturulan sorgu sonucunda veritabanına yazılmış ID bilgisini döndürür.

~~~

// Komut:
$result->lastInsertId();

~~~

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com