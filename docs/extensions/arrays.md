# Arrays Extension[^1] #
[Rutin](#routine)
[Alanlar](#fields)
[Metodlar](#methods)

## Rutin ## {#routine}


## Alanlar ## {#fields}


## Metodlar ## {#methods}
[flat](#flat)
[getFirst](#getFirst)
[get](#get)
[getArray](#getArray)
[getRandom](#getRandom)
[range](#range)
[sortByKey](#sortByKey)
[categorize](#categorize)
[assignKeys](#assignKeys)
[column](#column)
[getRow](#getRow)
[getRowKey](#getRowKey)
[getRows](#getRows)
[combine](#combine)
[combine2](#combine2)
[sortByPriority](#sortByPriority)

### flat ### {#flat}
{array} arrays::flat($values ...)

> İç içe olan tüm array bilgisini tek boyuta indirerek düzleştirir.

~~~

// Önce:
array(4) {
	[0] = string('test')
	[1] = array(2) {
		[0] = string('fgdfgd')
		[1] = string('reewr')
	}
	[test] = string('qwerwqe')
	[test2] = array(1) {
		[0] = string('asdasds')
	}
}

// Komut:
$arrays = array(
	'test',
	array('fgdfgd', 'reewr'),
	'test' => 'qwerwqe',
	'test2' => array('asdasds')
);
arrays::flat($arrays)

// Sonra:
array(5) {
	[0] = string('test')
	[1] = string('fgdfgd')
	[2] = string('reewr')
	[3] = string('qwerwqe')
	[4] = string('asdasds')
}

~~~

### getFirst ### {#getFirst}
{mixed} arrays::getFirst($array[, $default = null])

> Array'e ait ilk element'i döndürür.

~~~

// Önce:
array(3) {
	[0] = string('test')
	[1] = string('test2')
	[3] = string('test3')
}

// Komut:
$arrays = array(
	'test',
	'test2',
	'test3'
);
arrays::getFirst($arrays)

// Sonra:
string('test')

~~~

### get ### {#get}
{mixed} arrays::get($array, $key[, $default = null])

> Bir array içerisinden $key ile belirtilen elemanı okumaya çalışır, eleman bulunmuyorsa $default ile belirtilmiş değeri döndürür.

~~~

// Önce:
array(4) {
	[0] = string('test')
	[1] = array(2) {
		[0] = string('fgdfgd')
		[1] = string('reewr')
	}
	[test] = string('qwerwqe')
	[test2] = array(1) {
		[0] = string('asdasds')
	}
}

// Komut:
$arrays = array(
	'test',
	array('fgdfgd', 'reewr'),
	'test' => 'qwerwqe',
	'test2' => array('asdasds')
);
arrays::get($arrays, 'test2')

// Sonra:
array(1) {
	[0] = string('asdasds')
}

~~~

### getArray ### {#getArray}
{array} arrays::getArray($array, $values ...)

> Array içerisinden parametre listesinde belirtilmiş elemanları döndürerek yeni bir array oluşturur.

~~~

// Önce:
array(4) {
	[0] = string('test')
	[1] = array(2) {
		[0] = string('fgdfgd')
		[1] = string('reewr')
	}
	[test] = string('qwerwqe')
	[test2] = array(1) {
		[0] = string('asdasds')
	}
}

// Komut:
$arrays = array(
	'test',
	array('fgdfgd', 'reewr'),
	'test' => 'qwerwqe',
	'test2' => array('asdasds')
);
arrays::getArray($arrays, 'test', 'test2')

// Sonra:
array(2) {
	[0] = string('test')
	[1] = array(2) {
		[0] = string('fgdfgd')
		[1] = string('reewr')
	}
}

~~~

### getRandom ### {#getRandom}
{mixed} arrays::getRandom($array)

> Array içerisinden rastgele bir eleman döndürür.

~~~

// Önce:
array(4) {
	[0] = string('test')
	[1] = array(2) {
		[0] = string('fgdfgd')
		[1] = string('reewr')
	}
	[test] = string('qwerwqe')
	[test2] = array(1) {
		[0] = string('asdasds')
	}
}

// Komut:
$arrays = array(
	'test',
	array('fgdfgd', 'reewr'),
	'test' => 'qwerwqe',
	'test2' => array('asdasds')
);
arrays::getRandom($arrays)

// Sonra:
string('qwerwqe')

~~~

### range ### {#range}
{array} arrays::range($minimum, $maximum[, $withKeys = false])

> Belirtilmiş aralıktaki sayılarla yeni bir array oluşturur. $withKeys parametresi true olarak belirtildiyse elemanların key'leri değerleri ile aynı olur.

~~~

// Komut:
arrays::range(1900, 1910)

// Sonra:
array(11) {
	[0] = integer('1900')
	[1] = integer('1901')
	[2] = integer('1902')
	[3] = integer('1903')
	[4] = integer('1904')
	[5] = integer('1905')
	[6] = integer('1906')
	[7] = integer('1907')
	[8] = integer('1908')
	[9] = integer('1909')
	[10] = integer('1910')
}

~~~

### sortByKey ### {#sortByKey}
{array} arrays::sortByKey($array, $key[, $order = 'asc'])

> Array'i değerlerin sahip oldukları key'lerin sırasına göre sıralar.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::sortByKey($arrays2, 'name', 'asc')

// Sonra:
array(4) {
	[0] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

~~~

### categorize ### {#categorize}
{array} arrays::categorize($array, $key)

> İç içe bir array yapısını, iç array'deki bir key'e göre kategorize eder.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::categorize($arrays2, 'dep')

// Sonra:
array(2) {
	[IT] = array(2) {
		[0] = array(2) {
			[name] = string('eser')
			[dep] = string('IT')
		}
		[1] = array(2) {
			[name] = string('cengiz')
			[dep] = string('IT')
		}
	}
	[MIS] = array(2) {
		[0] = array(2) {
			[name] = string('baris')
			[dep] = string('MIS')
		}
		[1] = array(2) {
			[name] = string('hasan')
			[dep] = string('MIS')
		}
	}
}

~~~

### assignKeys ### {#assignKeys}
{array} arrays::assignKeys($array, $key)

> İç içe bir array yapısında iç array'deki bir key'in dış array için de key olmasını sağlar.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::assignKeys($arrays2, 'name')

// Sonra:
array(4) {
	[eser] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[cengiz] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[baris] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[hasan] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

~~~

### column ### {#column}
{array} arrays::column($array, $key[, $skipEmpties = false, $distinct = false])

> İç içe bir array yapısında yalnızca iç array'de yer alan belirtilen key'e ait değerlerden oluşacak yeni bir array oluşturur.
> $skipEmpties parametresi true olarak belirtildiyse bu key'e sahip olmayan array'ler dönen array'de yer almaz.
> $distinct parametresi array içerisinde aynı element'in birden fazla kez yer almasını engeller.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::column($arrays2, 'name')

// Sonra:
array(4) {
	[0] = string('eser')
	[1] = string('cengiz')
	[2] = string('baris')
	[3] = string('hasan')
}

~~~

### getRow ### {#getRow}
{array} arrays::getRow($array, $key, $value)

> İç içe bir array yapısında belirtilen key'in iç array'deki değere eşit olduğu ilk iç array'i döndürür.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::getRow($arrays2, 'name', 'eser')

// Sonra:
array(2) {
	[name] = string('eser')
	[dep] = string('IT')
}

~~~

### getRowKey ### {#getRowKey}
{mixed} arrays::getRowKey($array, $key, $value)

> İç içe bir array yapısında belirtilen key'in iç array'deki değere eşit olduğu ilk iç array'e ait key'i döndürür.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::getRowKey($arrays2, 'name', 'eser')

// Sonra:
integer(0)

~~~

### getRows ### {#getRows}
{array} arrays::getRows($array, $key, $value)

> İç içe bir array yapısında belirtilen key'in iç array'deki değere eşit olduğu iç arrayleri döndürür.

~~~

// Önce:
array(4) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
	[2] = array(2) {
		[name] = string('baris')
		[dep] = string('MIS')
	}
	[3] = array(2) {
		[name] = string('hasan')
		[dep] = string('MIS')
	}
}

// Komut:
$arrays2 = array(
	array('name' => 'eser', 'dep' => 'IT'),
	array('name' => 'cengiz', 'dep' => 'IT'),
	array('name' => 'baris', 'dep' => 'MIS'),
	array('name' => 'hasan', 'dep' => 'MIS')
);
arrays::getRows($arrays2, 'dep', 'IT')

// Sonra:
array(2) {
	[0] = array(2) {
		[name] = string('eser')
		[dep] = string('IT')
	}
	[1] = array(2) {
		[name] = string('cengiz')
		[dep] = string('IT')
	}
}

~~~

### combine ### {#combine}
{array} arrays::combine($keyArray, $valueArray)

> İki array'i birbiriyle birleştirir, eğer keyArray valueArray'den uzunsa karşılık gelen değerler null olarak işaretlenir.

~~~

// Önce:
array(4) {
	[0] = string('a')
	[1] = string('b')
	[2] = string('c')
	[3] = string('d')
}
array(3) {
	[0] = string('A')
	[1] = string('B')
	[2] = string('C')
}

// Komut:
$array3 = array('a', 'b', 'c', 'd');
$array4 = array('A', 'B', 'C');
arrays::combine($array3, $array4)

// Sonra:
array(4) {
	[a] = string('A')
	[b] = string('B')
	[c] = string('C')
	[d] = null
}

~~~

### combine2 ### {#combine2}
{array} arrays::combine2()

> Şu an için bir bilgi bulunmamakta.

~~~

// Önce:

// Komut:

// Sonra:

~~~

### sortByPriority ### {#sortByPriority}
{array} arrays::sortByPriority($array, $keyArray)

> Array içerisinde yer alan elemanları keyArray ile verilen listeye göre sıralar.

~~~

// Önce:
array(5) {
	[sayi1] = integer('5')
	[sayi2] = integer('9')
	[sayi3] = integer('7')
	[sayi4] = integer('4')
	[sayi5] = integer('1')
}

// Komut:
$array5 = array('sayi1' => 5, 'sayi2' => 9, 'sayi3' => 7, 'sayi4' => 4, 'sayi5' => 1);
arrays::sortByPriority($array5, array('sayi3', 'sayi2', 'sayi5'))

// Sonra:
array(5) {
	[sayi3] = integer('7')
	[sayi2] = integer('9')
	[sayi5] = integer('1')
	[sayi1] = integer('5')
	[sayi4] = integer('4')
}

~~~

[^1]: Scabbia Documentation Revision 1. Written by Eser 'Laroux' Ozvataf. eser@sent.com