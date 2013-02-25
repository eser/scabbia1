// <?php exit(); ?>
{
	// "moduleList" : [],

	// "downloadList" : [],

	"includeList" : [
		// "{vendor}controllers/*.php",
		// "{vendor}models/*.php"
	],

	"extensionList": [
		"string",
		"mime",
		"io",
		"http",
		"router",
		"arrays",
		"time",
		"contracts",
		"validation",
		// <scope mode="development">
			"profiler",
		// </scope>
		"cache",
		// <scope mode="development">
			"datasources",
			"database",
		// </scope>
		"session",
		"logger",
		"html",
		"i8n",
		"resources",
		"models",
		"views",
		"mvc",
		// <scope mode="development">
			"docs",
			"auth",
			"zmodels",
			"blackmore"
		// </scope>
		// "oauth"
	],

	"options" : {
		"gzip": 1,
		"autoload": 0
		// "siteroot": "/sampleapp"
	},

	"i8n": {
		"languageList": [
			{
				"id": "en",
				"locale": "en_US.UTF-8",
				"localewin": "English_United States.1252",
				"internalEncoding": "UTF-8",
				"name": "English"
			}
			// {
			// 	"id": "tr",
			// 	"locale": "tr_TR.UTF-8",
			// 	"localewin": "Turkish_Turkey.1254",
			// 	"internalEncoding": "UTF-8",
			// 	"name": "Turkish"
			// }
		]
	},

	"logger": {
		"filename": "{date|'d-m-Y'} {@category}.txt",
		"line": "[{date|'d-m-Y H:i:s'}] {strtoupper|@category} | {@ip} | {@message}"
	}

	// "cache": {
	// 	"keyphase": "",
	// 	"storage": "memcache://192.168.2.4:11211"
	// },
    // 
	// "smtp": {
	// 	"host": "ssl://mail.messagingengine.com",
	// 	"port": 465,
	// 	"username": "eser@sent.com",
	// 	"password": ""
	// }
}